<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('provider');

$providerUserId = (int)($_SESSION['user_id'] ?? 0);

if ($providerUserId <= 0) {
    exit('Unauthorized access.');
}

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

if (isset($pdo) && $pdo instanceof PDO) {
    $db = $pdo;
} elseif (isset($conn) && $conn instanceof PDO) {
    $db = $conn;
} else {
    exit('Database connection not available.');
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function provider_message_h(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function provider_message_time(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }

    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return '';
    }

    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    }

    if ($diff < 3600) {
        return (int)floor($diff / 60) . 'm ago';
    }

    if ($diff < 86400) {
        return (int)floor($diff / 3600) . 'h ago';
    }

    if ($diff < 604800) {
        return (int)floor($diff / 86400) . 'd ago';
    }

    return date('M j, Y', $timestamp);
}

function provider_message_full_time(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }

    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return '';
    }

    return date('M j, Y \a\t g:i A', $timestamp);
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['provider_message_csrf_token'])) {
    $_SESSION['provider_message_csrf_token'] = bin2hex(
        random_bytes(32)
    );
}

$csrfToken = $_SESSION['provider_message_csrf_token'];

/*
|--------------------------------------------------------------------------
| Selected Client / Booking
|--------------------------------------------------------------------------
*/

$selectedClientId = filter_input(
    INPUT_GET,
    'client_id',
    FILTER_VALIDATE_INT
);

$selectedClientId = (
    $selectedClientId !== false &&
    $selectedClientId !== null
)
    ? (int)$selectedClientId
    : 0;

$selectedBookingId = filter_input(
    INPUT_GET,
    'booking_id',
    FILTER_VALIDATE_INT
);

$selectedBookingId = (
    $selectedBookingId !== false &&
    $selectedBookingId !== null
)
    ? (int)$selectedBookingId
    : 0;

