<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

/*
|--------------------------------------------------------------------------
| Current Client
|--------------------------------------------------------------------------
*/

$clientUserId = (int)($_SESSION['user_id'] ?? 0);

if ($clientUserId <= 0) {
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

function client_message_h(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function client_message_time(string $datetime): string
{
    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return '';
    }

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    }

    if ($difference < 3600) {
        return (int)floor($difference / 60) . 'm ago';
    }

    if ($difference < 86400) {
        return (int)floor($difference / 3600) . 'h ago';
    }

    if ($difference < 604800) {
        return (int)floor($difference / 86400) . 'd ago';
    }

    return date('M j, Y', $timestamp);
}

function client_message_full_time(string $datetime): string
{
    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return '';
    }

    return date(
        'M j, Y \a\t g:i A',
        $timestamp
    );
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['client_message_csrf_token'])) {
    $_SESSION['client_message_csrf_token'] =
        bin2hex(random_bytes(32));
}

$csrfToken =
    $_SESSION['client_message_csrf_token'];

/*
|--------------------------------------------------------------------------
| Selected Provider
|--------------------------------------------------------------------------
|
| Example:
| dashboard.php?page=messages&provider_id=4
|
*/

$selectedProviderId = filter_input(
    INPUT_GET,
    'provider_id',
    FILTER_VALIDATE_INT
);

$selectedProviderId =
    ($selectedProviderId !== false &&
     $selectedProviderId !== null)
        ? (int)$selectedProviderId
        : 0;

/*
|--------------------------------------------------------------------------
| Selected Booking
|--------------------------------------------------------------------------
|
| Example:
| dashboard.php?page=messages&provider_id=4&booking_id=12
|
*/

$selectedBookingId = filter_input(
    INPUT_GET,
    'booking_id',
    FILTER_VALIDATE_INT
);

$selectedBookingId =
    ($selectedBookingId !== false &&
     $selectedBookingId !== null)
        ? (int)$selectedBookingId
        : 0;

