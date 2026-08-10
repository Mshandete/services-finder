<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$clientId = $_SESSION['user_id'] ?? null;

/*
|--------------------------------------------------------------------------
| Demo Conversations
|--------------------------------------------------------------------------
| Later these will come from messages/conversations tables.
|--------------------------------------------------------------------------
*/

$conversations = [
    [
        'id' => 1,
        'provider' => 'John Plumbing Services',
        'category' => 'Plumbing',
        'location' => 'Mabibo, Dar es Salaam',
        'image' => '../assets/images/provider-default.jpg',
        'status' => 'online',
        'last_message' => 'I can come tomorrow at 10 AM.',
        'time' => '2 min',
        'unread' => 1,
        'messages' => [
            [
                'sender' => 'provider',
                'message' => 'Hello, how can I help you?',
                'time' => '09:41 AM'
            ],
            [
                'sender' => 'client',
                'message' => 'I need help repairing my kitchen sink.',
                'time' => '09:43 AM'
            ],
            [
                'sender' => 'provider',
                'message' => 'Sure. I can come tomorrow at 10 AM.',
                'time' => '09:45 AM'
            ]
        ],
        'booking' => [
            'id' => '1001',
            'service' => 'Kitchen Sink Repair',
            'date' => 'Aug 15, 2026',
            'time' => '10:00 AM',
            'status' => 'Upcoming'
        ]
    ],
    [
        'id' => 2,
        'provider' => 'Mshana Electrical Services',
        'category' => 'Electrical',
        'location' => 'Sinza, Dar es Salaam',
        'image' => '../assets/images/provider-default.jpg',
        'status' => 'offline',
        'last_message' => 'Are you available this weekend?',
        'time' => '1 hr',
        'unread' => 0,
        'messages' => [
            [
                'sender' => 'client',
                'message' => 'Are you available to install new lights?',
                'time' => '08:30 AM'
            ],
            [
                'sender' => 'provider',
                'message' => 'Yes, I am available this weekend.',
                'time' => '08:45 AM'
            ],
            [
                'sender' => 'provider',
                'message' => 'What day would work best for you?',
                'time' => '08:46 AM'
            ]
        ],
        'booking' => [
            'id' => '1002',
            'service' => 'Electrical Installation',
            'date' => 'Aug 18, 2026',
            'time' => '02:00 PM',
            'status' => 'Pending'
        ]
    ],
    [
        'id' => 3,
        'provider' => 'CleanPro Tanzania',
        'category' => 'Cleaning',
        'location' => 'Kinondoni, Dar es Salaam',
        'image' => '../assets/images/provider-default.jpg',
        'status' => 'offline',
        'last_message' => 'Your booking has been confirmed.',
        'time' => 'Yesterday',
        'unread' => 0,
        'messages' => [
            [
                'sender' => 'provider',
                'message' => 'Your cleaning appointment has been confirmed.',
                'time' => 'Yesterday'
            ],
            [
                'sender' => 'client',
                'message' => 'Perfect, thank you.',
                'time' => 'Yesterday'
            ]
        ],
        'booking' => [
            'id' => '1003',
            'service' => 'House Deep Cleaning',
            'date' => 'Jul 28, 2026',
            'time' => '09:00 AM',
            'status' => 'Completed'
        ]
    ]
];
?>

