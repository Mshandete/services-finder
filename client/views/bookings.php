<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Client Bookings Page
|--------------------------------------------------------------------------
| This page displays the client's bookings.
| Database integration can be added after the UI is confirmed.
|--------------------------------------------------------------------------
*/

// Make sure the session is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Current logged-in client
$clientId = $_SESSION['user_id'] ?? null;

// Demo booking data
// Later this will come directly from the bookings table.
$bookings = [
    [
        'id' => 1001,
        'provider' => 'John Plumbing Services',
        'category' => 'Plumbing',
        'service' => 'Kitchen Sink Repair',
        'date' => 'Aug 15, 2026',
        'time' => '10:00 AM',
        'location' => 'Mabibo, Dar es Salaam',
        'price' => 'TZS 35,000',
        'status' => 'upcoming',
        'image' => '../assets/images/i.jpg'
    ],
    [
        'id' => 1002,
        'provider' => 'Mshana Electrical Services',
        'category' => 'Electrical',
        'service' => 'Electrical Installation',
        'date' => 'Aug 18, 2026',
        'time' => '02:00 PM',
        'location' => 'Sinza, Dar es Salaam',
        'price' => 'TZS 50,000',
        'status' => 'pending',
        'image' => '../assets/images/a.jpg'
    ],
    [
        'id' => 1003,
        'provider' => 'CleanPro Tanzania',
        'category' => 'Cleaning',
        'service' => 'House Deep Cleaning',
        'date' => 'Jul 28, 2026',
        'time' => '09:00 AM',
        'location' => 'Kinondoni, Dar es Salaam',
        'price' => 'TZS 60,000',
        'status' => 'completed',
        'image' => '../assets/images/p.jpg'
    ],
    [
        'id' => 1004,
        'provider' => 'QuickFix Technicians',
        'category' => 'Appliance Repair',
        'service' => 'Washing Machine Repair',
        'date' => 'Jul 20, 2026',
        'time' => '11:00 AM',
        'location' => 'Ubungo, Dar es Salaam',
        'price' => 'TZS 45,000',
        'status' => 'cancelled',
        'image' => '../assets/images/h.jpg'
    ]
];


/*
|--------------------------------------------------------------------------
| Filter bookings
|--------------------------------------------------------------------------
*/

$upcomingBookings = array_filter(
    $bookings,
    fn(array $booking): bool => $booking['status'] === 'upcoming'
);

$pendingBookings = array_filter(
    $bookings,
    fn(array $booking): bool => $booking['status'] === 'pending'
);

$completedBookings = array_filter(
    $bookings,
    fn(array $booking): bool => $booking['status'] === 'completed'
);

$cancelledBookings = array_filter(
    $bookings,
    fn(array $booking): bool => $booking['status'] === 'cancelled'
);


/*
|--------------------------------------------------------------------------
| Helper function
|--------------------------------------------------------------------------
*/

function getBookingStatus(string $status): array
{
    return match ($status) {

        'upcoming' => [
            'label' => 'Upcoming',
            'icon' => 'fa-calendar-check',
            'class' => 'status-upcoming'
        ],

        'pending' => [
            'label' => 'Pending',
            'icon' => 'fa-clock',
            'class' => 'status-pending'
        ],

        'completed' => [
            'label' => 'Completed',
            'icon' => 'fa-circle-check',
            'class' => 'status-completed'
        ],

        'cancelled' => [
            'label' => 'Cancelled',
            'icon' => 'fa-circle-xmark',
            'class' => 'status-cancelled'
        ],

        default => [
            'label' => 'Unknown',
            'icon' => 'fa-circle-question',
            'class' => 'status-default'
        ]
    };
}

?>