/*
|--------------------------------------------------------------------------
| Send Message
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($postedToken) ||
        !hash_equals($csrfToken, $postedToken)
    ) {
        header(
            'Location: dashboard.php?page=messages&error=security'
        );
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'send_message') {

        $receiverId = filter_input(
            INPUT_POST,
            'receiver_user_id',
            FILTER_VALIDATE_INT
        );

        $bookingId = filter_input(
            INPUT_POST,
            'booking_id',
            FILTER_VALIDATE_INT
        );

        $receiverId = (
            $receiverId !== false &&
            $receiverId !== null
        )
            ? (int)$receiverId
            : 0;

        $bookingId = (
            $bookingId !== false &&
            $bookingId !== null
        )
            ? (int)$bookingId
            : 0;

        $messageText = trim(
            (string)($_POST['message'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Basic validation
        |--------------------------------------------------------------------------
        */

        if (
            $receiverId <= 0 ||
            $messageText === ''
        ) {
            header(
                'Location: dashboard.php?page=messages'
                . ($receiverId > 0
                    ? '&client_id=' . $receiverId
                    : '')
                . ($bookingId > 0
                    ? '&booking_id=' . $bookingId
                    : '')
                . '&error=empty'
            );
            exit;
        }

        if (mb_strlen($messageText) > 5000) {
            header(
                'Location: dashboard.php?page=messages'
                . '&client_id=' . $receiverId
                . ($bookingId > 0
                    ? '&booking_id=' . $bookingId
                    : '')
                . '&error=too_long'
            );
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Client
        |--------------------------------------------------------------------------
        */

        $clientStmt = $db->prepare("
            SELECT
                id,
                full_name
            FROM users
            WHERE id = ?
              AND role = 'client'
              AND account_status = 'active'
            LIMIT 1
        ");

        $clientStmt->execute([
            $receiverId
        ]);

        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            header(
                'Location: dashboard.php?page=messages&error=invalid_client'
            );
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Relationship
        |--------------------------------------------------------------------------
        |
        | Provider can message a client if:
        |
        | 1. They already have a conversation
        | OR
        | 2. They have a booking together.
        |
        */

        $relationshipStmt = $db->prepare("
            SELECT 1
            FROM messages
            WHERE (
                sender_user_id = ?
                AND receiver_user_id = ?
            )
            OR (
                sender_user_id = ?
                AND receiver_user_id = ?
            )
            LIMIT 1
        ");

        $relationshipStmt->execute([
            $providerUserId,
            $receiverId,
            $receiverId,
            $providerUserId
        ]);

        $hasConversation = (bool)$relationshipStmt->fetchColumn();

        $validBookingId = null;

        /*
        |--------------------------------------------------------------------------
        | Verify Booking
        |--------------------------------------------------------------------------
        */

        if ($bookingId > 0) {

            $bookingStmt = $db->prepare("
                SELECT id
                FROM bookings
                WHERE id = ?
                  AND provider_user_id = ?
                  AND client_user_id = ?
                LIMIT 1
            ");

            $bookingStmt->execute([
                $bookingId,
                $providerUserId,
                $receiverId
            ]);

            $bookingExists = $bookingStmt->fetchColumn();

            if (!$bookingExists) {
                header(
                    'Location: dashboard.php?page=messages'
                    . '&client_id=' . $receiverId
                    . '&error=invalid_booking'
                );
                exit;
            }

            $validBookingId = (int)$bookingExists;
        }

        if (!$hasConversation && $validBookingId === null) {
            header(
                'Location: dashboard.php?page=messages&error=not_allowed'
            );
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert Message
        |--------------------------------------------------------------------------
        */

        $insertMessageStmt = $db->prepare("
            INSERT INTO messages (
                sender_user_id,
                receiver_user_id,
                booking_id,
                message,
                is_read
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $insertMessageStmt->execute([
            $providerUserId,
            $receiverId,
            $validBookingId,
            $messageText
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        |
        | Notification failure must not break messaging.
        |
        */

        try {

            $notificationStmt = $db->prepare("
                INSERT INTO notifications (
                    user_id,
                    type,
                    title,
                    message,
                    booking_id,
                    sender_user_id,
                    is_read
                )
                VALUES (?, ?, ?, ?, ?, ?, 0)
            ");

            $notificationStmt->execute([
                $receiverId,
                'new_message',
                'New message',
                'You received a new message from '
                . (string)$client['full_name']
                . '.',
                $validBookingId,
                $providerUserId
            ]);

        } catch (Throwable $notificationError) {
            // Messaging continues even if notification fails.
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        header(
            'Location: dashboard.php?page=messages'
            . '&client_id=' . $receiverId
            . ($validBookingId !== null
                ? '&booking_id=' . $validBookingId
                : '')
            . '&sent=1'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Resolve Client From Booking
|--------------------------------------------------------------------------
*/

if (
    $selectedBookingId > 0 &&
    $selectedClientId <= 0
) {

    $bookingClientStmt = $db->prepare("
        SELECT client_user_id
        FROM bookings
        WHERE id = ?
          AND provider_user_id = ?
        LIMIT 1
    ");

    $bookingClientStmt->execute([
        $selectedBookingId,
        $providerUserId
    ]);

    $bookingClient = $bookingClientStmt->fetchColumn();

    if ($bookingClient !== false) {
        $selectedClientId = (int)$bookingClient;
    }
}

/*
|--------------------------------------------------------------------------
| Conversation List
|--------------------------------------------------------------------------
*/

$conversationsStmt = $db->prepare("
    SELECT
        conversation.other_user_id,
        u.full_name,
        u.phone,
        cp.profile_image,
        last_message.message AS last_message,
        last_message.created_at AS last_message_at,
        conversation.unread_count

    FROM (
        SELECT
            CASE
                WHEN sender_user_id = :provider1
                    THEN receiver_user_id
                ELSE sender_user_id
            END AS other_user_id,

            MAX(id) AS last_message_id,

            SUM(
                CASE
                    WHEN receiver_user_id = :provider2
                     AND is_read = 0
                    THEN 1
                    ELSE 0
                END
            ) AS unread_count

        FROM messages

        WHERE sender_user_id = :provider3
           OR receiver_user_id = :provider4

        GROUP BY other_user_id
    ) AS conversation

    INNER JOIN messages last_message
        ON last_message.id = conversation.last_message_id

    INNER JOIN users u
        ON u.id = conversation.other_user_id

    LEFT JOIN client_profiles cp
        ON cp.user_id = u.id

    WHERE u.role = 'client'

    ORDER BY last_message.created_at DESC
");

$conversationsStmt->execute([
    ':provider1' => $providerUserId,
    ':provider2' => $providerUserId,
    ':provider3' => $providerUserId,
    ':provider4' => $providerUserId
]);

$conversations = $conversationsStmt->fetchAll(
    PDO::FETCH_ASSOC
);

/*
|--------------------------------------------------------------------------
| Validate Selected Client
|--------------------------------------------------------------------------
*/

if ($selectedClientId > 0) {

    $selectedClientStmt = $db->prepare("
        SELECT
            u.id,
            u.full_name,
            u.phone,
            cp.profile_image
        FROM users u

        LEFT JOIN client_profiles cp
            ON cp.user_id = u.id

        WHERE u.id = ?
          AND u.role = 'client'
          AND u.account_status = 'active'

        LIMIT 1
    ");

    $selectedClientStmt->execute([
        $selectedClientId
    ]);

    $selectedClient =
        $selectedClientStmt->fetch(PDO::FETCH_ASSOC);

    if (!$selectedClient) {
        $selectedClientId = 0;
        $selectedBookingId = 0;
    }
} else {
    $selectedClient = null;
}

/*
|--------------------------------------------------------------------------
| Selected Booking
|--------------------------------------------------------------------------
*/

$selectedBooking = null;

if (
    $selectedClientId > 0 &&
    $selectedBookingId > 0
) {

    $selectedBookingStmt = $db->prepare("
        SELECT
            b.id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,

            ps.service_name,
            ps.price,
            ps.price_type

        FROM bookings b

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id

        WHERE b.id = ?
          AND b.provider_user_id = ?
          AND b.client_user_id = ?

        LIMIT 1
    ");

    $selectedBookingStmt->execute([
        $selectedBookingId,
        $providerUserId,
        $selectedClientId
    ]);

    $selectedBooking =
        $selectedBookingStmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Use Latest Booking When None Selected
|--------------------------------------------------------------------------
*/

if (
    $selectedClientId > 0 &&
    !$selectedBooking
) {

    $latestBookingStmt = $db->prepare("
        SELECT
            b.id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,

            ps.service_name,
            ps.price,
            ps.price_type

        FROM bookings b

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id

        WHERE b.provider_user_id = ?
          AND b.client_user_id = ?

        ORDER BY b.created_at DESC, b.id DESC

        LIMIT 1
    ");

    $latestBookingStmt->execute([
        $providerUserId,
        $selectedClientId
    ]);

    $selectedBooking =
        $latestBookingStmt->fetch(PDO::FETCH_ASSOC)
        ?: null;
}

/*
|--------------------------------------------------------------------------
| Mark Incoming Messages As Read
|--------------------------------------------------------------------------
*/

if ($selectedClientId > 0) {

    $markReadStmt = $db->prepare("
        UPDATE messages
        SET is_read = 1

        WHERE sender_user_id = ?
          AND receiver_user_id = ?
          AND is_read = 0
    ");

    $markReadStmt->execute([
        $selectedClientId,
        $providerUserId
    ]);
}

/*
|--------------------------------------------------------------------------
| Load Conversation Messages
|--------------------------------------------------------------------------
*/

$conversationMessages = [];

if ($selectedClientId > 0) {

    $messagesStmt = $db->prepare("
        SELECT
            id,
            sender_user_id,
            receiver_user_id,
            booking_id,
            message,
            is_read,
            created_at

        FROM messages

        WHERE (
            sender_user_id = ?
            AND receiver_user_id = ?
        )
        OR (
            sender_user_id = ?
            AND receiver_user_id = ?
        )

        ORDER BY created_at ASC, id ASC
    ");

    $messagesStmt->execute([
        $providerUserId,
        $selectedClientId,
        $selectedClientId,
        $providerUserId
    ]);

    $conversationMessages =
        $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Total Unread
|--------------------------------------------------------------------------
*/

$unreadStmt = $db->prepare("
    SELECT COUNT(*)
    FROM messages
    WHERE receiver_user_id = ?
      AND is_read = 0
");

$unreadStmt->execute([
    $providerUserId
]);

$totalUnreadMessages =
    (int)$unreadStmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Client Avatar
|--------------------------------------------------------------------------
*/

$clientImage = '';

if ($selectedClient) {
    $clientImage = trim(
        (string)(
            $selectedClient['profile_image'] ?? ''
        )
    );
}

$clientImageUrl =
    '../assets/images/default-avatar.jpg';

if ($clientImage !== '') {
    $clientImageUrl =
        '../assets/images/profiles/'
        . rawurlencode($clientImage);
}

?>

<section class="provider-messages-page">

    <!-- ==============================================================
         PAGE HEADER
         ============================================================== -->

    <div class="messages-page-header">

        <div>
            <span class="messages-eyebrow">
                Communication
            </span>

            <h1>Messages</h1>

            <p>
                Communicate with your clients about bookings and services.
            </p>
        </div>

        <?php if ($totalUnreadMessages > 0): ?>

            <div class="messages-unread-summary">

                <span class="messages-unread-dot"></span>

                <?= $totalUnreadMessages ?>

                <?= $totalUnreadMessages === 1
                    ? 'unread message'
                    : 'unread messages' ?>

            </div>

        <?php endif; ?>

    </div>


    <!-- ==============================================================
         MESSAGES LAYOUT
         ============================================================== -->

    <div class="messages-layout">


        <!-- ==========================================================
             CONVERSATIONS
             ========================================================== -->

        <aside class="messages-conversations">

            <div class="messages-conversations-header">

                <div>

                    <h2>Conversations</h2>

                    <span>
                        <?= count($conversations) ?>

                        <?= count($conversations) === 1
                            ? 'conversation'
                            : 'conversations' ?>
                    </span>

                </div>

            </div>


            <div class="conversation-list">

                <?php if (empty($conversations)): ?>

                    <div class="conversation-empty">

                        <div class="conversation-empty-icon">
                            <i class="fa-regular fa-comments"></i>
                        </div>

                        <h3>No conversations yet</h3>

                        <p>
                            Messages from your clients will appear here.
                        </p>

                    </div>

                <?php else: ?>

                    <?php foreach ($conversations as $conversation): ?>

                        <?php

                        $conversationClientId =
                            (int)$conversation['other_user_id'];

                        $isActive =
                            $selectedClientId ===
                            $conversationClientId;

                        $conversationImage =
                            trim(
                                (string)(
                                    $conversation['profile_image']
                                    ?? ''
                                )
                            );

                        $conversationImageUrl =
                            '../assets/images/default-avatar.jpg';

                        if ($conversationImage !== '') {
                            $conversationImageUrl =
                                '../assets/images/profiles/'
                                . rawurlencode(
                                    $conversationImage
                                );
                        }

                        $lastMessage =
                            trim(
                                (string)(
                                    $conversation['last_message']
                                    ?? ''
                                )
                            );

                        if (mb_strlen($lastMessage) > 45) {
                            $lastMessage =
                                mb_substr(
                                    $lastMessage,
                                    0,
                                    45
                                ) . '…';
                        }

                        $unreadCount =
                            (int)(
                                $conversation['unread_count']
                                ?? 0
                            );

                        ?>

                        <a
                            href="dashboard.php?page=messages&client_id=<?= $conversationClientId ?>"
                            class="conversation-item <?= $isActive
                                ? 'is-active'
                                : '' ?>"
                        >

                            <div class="conversation-avatar">

                                <img
                                    src="<?= provider_message_h($conversationImageUrl) ?>"
                                    alt="<?= provider_message_h(
                                        $conversation['full_name']
                                    ) ?>"
                                >

                            </div>


                            <div class="conversation-content">

                                <div class="conversation-top">

                                    <strong>
                                        <?= provider_message_h(
                                            $conversation['full_name']
                                        ) ?>
                                    </strong>

                                    <time>
                                        <?= provider_message_h(
                                            provider_message_time(
                                                $conversation['last_message_at']
                                            )
                                        ) ?>
                                    </time>

                                </div>


                                <div class="conversation-bottom">

                                    <span>
                                        <?= provider_message_h(
                                            $lastMessage
                                        ) ?>
                                    </span>

                                    <?php if ($unreadCount > 0): ?>

                                        <b class="conversation-unread">
                                            <?= $unreadCount ?>
                                        </b>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </a>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </aside>


        <!-- ==========================================================
             CHAT
             ========================================================== -->

        <section class="messages-chat">

            <?php if (!$selectedClient): ?>

                <div class="chat-empty-state">

                    <div class="chat-empty-icon">
                        <i class="fa-regular fa-paper-plane"></i>
                    </div>

                    <h2>Select a conversation</h2>

                    <p>
                        Choose a client from your conversations to view
                        messages and continue the conversation.
                    </p>

                </div>

            <?php else: ?>


                <!-- ==================================================
                     CHAT HEADER
                     ================================================== -->

                <header class="chat-header">

                    <div class="chat-user">

                        <img
                            src="<?= provider_message_h($clientImageUrl) ?>"
                            alt="<?= provider_message_h(
                                $selectedClient['full_name']
                            ) ?>"
                        >

                        <div>

                            <strong>
                                <?= provider_message_h(
                                    $selectedClient['full_name']
                                ) ?>
                            </strong>

                            <span>
                                Client
                            </span>

                        </div>

                    </div>

                </header>


                <!-- ==================================================
                     BOOKING CONTEXT
                     ================================================== -->

                <?php if ($selectedBooking): ?>

                    <div class="chat-booking-bar">

                        <div class="chat-booking-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="chat-booking-info">

                            <strong>
                                <?= provider_message_h(
                                    $selectedBooking['service_name']
                                    ?? 'Service booking'
                                ) ?>
                            </strong>

                            <span>

                                <?= provider_message_h(
                                    date(
                                        'M j, Y \a\t g:i A',
                                        strtotime(
                                            (string)$selectedBooking['booking_date']
                                        )
                                    )
                                ) ?>

                                •

                                <?= provider_message_h(
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            (string)$selectedBooking['status']
                                        )
                                    )
                                ) ?>

                            </span>

                        </div>

                        <a
                            href="dashboard.php?page=bookings&id=<?= (int)$selectedBooking['id'] ?>"
                        >
                            View booking
                        </a>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     CHAT MESSAGES
                     ================================================== -->

                <div
                    class="chat-messages"
                    id="chatMessages"
                >

                    <?php if (empty($conversationMessages)): ?>

                        <div class="chat-no-messages">

                            <div>
                                <i class="fa-regular fa-comment"></i>
                            </div>

                            <h3>Start the conversation</h3>

                            <p>
                                Send a message to
                                <?= provider_message_h(
                                    $selectedClient['full_name']
                                ) ?>.
                            </p>

                        </div>

                    <?php else: ?>

                        <?php

                        $lastMessageDate = '';

                        foreach (
                            $conversationMessages
                            as $message
                        ):

                            $messageTimestamp =
                                strtotime(
                                    (string)$message['created_at']
                                );

                            $messageDate =
                                $messageTimestamp !== false
                                    ? date(
                                        'Y-m-d',
                                        $messageTimestamp
                                    )
                                    : '';

                            if (
                                $messageDate !==
                                $lastMessageDate
                            ):

                                $lastMessageDate =
                                    $messageDate;

                                $displayDate =
                                    $messageTimestamp !== false
                                        ? date(
                                            'M j, Y',
                                            $messageTimestamp
                                        )
                                        : '';

                        ?>

                                <div class="chat-date-divider">
                                    <span>
                                        <?= provider_message_h(
                                            $displayDate
                                        ) ?>
                                    </span>
                                </div>

                        <?php endif; ?>


                        <?php

                        $isMine =
                            (int)$message['sender_user_id'] ===
                            $providerUserId;

                        ?>

                        <div
                            class="message-row <?= $isMine
                                ? 'message-row-sent'
                                : 'message-row-received' ?>"
                        >

                            <div class="message-bubble">

                                <p>
                                    <?= nl2br(
                                        provider_message_h(
                                            (string)$message['message']
                                        )
                                    ) ?>
                                </p>

                                <time
                                    title="<?= provider_message_h(
                                        provider_message_full_time(
                                            (string)$message['created_at']
                                        )
                                    ) ?>"
                                >
                                    <?= provider_message_h(
                                        $messageTimestamp !== false
                                            ? date(
                                                'g:i A',
                                                $messageTimestamp
                                            )
                                            : ''
                                    ) ?>
                                </time>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>


                <!-- ==================================================
                     MESSAGE FORM
                     ================================================== -->

                <form
                    method="POST"
                    action="dashboard.php?page=messages&client_id=<?= $selectedClientId ?><?= $selectedBooking ? '&booking_id=' . (int)$selectedBooking['id'] : '' ?>"
                    class="chat-form"
                    autocomplete="off"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="send_message"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= provider_message_h($csrfToken) ?>"
                    >

                    <input
                        type="hidden"
                        name="receiver_user_id"
                        value="<?= $selectedClientId ?>"
                    >

                    <?php if ($selectedBooking): ?>

                        <input
                            type="hidden"
                            name="booking_id"
                            value="<?= (int)$selectedBooking['id'] ?>"
                        >

                    <?php endif; ?>


                    <div class="chat-input-wrapper">

                        <textarea
                            name="message"
                            id="messageInput"
                            rows="1"
                            maxlength="5000"
                            placeholder="Write a message..."
                            required
                        ></textarea>

                        <button
                            type="submit"
                            class="chat-send-button"
                            aria-label="Send message"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>

                    </div>


                    <div class="chat-form-footer">

                        <span>
                            <i class="fa-solid fa-shield-halved"></i>
                            Keep communication related to the service.
                        </span>

                        <span id="messageCounter">
                            0 / 5000
                        </span>

                    </div>

                </form>

            <?php endif; ?>

        </section>

    </div>

</section>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const chatMessages =
        document.getElementById('chatMessages');

    const messageInput =
        document.getElementById('messageInput');

    const messageCounter =
        document.getElementById('messageCounter');

    if (chatMessages) {
        chatMessages.scrollTop =
            chatMessages.scrollHeight;
    }

    if (!messageInput) {
        return;
    }

    function updateCounter() {
        if (messageCounter) {
            messageCounter.textContent =
                messageInput.value.length + ' / 5000';
        }
    }

    function resizeInput() {

        messageInput.style.height = 'auto';

        messageInput.style.height =
            Math.min(
                messageInput.scrollHeight,
                140
            ) + 'px';
    }

    messageInput.addEventListener(
        'input',
        function () {
            updateCounter();
            resizeInput();
        }
    );

    messageInput.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Enter' &&
                !event.shiftKey
            ) {
                event.preventDefault();

                const form =
                    messageInput.closest('form');

                if (
                    form &&
                    messageInput.value.trim() !== ''
                ) {
                    form.requestSubmit();
                }
            }
        }
    );

    updateCounter();
    resizeInput();

});
</script>