<div class="messages-page">
    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->
    <div class="messages-header">
        <div>
            <span class="messages-label">
                <i class="fa-regular fa-comments"></i>
                Communication
            </span>
            <h1>Messages</h1>
            <p>Stay connected with the people providing your services.</p>
        </div>
    </div>

    <!-- =====================================================
         MESSAGES WORKSPACE
    ====================================================== -->
    <div class="messages-workspace">
        <!-- =================================================
             CONVERSATIONS
        ================================================== -->
        <aside class="conversations-panel">
            <div class="conversations-heading">
                <div>
                    <span>Conversations</span>
                    <strong><?= count($conversations); ?></strong>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="conversation-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="conversationSearch" placeholder="Search conversations..." autocomplete="off">
            </div>

            <!-- CONVERSATION LIST -->
            <div class="conversation-list" id="conversationList">
                <?php foreach ($conversations as $index => $conversation): ?>
                    <button type="button" class="conversation-item <?= $index === 0 ? 'active' : ''; ?>" data-conversation-id="<?= (int) $conversation['id']; ?>" data-search="<?= htmlspecialchars(strtolower($conversation['provider'] . ' ' . $conversation['category'] . ' ' . $conversation['last_message'])); ?>">
                        <div class="conversation-avatar">
                            <img src="<?= htmlspecialchars($conversation['image']); ?>" alt="<?= htmlspecialchars($conversation['provider']); ?>" onerror="this.src='../assets/images/default-provider.png';">
                            <?php if ($conversation['status'] === 'online'): ?>
                                <span class="online-dot"></span>
                            <?php endif; ?>
                        </div>

                        <div class="conversation-content">
                            <div class="conversation-top">
                                <strong><?= htmlspecialchars($conversation['provider']); ?></strong>
                                <time><?= htmlspecialchars($conversation['time']); ?></time>
                            </div>

                            <div class="conversation-bottom">
                                <span><?= htmlspecialchars($conversation['last_message']); ?></span>
                                <?php if ($conversation['unread'] > 0): ?>
                                    <b class="unread-count"><?= (int) $conversation['unread']; ?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                    </button>
                <?php endforeach; ?>

                <div class="conversation-no-results" id="conversationNoResults">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>No conversations found.</p>
                </div>
            </div>
        </aside>

        <!-- =================================================
             CHAT AREA
        ================================================== -->
        <section class="chat-panel">
            <?php $activeConversation = $conversations[0]; ?>

            <!-- CHAT HEADER -->
            <div class="chat-header">
                <button type="button" class="mobile-back-button" id="mobileBackButton" aria-label="Back to conversations">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>

                <div class="chat-provider-avatar">
                    <img id="chatProviderImage" src="<?= htmlspecialchars($activeConversation['image']); ?>" alt="<?= htmlspecialchars($activeConversation['provider']); ?>" onerror="this.src='../assets/images/default-provider.png';">
                    <span id="chatOnlineDot" class="chat-online-dot <?= $activeConversation['status'] === 'online' ? 'visible' : ''; ?>"></span>
                </div>

                <div class="chat-provider-info">
                    <h2 id="chatProviderName"><?= htmlspecialchars($activeConversation['provider']); ?></h2>
                    <span id="chatProviderMeta">
                        <?= htmlspecialchars($activeConversation['category']); ?>
                        <span class="chat-divider">·</span>
                        <?= htmlspecialchars($activeConversation['location']); ?>
                    </span>
                </div>

                <button type="button" class="chat-more-button" aria-label="More options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>

            <!-- BOOKING CONTEXT -->
            <div class="chat-booking-context">
                <div class="booking-context-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>

                <div class="booking-context-info">
                    <span>Booking #<?= htmlspecialchars($activeConversation['booking']['id']); ?></span>
                    <strong id="chatBookingService"><?= htmlspecialchars($activeConversation['booking']['service']); ?></strong>
                    <small id="chatBookingSchedule">
                        <?= htmlspecialchars($activeConversation['booking']['date']); ?> · <?= htmlspecialchars($activeConversation['booking']['time']); ?>
                    </small>
                </div>

                <span class="booking-context-status" id="chatBookingStatus">
                    <?= htmlspecialchars($activeConversation['booking']['status']); ?>
                </span>
            </div>

            <!-- MESSAGES -->
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($activeConversation['messages'] as $message): ?>
                    <div class="message-row <?= $message['sender'] === 'client' ? 'message-client' : 'message-provider'; ?>">
                        <div class="message-bubble">
                            <p><?= htmlspecialchars($message['message']); ?></p>
                            <time><?= htmlspecialchars($message['time']); ?></time>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- TYPING AREA -->
            <form class="message-composer" id="messageComposer">
                <button type="button" class="composer-attachment" aria-label="Attach file">
                    <i class="fa-solid fa-paperclip"></i>
                </button>

                <input type="text" id="messageInput" placeholder="Write a message..." autocomplete="off">

                <button type="submit" class="send-message-button" aria-label="Send message">
                    <i class="fa-solid fa-arrow-up"></i>
                </button>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Conversation data
    |--------------------------------------------------------------------------
    */
    const conversations = <?= json_encode($conversations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */
    const conversationItems = document.querySelectorAll(".conversation-item");
    const chatProviderName = document.getElementById("chatProviderName");
    const chatProviderMeta = document.getElementById("chatProviderMeta");
    const chatProviderImage = document.getElementById("chatProviderImage");
    const chatOnlineDot = document.getElementById("chatOnlineDot");
    const chatMessages = document.getElementById("chatMessages");
    const chatBookingService = document.getElementById("chatBookingService");
    const chatBookingSchedule = document.getElementById("chatBookingSchedule");
    const chatBookingStatus = document.getElementById("chatBookingStatus");

    /*
    |--------------------------------------------------------------------------
    | Load conversation
    |--------------------------------------------------------------------------
    */
    function loadConversation(id) {
        const conversation = conversations.find(function (item) {
            return Number(item.id) === Number(id);
        });

        if (!conversation) {
            return;
        }

        chatProviderName.textContent = conversation.provider;
        chatProviderMeta.innerHTML = escapeHtml(conversation.category) + ' <span class="chat-divider">·</span> ' + escapeHtml(conversation.location);
        chatProviderImage.src = conversation.image;

        if (conversation.status === "online") {
            chatOnlineDot.classList.add("visible");
        } else {
            chatOnlineDot.classList.remove("visible");
        }

        chatBookingService.textContent = conversation.booking.service;
        chatBookingSchedule.textContent = conversation.booking.date + " · " + conversation.booking.time;
        chatBookingStatus.textContent = conversation.booking.status;

        renderMessages(conversation.messages);
    }

    /*
    |--------------------------------------------------------------------------
    | Render messages
    |--------------------------------------------------------------------------
    */
    function renderMessages(messages) {
        chatMessages.innerHTML = "";

        messages.forEach(function (message) {
            const row = document.createElement("div");
            row.className = "message-row " + (message.sender === "client" ? "message-client" : "message-provider");

            const bubble = document.createElement("div");
            bubble.className = "message-bubble";

            const text = document.createElement("p");
            text.textContent = message.message;

            const time = document.createElement("time");
            time.textContent = message.time;

            bubble.appendChild(text);
            bubble.appendChild(time);
            row.appendChild(bubble);
            chatMessages.appendChild(row);
        });

        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    /*
    |--------------------------------------------------------------------------
    | Conversation click
    |--------------------------------------------------------------------------
    */
    conversationItems.forEach(function (item) {
        item.addEventListener("click", function () {
            conversationItems.forEach(function (conversation) {
                conversation.classList.remove("active");
            });

            this.classList.add("active");
            const id = this.dataset.conversationId;
            loadConversation(id);

            /* Mobile: open chat */
            document.querySelector(".messages-workspace").classList.add("chat-open");

            /* Remove unread indicator */
            const unread = this.querySelector(".unread-count");
            if (unread) {
                unread.remove();
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Search conversations
    |--------------------------------------------------------------------------
    */
    const searchInput = document.getElementById("conversationSearch");
    const noResults = document.getElementById("conversationNoResults");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const value = this.value.toLowerCase().trim();
            let found = 0;

            conversationItems.forEach(function (item) {
                const searchable = item.dataset.search.toLowerCase();

                if (value === "" || searchable.includes(value)) {
                    item.style.display = "";
                    found++;
                } else {
                    item.style.display = "none";
                }
            });

            noResults.style.display = found === 0 ? "flex" : "none";
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Send message
    |--------------------------------------------------------------------------
    */
    const composer = document.getElementById("messageComposer");
    const messageInput = document.getElementById("messageInput");

    if (composer) {
        composer.addEventListener("submit", function (event) {
            event.preventDefault();

            const message = messageInput.value.trim();
            if (!message) {
                return;
            }

            const row = document.createElement("div");
            row.className = "message-row message-client";

            const bubble = document.createElement("div");
            bubble.className = "message-bubble";

            const text = document.createElement("p");
            text.textContent = message;

            const time = document.createElement("time");
            time.textContent = getCurrentTime();

            bubble.appendChild(text);
            bubble.appendChild(time);
            row.appendChild(bubble);
            chatMessages.appendChild(row);

            messageInput.value = "";
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile back
    |--------------------------------------------------------------------------
    */
    const mobileBack = document.getElementById("mobileBackButton");

    if (mobileBack) {
        mobileBack.addEventListener("click", function () {
            document.querySelector(".messages-workspace").classList.remove("chat-open");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll chat to bottom
    |--------------------------------------------------------------------------
    */
    chatMessages.scrollTop = chatMessages.scrollHeight;
});
</script>