<div class="bookings-page">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="bookings-header">

        <div class="bookings-header-text">

            <span class="bookings-small-title">
                <i class="fa-solid fa-calendar-days"></i>
                My Bookings
            </span>

            <h1>Manage Your Bookings</h1>

            <p>
                View, track and manage all your service bookings in one place.
            </p>

        </div>

        <a href="dashboard.php?page=services" class="browse-services-btn">

            <i class="fa-solid fa-magnifying-glass"></i>

            Find a Service

        </a>

    </div>


    <!-- =========================================================
         BOOKING SUMMARY
    ========================================================== -->

    <div class="booking-summary">

        <div class="booking-summary-card">

            <div class="summary-icon upcoming-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>

            <div class="summary-content">

                <span>Upcoming</span>

                <strong>
                    <?= count($upcomingBookings); ?>
                </strong>

            </div>

        </div>


        <div class="booking-summary-card">

            <div class="summary-icon pending-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="summary-content">

                <span>Pending</span>

                <strong>
                    <?= count($pendingBookings); ?>
                </strong>

            </div>

        </div>


        <div class="booking-summary-card">

            <div class="summary-icon completed-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="summary-content">

                <span>Completed</span>

                <strong>
                    <?= count($completedBookings); ?>
                </strong>

            </div>

        </div>


        <div class="booking-summary-card">

            <div class="summary-icon cancelled-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="summary-content">

                <span>Cancelled</span>

                <strong>
                    <?= count($cancelledBookings); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =========================================================
         BOOKING FILTER
    ========================================================== -->

    <div class="booking-controls">

        <div class="booking-tabs">

            <button
                class="booking-tab active"
                data-filter="all"
                type="button"
            >
                All
                <span><?= count($bookings); ?></span>
            </button>

            <button
                class="booking-tab"
                data-filter="upcoming"
                type="button"
            >
                Upcoming
                <span><?= count($upcomingBookings); ?></span>
            </button>

            <button
                class="booking-tab"
                data-filter="pending"
                type="button"
            >
                Pending
                <span><?= count($pendingBookings); ?></span>
            </button>

            <button
                class="booking-tab"
                data-filter="completed"
                type="button"
            >
                Completed
                <span><?= count($completedBookings); ?></span>
            </button>

            <button
                class="booking-tab"
                data-filter="cancelled"
                type="button"
            >
                Cancelled
                <span><?= count($cancelledBookings); ?></span>
            </button>

        </div>


        <div class="booking-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="bookingSearch"
                placeholder="Search bookings..."
                autocomplete="off"
            >

        </div>

    </div>


    <!-- =========================================================
         BOOKINGS LIST
    ========================================================== -->

    <div class="bookings-list" id="bookingsList">


        <?php if (empty($bookings)): ?>

            <!-- EMPTY STATE -->

            <div class="booking-empty-state">

                <div class="empty-booking-icon">

                    <i class="fa-regular fa-calendar-xmark"></i>

                </div>

                <h3>No bookings yet</h3>

                <p>
                    You haven't booked any service yet.
                    Find a provider and make your first booking.
                </p>

                <a href="dashboard.php?page=services">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Find Services

                </a>

            </div>


        <?php else: ?>


            <?php foreach ($bookings as $booking): ?>

                <?php

                $status = getBookingStatus($booking['status']);

                ?>

                <article
                    class="booking-card"
                    data-status="<?= htmlspecialchars($booking['status']); ?>"
                    data-search="
                        <?= htmlspecialchars(
                            strtolower(
                                $booking['provider'] . ' ' .
                                $booking['category'] . ' ' .
                                $booking['service'] . ' ' .
                                $booking['location']
                            )
                        ); ?>
                    "
                >


                    <!-- Provider -->

                    <div class="booking-provider">

                        <div class="provider-booking-image">

                            <img
                                src="<?= htmlspecialchars($booking['image']); ?>"
                                alt="<?= htmlspecialchars($booking['provider']); ?>"
                                onerror="this.src='../assets/images/d.jpg';"
                            >

                        </div>


                        <div class="provider-booking-info">

                            <span class="booking-category">

                                <?= htmlspecialchars($booking['category']); ?>

                            </span>

                            <h3>
                                <?= htmlspecialchars($booking['provider']); ?>
                            </h3>

                            <p>
                                <i class="fa-solid fa-briefcase"></i>

                                <?= htmlspecialchars($booking['service']); ?>
                            </p>

                        </div>

                    </div>


                    <!-- Booking Information -->

                    <div class="booking-information">


                        <div class="booking-info-item">

                            <i class="fa-regular fa-calendar"></i>

                            <div>

                                <span>Date</span>

                                <strong>
                                    <?= htmlspecialchars($booking['date']); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="booking-info-item">

                            <i class="fa-regular fa-clock"></i>

                            <div>

                                <span>Time</span>

                                <strong>
                                    <?= htmlspecialchars($booking['time']); ?>
                                </strong>

                            </div>

                        </div>


                        <div class="booking-info-item">

                            <i class="fa-solid fa-location-dot"></i>

                            <div>

                                <span>Location</span>

                                <strong>
                                    <?= htmlspecialchars($booking['location']); ?>
                                </strong>

                            </div>

                        </div>


                    </div>


                    <!-- Price + Status -->

                    <div class="booking-side">


                        <div class="booking-price">

                            <span>Estimated Price</span>

                            <strong>
                                <?= htmlspecialchars($booking['price']); ?>
                            </strong>

                        </div>


                        <div class="booking-status <?= htmlspecialchars($status['class']); ?>">

                            <i class="fa-solid <?= htmlspecialchars($status['icon']); ?>"></i>

                            <?= htmlspecialchars($status['label']); ?>

                        </div>


                        <button
                            type="button"
                            class="booking-details-btn"
                            data-booking-id="<?= (int) $booking['id']; ?>"
                        >

                            View Details

                            <i class="fa-solid fa-arrow-right"></i>

                        </button>


                    </div>

                </article>

            <?php endforeach; ?>


        <?php endif; ?>


        <!-- SEARCH EMPTY STATE -->

        <div
            class="booking-search-empty"
            id="bookingSearchEmpty"
            style="display: none;"
        >

            <div>

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <h3>No bookings found</h3>

            <p>
                We couldn't find any booking matching your search.
            </p>

        </div>


    </div>