/*
|--------------------------------------------------------------------------
| POST - Send Message
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

        $receiverId =
            ($receiverId !== false &&
             $receiverId !== null)
                ? (int)$receiverId
                : 0;

        $bookingId =
            ($bookingId !== false &&
             $bookingId !== null)
                ? (int)$bookingId
                : 0;

        $messageText = trim(
            (string)($_POST['message'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Message
        |--------------------------------------------------------------------------
        */

        if ($receiverId <= 0 || $messageText === '') {

            header(
                'Location: dashboard.php?page=messages'
                . ($receiverId > 0
                    ? '&provider_id=' . $receiverId
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
                . '&provider_id=' . $receiverId
                . ($bookingId > 0
                    ? '&booking_id=' . $bookingId
                    : '')
                . '&error=too_long'
            );

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Provider
        |--------------------------------------------------------------------------
        */

        $providerStmt = $db->prepare("
            SELECT id
            FROM users
            WHERE id = ?
              AND role = 'provider'
              AND account_status = 'active'
            LIMIT 1
        ");

        $providerStmt->execute([
            $receiverId
        ]);

        $providerExists =
            $providerStmt->fetchColumn();

        if (!$providerExists) {

            header(
                'Location: dashboard.php?page=messages&error=invalid_user'
            );

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Check Existing Conversation
        |--------------------------------------------------------------------------
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
            $clientUserId,
            $receiverId,
            $receiverId,
            $clientUserId
        ]);

        $hasMessageRelationship =
            (bool)$relationshipStmt->fetchColumn();

        /*
        |--------------------------------------------------------------------------
        | Validate Booking
        |--------------------------------------------------------------------------
        */

        $validBookingId = null;

        if ($bookingId > 0) {

            $bookingStmt = $db->prepare("
                SELECT id
                FROM bookings
                WHERE id = ?
                  AND client_user_id = ?
                  AND provider_user_id = ?
                LIMIT 1
            ");

            $bookingStmt->execute([
                $bookingId,
                $clientUserId,
                $receiverId
            ]);

            $bookingExists =
                $bookingStmt->fetchColumn();

            if ($bookingExists) {
                $validBookingId = $bookingId;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Messaging Permission
        |--------------------------------------------------------------------------
        |
        | Client can message provider when:
        |
        | 1. They already have a conversation
        | OR
        | 2. They have a booking together.
        |
        */

        if (
            !$hasMessageRelationship &&
            $validBookingId === null
        ) {

            header(
                'Location: dashboard.php?page=messages'
                . '&error=not_allowed'
            );

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert Message
        |--------------------------------------------------------------------------
        */

        $insertStmt = $db->prepare("
            INSERT INTO messages (
                sender_user_id,
                receiver_user_id,
                booking_id,
                message,
                is_read
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $insertStmt->execute([
            $clientUserId,
            $receiverId,
            $validBookingId,
            $messageText
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Notification
        |--------------------------------------------------------------------------
        */

        try {

            $clientStmt = $db->prepare("
                SELECT full_name
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $clientStmt->execute([
                $clientUserId
            ]);

            $clientName =
                (string)(
                    $clientStmt->fetchColumn()
                    ?: 'Client'
                );

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
                $clientName . ' sent you a new message.',
                $validBookingId,
                $clientUserId
            ]);

        } catch (Throwable $notificationError) {

            /*
            |--------------------------------------------------------------------------
            | Messaging should continue even if notifications fail.
            |--------------------------------------------------------------------------
            */
        }

        /*
        |--------------------------------------------------------------------------
        | Return To Conversation
        |--------------------------------------------------------------------------
        */

        header(
            'Location: dashboard.php?page=messages'
            . '&provider_id=' . $receiverId
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
| Resolve Provider From Booking
|--------------------------------------------------------------------------
*/

if (
    $selectedBookingId > 0 &&
    $selectedProviderId <= 0
) {

    $bookingProviderStmt = $db->prepare("
        SELECT provider_user_id
        FROM bookings
        WHERE id = ?
          AND client_user_id = ?
        LIMIT 1
    ");

    $bookingProviderStmt->execute([
        $selectedBookingId,
        $clientUserId
    ]);

    $bookingProvider =
        $bookingProviderStmt->fetchColumn();

    if ($bookingProvider !== false) {
        $selectedProviderId =
            (int)$bookingProvider;
    }
}

/*
|--------------------------------------------------------------------------
| Conversations
|--------------------------------------------------------------------------
*/

$conversationsStmt = $db->prepare("
    SELECT
        conversation.other_user_id,
        u.full_name,
        u.phone,
        pp.profile_image,
        last_message.message AS last_message,
        last_message.created_at AS last_message_at,
        conversation.unread_count

    FROM (
        SELECT
            CASE
                WHEN sender_user_id = :client_1
                    THEN receiver_user_id
                ELSE sender_user_id
            END AS other_user_id,

            MAX(id) AS last_message_id,

            SUM(
                CASE
                    WHEN receiver_user_id = :client_2
                     AND is_read = 0
                    THEN 1
                    ELSE 0
                END
            ) AS unread_count

        FROM messages

        WHERE sender_user_id = :client_3
           OR receiver_user_id = :client_4

        GROUP BY other_user_id

    ) AS conversation

    INNER JOIN messages last_message
        ON last_message.id =
           conversation.last_message_id

    INNER JOIN users u
        ON u.id = conversation.other_user_id

    LEFT JOIN provider_profiles pp
        ON pp.user_id = u.id

    WHERE u.role = 'provider'
      AND u.account_status = 'active'

    ORDER BY last_message.created_at DESC
");

$conversationsStmt->execute([
    ':client_1' => $clientUserId,
    ':client_2' => $clientUserId,
    ':client_3' => $clientUserId,
    ':client_4' => $clientUserId
]);

$conversations =
    $conversationsStmt->fetchAll(
        PDO::FETCH_ASSOC
    );

/*
|--------------------------------------------------------------------------
| Validate Selected Provider
|--------------------------------------------------------------------------
*/

if ($selectedProviderId > 0) {

    $providerExistsStmt = $db->prepare("
        SELECT id
        FROM users
        WHERE id = ?
          AND role = 'provider'
          AND account_status = 'active'
        LIMIT 1
    ");

    $providerExistsStmt->execute([
        $selectedProviderId
    ]);

    if (!$providerExistsStmt->fetchColumn()) {

        $selectedProviderId = 0;
        $selectedBookingId = 0;
    }
}

/*
|--------------------------------------------------------------------------
| Selected Provider
|--------------------------------------------------------------------------
*/

$selectedProvider = null;

if ($selectedProviderId > 0) {

    $selectedProviderStmt = $db->prepare("
        SELECT
            u.id,
            u.full_name,
            u.phone,

            pp.profile_image,
            pp.location_name,
            pp.availability,
            pp.is_verified,
            pp.average_rating,
            pp.total_reviews

        FROM users u

        LEFT JOIN provider_profiles pp
            ON pp.user_id = u.id

        WHERE u.id = ?
          AND u.role = 'provider'
          AND u.account_status = 'active'

        LIMIT 1
    ");

    $selectedProviderStmt->execute([
        $selectedProviderId
    ]);

    $selectedProvider =
        $selectedProviderStmt->fetch(
            PDO::FETCH_ASSOC
        );

    if (!$selectedProvider) {

        $selectedProviderId = 0;
        $selectedBookingId = 0;
    }
}

/*
|--------------------------------------------------------------------------
| Mark Provider Messages As Read
|--------------------------------------------------------------------------
*/

if ($selectedProviderId > 0) {

    $markReadStmt = $db->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE sender_user_id = ?
          AND receiver_user_id = ?
          AND is_read = 0
    ");

    $markReadStmt->execute([
        $selectedProviderId,
        $clientUserId
    ]);
}

/*
|--------------------------------------------------------------------------
| Conversation Messages
|--------------------------------------------------------------------------
*/

$conversationMessages = [];

if ($selectedProviderId > 0) {

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
        $clientUserId,
        $selectedProviderId,
        $selectedProviderId,
        $clientUserId
    ]);

    $conversationMessages =
        $messagesStmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}

/*
|--------------------------------------------------------------------------
| Selected Booking
|--------------------------------------------------------------------------
*/

$selectedBooking = null;

if (
    $selectedProviderId > 0 &&
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
          AND b.client_user_id = ?
          AND b.provider_user_id = ?

        LIMIT 1
    ");

    $selectedBookingStmt->execute([
        $selectedBookingId,
        $clientUserId,
        $selectedProviderId
    ]);

    $selectedBooking =
        $selectedBookingStmt->fetch(
            PDO::FETCH_ASSOC
        );
}

/*
|--------------------------------------------------------------------------
| If No Booking Selected
|--------------------------------------------------------------------------
|
| Use the latest booking with this provider.
|
*/

if (
    $selectedProviderId > 0 &&
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

        WHERE b.client_user_id = ?
          AND b.provider_user_id = ?

        ORDER BY b.created_at DESC, b.id DESC

        LIMIT 1
    ");

    $latestBookingStmt->execute([
        $clientUserId,
        $selectedProviderId
    ]);

    $selectedBooking =
        $latestBookingStmt->fetch(
            PDO::FETCH_ASSOC
        ) ?: null;
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
    $clientUserId
]);

$totalUnreadMessages =
    (int)$unreadStmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Provider Image
|--------------------------------------------------------------------------
*/

$providerImage = '';

if ($selectedProvider) {

    $providerImage =
        trim(
            (string)(
                $selectedProvider['profile_image']
                ?? ''
            )
        );
}

$providerImageUrl =
    '../assets/images/default-avatar.jpg';

if ($providerImage !== '') {

    $providerImageUrl =
        '../assets/images/profiles/'
        . rawurlencode($providerImage);
}

?>

<section class="client-messages-page">

    <!-- =========================================================
         PAGE HEADER
         ========================================================= -->

    <div class="messages-page-header">

        <div>
            <h1>Messages</h1>

            <p>
                Stay connected with providers about your services and bookings.
            </p>
        </div>

        <?php if ($totalUnreadMessages > 0): ?>

            <div class="messages-unread-summary">
                <span class="messages-unread-dot"></span>

                <?= $totalUnreadMessages ?>

                unread
            </div>

        <?php endif; ?>

    </div>


    <!-- =========================================================
         MESSAGES CONTAINER
         ========================================================= -->

    <div class="messages-container">

        <!-- =====================================================
             CONVERSATIONS
             ===================================================== -->

        <aside class="messages-conversations">

            <div class="conversations-header">

                <h2>Conversations</h2>

                <span class="conversation-count">
                    <?= count($conversations) ?>
                </span>

            </div>


            <div class="conversation-list">

                <?php if (empty($conversations)): ?>

                    <div class="conversations-empty">

                        <i class="fa-regular fa-comments"></i>

                        <p>
                            Your conversations with providers will appear here.
                        </p>

                    </div>

                <?php else: ?>

                    <?php foreach ($conversations as $conversation): ?>

                        <?php

                        $conversationProviderId =
                            (int)$conversation['other_user_id'];

                        $isActive =
                            $selectedProviderId ===
                            $conversationProviderId;

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

                        if (mb_strlen($lastMessage) > 55) {

                            $lastMessage =
                                mb_substr(
                                    $lastMessage,
                                    0,
                                    55
                                ) . '…';
                        }

                        $unreadCount =
                            (int)(
                                $conversation['unread_count']
                                ?? 0
                            );

                        ?>

                        <a
                            href="dashboard.php?page=messages&provider_id=<?= $conversationProviderId ?>"
                            class="conversation-item <?= $isActive ? 'active' : '' ?> <?= $unreadCount > 0 ? 'unread' : '' ?>"
                        >

                            <div class="conversation-avatar">

                                <img
                                    src="<?= client_message_h($conversationImageUrl) ?>"
                                    alt="<?= client_message_h((string)$conversation['full_name']) ?>"
                                >

                            </div>


                            <div class="conversation-info">

                                <div class="conversation-top">

                                    <span class="conversation-name">
                                        <?= client_message_h(
                                            (string)$conversation['full_name']
                                        ) ?>
                                    </span>

                                    <span class="conversation-time">
                                        <?= client_message_h(
                                            client_message_time(
                                                (string)$conversation['last_message_at']
                                            )
                                        ) ?>
                                    </span>

                                </div>


                                <div class="conversation-preview">

                                    <span class="conversation-message">
                                        <?= client_message_h($lastMessage) ?>
                                    </span>

                                    <?php if ($unreadCount > 0): ?>

                                        <span class="unread-badge">
                                            <?= $unreadCount > 99
                                                ? '99+'
                                                : $unreadCount ?>
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </a>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </aside>


        <!-- =====================================================
             CHAT PANEL
             ===================================================== -->

        <section class="chat-panel">

            <?php if (!$selectedProvider): ?>

                <div class="chat-empty">

                    <div class="chat-empty-icon">
                        <i class="fa-regular fa-paper-plane"></i>
                    </div>

                    <h3>Your messages</h3>

                    <p>
                        Select a conversation to view your messages with a provider.
                    </p>

                </div>

            <?php else: ?>

                <!-- =================================================
                     CHAT HEADER
                     ================================================= -->

                <header class="chat-header">

                    <div class="chat-user">

                        <div class="chat-avatar">

                            <img
                                src="<?= client_message_h($providerImageUrl) ?>"
                                alt="<?= client_message_h($selectedProvider['full_name']) ?>"
                            >

                        </div>


                        <div class="chat-user-info">

                            <h2 class="chat-user-name">

                                <?= client_message_h(
                                    $selectedProvider['full_name']
                                ) ?>

                            </h2>


                            <div class="chat-user-status">

                                Provider

                                <?php if (!empty($selectedProvider['location_name'])): ?>

                                    ·
                                    <?= client_message_h(
                                        $selectedProvider['location_name']
                                    ) ?>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>


                    <div class="chat-actions">

                        <a
                            href="dashboard.php?page=services&provider_id=<?= $selectedProviderId ?>"
                            class="chat-action"
                            title="View provider"
                            aria-label="View provider"
                        >
                            <i class="fa-regular fa-user"></i>
                        </a>

                    </div>

                </header>


                <!-- =================================================
                     BOOKING CONTEXT
                     ================================================= -->

                <?php if ($selectedBooking): ?>

                    <div class="booking-context">

                        <div class="booking-context-info">

                            <div class="booking-context-icon">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>


                            <div class="booking-context-text">

                                <span class="booking-context-label">
                                    Booking
                                </span>

                                <span class="booking-context-value">

                                    <?= client_message_h(
                                        $selectedBooking['service_name']
                                        ?? 'Service booking'
                                    ) ?>

                                    ·

                                    <?php

                                    $bookingTimestamp =
                                        strtotime(
                                            (string)$selectedBooking['booking_date']
                                        );

                                    if ($bookingTimestamp !== false) {
                                        echo client_message_h(
                                            date(
                                                'M j, Y \a\t g:i A',
                                                $bookingTimestamp
                                            )
                                        );
                                    }

                                    ?>

                                </span>

                            </div>

                        </div>


                        <a
                            href="dashboard.php?page=bookings&id=<?= (int)$selectedBooking['id'] ?>"
                            class="booking-context-link"
                        >
                            View booking
                        </a>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     CHAT MESSAGES
                     ================================================= -->

                <div
                    class="chat-messages"
                    id="chatMessages"
                >

                    <?php if (empty($conversationMessages)): ?>

                        <div class="no-messages">

                            <i class="fa-regular fa-comment-dots"></i>

                            <p>
                                Start the conversation with
                                <?= client_message_h(
                                    $selectedProvider['full_name']
                                ) ?>.
                            </p>

                        </div>

                    <?php else: ?>

                        <?php

                        $lastMessageDate = '';

                        foreach ($conversationMessages as $message):

                            $messageTimestamp =
                                strtotime(
                                    (string)$message['created_at']
                                );

                            if ($messageTimestamp === false) {
                                continue;
                            }

                            $messageDate =
                                date(
                                    'Y-m-d',
                                    $messageTimestamp
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Date Divider
                            |--------------------------------------------------------------------------
                            */

                            if ($messageDate !== $lastMessageDate):

                                $lastMessageDate =
                                    $messageDate;

                        ?>

                                <div class="message-date">

                                    <?= client_message_h(
                                        date(
                                            'M j, Y',
                                            $messageTimestamp
                                        )
                                    ) ?>

                                </div>

                        <?php endif; ?>


                        <?php

                        $isMine =
                            (int)$message['sender_user_id']
                            === $clientUserId;

                        ?>

                        <div
                            class="message-row <?= $isMine ? 'sent' : 'received' ?>"
                        >

                            <div class="message-bubble">

                                <p class="message-text">
                                    <?= nl2br(
                                        client_message_h(
                                            (string)$message['message']
                                        )
                                    ) ?>
                                </p>


                                <div class="message-meta">

                                    <time
                                        title="<?= client_message_h(
                                            client_message_full_time(
                                                (string)$message['created_at']
                                            )
                                        ) ?>"
                                    >
                                        <?= client_message_h(
                                            date(
                                                'g:i A',
                                                $messageTimestamp
                                            )
                                        ) ?>
                                    </time>

                                    <?php if ($isMine): ?>

                                        <span class="message-status">

                                            <?php if (
                                                !empty($message['is_read'])
                                            ): ?>

                                                <i class="fa-solid fa-check-double"></i>

                                            <?php else: ?>

                                                <i class="fa-solid fa-check"></i>

                                            <?php endif; ?>

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>


                <!-- =================================================
                     MESSAGE COMPOSER
                     ================================================= -->

                <form
                    method="POST"
                    action="dashboard.php?page=messages&provider_id=<?= $selectedProviderId ?><?= $selectedBooking ? '&booking_id=' . (int)$selectedBooking['id'] : '' ?>"
                    class="message-composer"
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
                        value="<?= client_message_h($csrfToken) ?>"
                    >

                    <input
                        type="hidden"
                        name="receiver_user_id"
                        value="<?= $selectedProviderId ?>"
                    >

                    <?php if ($selectedBooking): ?>

                        <input
                            type="hidden"
                            name="booking_id"
                            value="<?= (int)$selectedBooking['id'] ?>"
                        >

                    <?php endif; ?>


                    <div class="message-input-wrap">

                        <textarea
                            name="message"
                            id="messageInput"
                            class="message-input"
                            rows="1"
                            maxlength="5000"
                            placeholder="Write a message..."
                            required
                        ></textarea>


                        <div class="composer-footer">

                            <span class="composer-hint">
                                Press Enter to send · Shift + Enter for a new line
                            </span>

                            <span
                                class="character-count"
                                id="messageCounter"
                            >
                                0 / 5000
                            </span>

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="send-message-btn"
                        aria-label="Send message"
                        title="Send message"
                    >
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>

                </form>

            <?php endif; ?>

        </section>

    </div>

</section>


<script>

document.addEventListener('DOMContentLoaded', () => {

    const chatMessages =
        document.getElementById('chatMessages');

    const messageInput =
        document.getElementById('messageInput');

    const messageCounter =
        document.getElementById('messageCounter');


    /*
    |--------------------------------------------------------------------------
    | Scroll To Latest Message
    |--------------------------------------------------------------------------
    */

    if (chatMessages) {

        chatMessages.scrollTop =
            chatMessages.scrollHeight;
    }


    /*
    |--------------------------------------------------------------------------
    | Message Input
    |--------------------------------------------------------------------------
    */

    if (messageInput) {

        const resizeInput = () => {

            messageInput.style.height = 'auto';

            const maxHeight = 130;

            messageInput.style.height =
                Math.min(
                    messageInput.scrollHeight,
                    maxHeight
                ) + 'px';
        };


        /*
        |--------------------------------------------------------------------------
        | Auto Resize
        |--------------------------------------------------------------------------
        */

        messageInput.addEventListener(
            'input',
            resizeInput
        );

        resizeInput();


        /*
        |--------------------------------------------------------------------------
        | Character Counter
        |--------------------------------------------------------------------------
        */

        const updateCounter = () => {

            if (!messageCounter) {
                return;
            }

            messageCounter.textContent =
                `${messageInput.value.length} / 5000`;
        };


        messageInput.addEventListener(
            'input',
            updateCounter
        );

        updateCounter();


        /*
        |--------------------------------------------------------------------------
        | Enter To Send
        |--------------------------------------------------------------------------
        |
        | Enter = send
        | Shift + Enter = new line
        |
        */

        messageInput.addEventListener(
            'keydown',
            (event) => {

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
    }

});

</script>