</div>


<!-- =============================================================
     BOOKING DETAILS MODAL
============================================================= -->

<div
    class="booking-modal-overlay"
    id="bookingModal"
    aria-hidden="true"
>

    <div class="booking-modal">


        <div class="booking-modal-header">

            <div>

                <span>
                    Booking Details
                </span>

                <h2>
                    Booking #<span id="modalBookingId">---</span>
                </h2>

            </div>


            <button
                type="button"
                class="close-booking-modal"
                id="closeBookingModal"
                aria-label="Close"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <div class="booking-modal-body">

            <div class="modal-status">

                <i class="fa-solid fa-calendar-check"></i>

                <div>

                    <span>Booking Status</span>

                    <strong id="modalBookingStatus">
                        --
                    </strong>

                </div>

            </div>


            <div class="modal-message">

                <i class="fa-solid fa-circle-info"></i>

                <p>
                    Detailed booking information will appear here once
                    this booking is connected to the database.
                </p>

            </div>

        </div>


        <div class="booking-modal-footer">

            <button
                type="button"
                class="modal-close-btn"
                id="modalCloseBtn"
            >
                Close
            </button>

        </div>

    </div>

</div>


<script>

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Booking filter
    |--------------------------------------------------------------------------
    */

    const tabs = document.querySelectorAll(".booking-tab");
    const cards = document.querySelectorAll(".booking-card");

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {

                item.classList.remove("active");

            });

            this.classList.add("active");

            const filter = this.dataset.filter;

            cards.forEach(function (card) {

                const status = card.dataset.status;

                if (filter === "all" || status === filter) {

                    card.style.display = "";

                } else {

                    card.style.display = "none";

                }

            });

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Search bookings
    |--------------------------------------------------------------------------
    */

    const searchInput = document.getElementById("bookingSearch");
    const searchEmpty = document.getElementById("bookingSearchEmpty");

    if (searchInput) {

        searchInput.addEventListener("input", function () {

            const searchValue = this.value
                .toLowerCase()
                .trim();

            let visibleCards = 0;

            cards.forEach(function (card) {

                const searchableText =
                    card.dataset.search.toLowerCase();

                if (
                    searchValue === "" ||
                    searchableText.includes(searchValue)
                ) {

                    card.style.display = "";
                    visibleCards++;

                } else {

                    card.style.display = "none";

                }

            });


            if (searchValue !== "" && visibleCards === 0) {

                searchEmpty.style.display = "block";

            } else {

                searchEmpty.style.display = "none";

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Booking details modal
    |--------------------------------------------------------------------------
    */

    const modal = document.getElementById("bookingModal");
    const modalId = document.getElementById("modalBookingId");
    const modalStatus = document.getElementById("modalBookingStatus");

    const closeModalButton =
        document.getElementById("closeBookingModal");

    const modalCloseButton =
        document.getElementById("modalCloseBtn");


    document.querySelectorAll(".booking-details-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const bookingId = this.dataset.bookingId;

                const card = this.closest(".booking-card");

                const status =
                    card.dataset.status;

                modalId.textContent = bookingId;

                modalStatus.textContent =
                    status.charAt(0).toUpperCase() +
                    status.slice(1);

                modal.classList.add("show");

                modal.setAttribute(
                    "aria-hidden",
                    "false"
                );

            });

        });


    function closeBookingModal() {

        modal.classList.remove("show");

        modal.setAttribute(
            "aria-hidden",
            "true"
        );

    }


    if (closeModalButton) {

        closeModalButton.addEventListener(
            "click",
            closeBookingModal
        );

    }


    if (modalCloseButton) {

        modalCloseButton.addEventListener(
            "click",
            closeBookingModal
        );

    }


    modal.addEventListener("click", function (event) {

        if (event.target === modal) {

            closeBookingModal();

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Escape key closes modal
    |--------------------------------------------------------------------------
    */

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            closeBookingModal();

        }

    });

});

</script>