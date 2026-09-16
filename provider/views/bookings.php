<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$providerUserId = (int)($_SESSION['user_id'] ?? 0);

if ($providerUserId <= 0) {
    exit('Unauthorized access.');
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function providerBookingEscape(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

function providerBookingStatusClass(string $status): string
{
    return match ($status) {
        'pending'     => 'booking-status-pending',
        'accepted'    => 'booking-status-accepted',
        'in_progress' => 'booking-status-progress',
        'completed'   => 'booking-status-completed',
        'cancelled'   => 'booking-status-cancelled',
        'rejected'    => 'booking-status-rejected',
        default       => 'booking-status-default',
    };
}

function providerBookingStatusLabel(string $status): string
{
    return match ($status) {
        'pending'     => 'New Request',
        'accepted'    => 'Accepted',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
        'rejected'    => 'Rejected',
        default       => ucfirst(str_replace('_', ' ', $status)),
    };
}

function providerBookingDateLabel(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return providerBookingEscape($date);
    }

    return date('M j, Y \a\t g:i A', $timestamp);
}

function providerBookingRelativeTime(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '';
    }

    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    }

    if ($difference < 3600) {
        $minutes = (int)floor($difference / 60);
        return $minutes . ' min' . ($minutes === 1 ? '' : 's') . ' ago';
    }

    if ($difference < 86400) {
        $hours = (int)floor($difference / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }

    if ($difference < 604800) {
        $days = (int)floor($difference / 86400);
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    return date('M j, Y', $timestamp);
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['provider_booking_csrf_token'])) {
    $_SESSION['provider_booking_csrf_token'] = bin2hex(random_bytes(32));
}

$bookingCsrfToken = $_SESSION['provider_booking_csrf_token'];

/*
|--------------------------------------------------------------------------
| Current booking detail
|--------------------------------------------------------------------------
*/

$bookingId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$bookingId = $bookingId !== false && $bookingId !== null
    ? (int)$bookingId
    : 0;

/*
|--------------------------------------------------------------------------
| Handle Provider Actions
|--------------------------------------------------------------------------
*/

$bookingMessage = '';
$bookingMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($postedToken) ||
        !hash_equals($bookingCsrfToken, $postedToken)
    ) {
        $bookingMessage = 'Your session has expired. Please try again.';
        $bookingMessageType = 'error';
    } else {

        $action = $_POST['booking_action'] ?? '';

        $actionBookingId = filter_var(
            $_POST['booking_id'] ?? 0,
            FILTER_VALIDATE_INT
        );

        $actionBookingId = $actionBookingId !== false
            ? (int)$actionBookingId
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Accept Booking
        |--------------------------------------------------------------------------
        */

        if ($action === 'accept' && $actionBookingId > 0) {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET status = 'accepted'
                WHERE id = ?
                  AND provider_user_id = ?
                  AND status = 'pending'
                LIMIT 1
            ");

            $stmt->execute([
                $actionBookingId,
                $providerUserId
            ]);

            if ($stmt->rowCount() > 0) {

                header(
                    'Location: dashboard.php?page=bookings&id='
                    . $actionBookingId
                    . '&updated=accepted'
                );
                exit;
            }

            $bookingMessage = 'This booking could not be accepted. It may have already been updated.';
            $bookingMessageType = 'error';
        }

        /*
        |--------------------------------------------------------------------------
        | Reject Booking
        |--------------------------------------------------------------------------
        */

        elseif ($action === 'reject' && $actionBookingId > 0) {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET status = 'rejected'
                WHERE id = ?
                  AND provider_user_id = ?
                  AND status = 'pending'
                LIMIT 1
            ");

            $stmt->execute([
                $actionBookingId,
                $providerUserId
            ]);

            if ($stmt->rowCount() > 0) {

                header(
                    'Location: dashboard.php?page=bookings&id='
                    . $actionBookingId
                    . '&updated=rejected'
                );
                exit;
            }

            $bookingMessage = 'This booking could not be rejected. It may have already been updated.';
            $bookingMessageType = 'error';
        }

        /*
        |--------------------------------------------------------------------------
        | Start Service
        |--------------------------------------------------------------------------
        */

        elseif ($action === 'start' && $actionBookingId > 0) {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET status = 'in_progress'
                WHERE id = ?
                  AND provider_user_id = ?
                  AND status = 'accepted'
                LIMIT 1
            ");

            $stmt->execute([
                $actionBookingId,
                $providerUserId
            ]);

            if ($stmt->rowCount() > 0) {

                header(
                    'Location: dashboard.php?page=bookings&id='
                    . $actionBookingId
                    . '&updated=in_progress'
                );
                exit;
            }

            $bookingMessage = 'The service could not be started.';
            $bookingMessageType = 'error';
        }

        /*
        |--------------------------------------------------------------------------
        | Complete Service
        |--------------------------------------------------------------------------
        */

        elseif ($action === 'complete' && $actionBookingId > 0) {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET status = 'completed'
                WHERE id = ?
                  AND provider_user_id = ?
                  AND status = 'in_progress'
                LIMIT 1
            ");

            $stmt->execute([
                $actionBookingId,
                $providerUserId
            ]);

            if ($stmt->rowCount() > 0) {

                header(
                    'Location: dashboard.php?page=bookings&id='
                    . $actionBookingId
                    . '&updated=completed'
                );
                exit;
            }

            $bookingMessage = 'The booking could not be marked as completed.';
            $bookingMessageType = 'error';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

$updated = $_GET['updated'] ?? '';

if ($updated === 'accepted') {
    $bookingMessage = 'Booking accepted successfully.';
    $bookingMessageType = 'success';
}

elseif ($updated === 'rejected') {
    $bookingMessage = 'Booking request rejected.';
    $bookingMessageType = 'success';
}

elseif ($updated === 'in_progress') {
    $bookingMessage = 'Service marked as in progress.';
    $bookingMessageType = 'success';
}

elseif ($updated === 'completed') {
    $bookingMessage = 'Booking marked as completed.';
    $bookingMessageType = 'success';
}

/*
|--------------------------------------------------------------------------
| Booking Detail
|--------------------------------------------------------------------------
*/

$bookingDetail = null;

if ($bookingId > 0) {

    $stmt = $pdo->prepare("
        SELECT
            b.id,
            b.client_user_id,
            b.provider_user_id,
            b.service_id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,
            b.created_at,
            b.updated_at,

            u.full_name AS client_name,
            u.phone AS client_phone,
            u.email AS client_email,

            ps.service_name,
            ps.description AS service_description,
            ps.price,
            ps.price_type,
            ps.estimated_duration,
            ps.service_image

        FROM bookings b

        INNER JOIN users u
            ON u.id = b.client_user_id

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id
           AND ps.provider_user_id = b.provider_user_id

        WHERE b.id = ?
          AND b.provider_user_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $bookingId,
        $providerUserId
    ]);

    $bookingDetail = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bookingDetail) {
        $bookingMessage = 'Booking not found or you do not have access to it.';
        $bookingMessageType = 'error';
        $bookingId = 0;
    }
}

/*
|--------------------------------------------------------------------------
| Booking List
|--------------------------------------------------------------------------
*/

$pendingBookings = [];
$otherBookings = [];

if ($bookingId === 0) {

    /*
    |--------------------------------------------------------------------------
    | New Requests
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            b.id,
            b.client_user_id,
            b.service_id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,
            b.created_at,

            u.full_name AS client_name,

            ps.service_name,
            ps.price,
            ps.price_type,
            ps.estimated_duration

        FROM bookings b

        INNER JOIN users u
            ON u.id = b.client_user_id

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id
           AND ps.provider_user_id = b.provider_user_id

        WHERE b.provider_user_id = ?
          AND b.status = 'pending'

        ORDER BY b.created_at DESC
    ");

    $stmt->execute([
        $providerUserId
    ]);

    $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Other Bookings
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            b.id,
            b.client_user_id,
            b.service_id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,
            b.created_at,
            b.updated_at,

            u.full_name AS client_name,

            ps.service_name,
            ps.price,
            ps.price_type,
            ps.estimated_duration

        FROM bookings b

        INNER JOIN users u
            ON u.id = b.client_user_id

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id
           AND ps.provider_user_id = b.provider_user_id

        WHERE b.provider_user_id = ?
          AND b.status <> 'pending'

        ORDER BY
            CASE b.status
                WHEN 'accepted' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'cancelled' THEN 4
                WHEN 'rejected' THEN 5
                ELSE 6
            END,
            b.updated_at DESC
    ");

    $stmt->execute([
        $providerUserId
    ]);

    $otherBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="provider-bookings-page">

    <?php if ($bookingMessage !== ''): ?>

        <div class="provider-booking-alert <?= providerBookingEscape($bookingMessageType) ?>">
            <span class="provider-booking-alert-icon">
                <?php if ($bookingMessageType === 'success'): ?>
                    <i class="fa-solid fa-circle-check"></i>
                <?php else: ?>
                    <i class="fa-solid fa-circle-exclamation"></i>
                <?php endif; ?>
            </span>

            <span>
                <?= providerBookingEscape($bookingMessage) ?>
            </span>
        </div>

    <?php endif; ?>


    <?php if ($bookingDetail): ?>

        <!-- =========================================================
             BOOKING DETAIL
        ========================================================== -->

        <section class="provider-booking-detail-page">

            <div class="provider-booking-detail-top">

                <a
                    href="dashboard.php?page=bookings"
                    class="provider-booking-back"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Bookings</span>
                </a>

            </div>


            <div class="provider-booking-detail-header">

                <div>

                    <span class="provider-booking-eyebrow">
                        Booking Request
                    </span>

                    <h1>
                        <?= providerBookingEscape(
                            $bookingDetail['service_name'] ?? 'Service Request'
                        ) ?>
                    </h1>

                    <p>
                        Requested by
                        <strong>
                            <?= providerBookingEscape(
                                $bookingDetail['client_name'] ?? 'Client'
                            ) ?>
                        </strong>
                    </p>

                </div>


                <div class="provider-booking-detail-status">

                    <span class="<?= providerBookingStatusClass(
                        (string)$bookingDetail['status']
                    ) ?>">

                        <?= providerBookingEscape(
                            providerBookingStatusLabel(
                                (string)$bookingDetail['status']
                            )
                        ) ?>

                    </span>

                </div>

            </div>


            <div class="provider-booking-detail-layout">

                <div class="provider-booking-detail-main">

                    <!-- SERVICE -->

                    <div class="provider-booking-detail-section">

                        <div class="provider-booking-section-heading">
                            <i class="fa-solid fa-briefcase"></i>
                            <h2>Service</h2>
                        </div>

                        <div class="provider-booking-service-summary">

                            <div>

                                <h3>
                                    <?= providerBookingEscape(
                                        $bookingDetail['service_name']
                                        ?? 'Service'
                                    ) ?>
                                </h3>

                                <?php if (!empty($bookingDetail['service_description'])): ?>

                                    <p>
                                        <?= providerBookingEscape(
                                            $bookingDetail['service_description']
                                        ) ?>
                                    </p>

                                <?php endif; ?>

                            </div>


                            <?php if ($bookingDetail['price'] !== null): ?>

                                <div class="provider-booking-price">

                                    <strong>
                                        TZS
                                        <?= number_format(
                                            (float)$bookingDetail['price'],
                                            0
                                        ) ?>
                                    </strong>

                                    <?php if (!empty($bookingDetail['price_type'])): ?>

                                        <span>
                                            <?= providerBookingEscape(
                                                ucfirst(
                                                    (string)$bookingDetail['price_type']
                                                )
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- CLIENT -->

                    <div class="provider-booking-detail-section">

                        <div class="provider-booking-section-heading">
                            <i class="fa-regular fa-user"></i>
                            <h2>Client</h2>
                        </div>

                        <div class="provider-booking-client-info">

                            <strong>
                                <?= providerBookingEscape(
                                    $bookingDetail['client_name']
                                ) ?>
                            </strong>

                            <?php if (!empty($bookingDetail['client_phone'])): ?>

                                <a
                                    href="tel:<?= providerBookingEscape(
                                        $bookingDetail['client_phone']
                                    ) ?>"
                                >
                                    <i class="fa-solid fa-phone"></i>
                                    <?= providerBookingEscape(
                                        $bookingDetail['client_phone']
                                    ) ?>
                                </a>

                            <?php endif; ?>

                            <?php if (!empty($bookingDetail['client_email'])): ?>

                                <a
                                    href="mailto:<?= providerBookingEscape(
                                        $bookingDetail['client_email']
                                    ) ?>"
                                >
                                    <i class="fa-regular fa-envelope"></i>
                                    <?= providerBookingEscape(
                                        $bookingDetail['client_email']
                                    ) ?>
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- WHEN -->

                    <div class="provider-booking-detail-section">

                        <div class="provider-booking-section-heading">
                            <i class="fa-regular fa-clock"></i>
                            <h2>When</h2>
                        </div>

                        <div class="provider-booking-info-row">

                            <strong>
                                <?= date(
                                    'M j, Y \a\t g:i A',
                                    strtotime(
                                        (string)$bookingDetail['booking_date']
                                    )
                                ) ?>
                            </strong>

                            <?php
                            $bookingTimestamp = strtotime(
                                (string)$bookingDetail['booking_date']
                            );

                            $isAsap =
                                $bookingTimestamp !== false &&
                                abs($bookingTimestamp - time()) <= 300;
                            ?>

                            <?php if ($isAsap): ?>

                                <span class="provider-booking-asap">
                                    <i class="fa-solid fa-bolt"></i>
                                    ASAP
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- LOCATION -->

                    <div class="provider-booking-detail-section">

                        <div class="provider-booking-section-heading">
                            <i class="fa-solid fa-location-dot"></i>
                            <h2>Location</h2>
                        </div>

                        <p class="provider-booking-location-text">

                            <?= !empty($bookingDetail['location'])
                                ? providerBookingEscape(
                                    $bookingDetail['location']
                                )
                                : 'No location provided'
                            ?>

                        </p>

                    </div>


                    <!-- CLIENT NOTE -->

                    <?php if (!empty($bookingDetail['notes'])): ?>

                        <div class="provider-booking-detail-section">

                            <div class="provider-booking-section-heading">
                                <i class="fa-regular fa-note-sticky"></i>
                                <h2>Client Note</h2>
                            </div>

                            <div class="provider-booking-note">

                                <?= nl2br(
                                    providerBookingEscape(
                                        $bookingDetail['notes']
                                    )
                                ) ?>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- REQUESTED -->

                    <div class="provider-booking-requested-time">

                        <span>Requested</span>

                        <strong>
                            <?= providerBookingEscape(
                                providerBookingDateLabel(
                                    (string)$bookingDetail['created_at']
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>


                <!-- ACTION SIDE -->

                <aside class="provider-booking-detail-actions">

                    <?php if ($bookingDetail['status'] === 'pending'): ?>

                        <div class="provider-booking-next-step">

                            <span class="provider-booking-next-label">
                                Next step
                            </span>

                            <h3>Review this request</h3>

                            <p>
                                Accept the request if you are available
                                to provide this service.
                            </p>

                        </div>


                        <div class="provider-booking-action-buttons">

                            <form
                                method="POST"
                                action="dashboard.php?page=bookings&id=<?= (int)$bookingDetail['id'] ?>"
                                class="provider-booking-action-form"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= providerBookingEscape(
                                        $bookingCsrfToken
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="booking_action"
                                    value="accept"
                                >

                                <input
                                    type="hidden"
                                    name="booking_id"
                                    value="<?= (int)$bookingDetail['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="provider-booking-accept-button"
                                >
                                    <i class="fa-solid fa-check"></i>
                                    Accept Request
                                </button>

                            </form>


                            <form
                                method="POST"
                                action="dashboard.php?page=bookings&id=<?= (int)$bookingDetail['id'] ?>"
                                class="provider-booking-action-form"
                                onsubmit="return confirm('Are you sure you want to reject this booking request?');"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= providerBookingEscape(
                                        $bookingCsrfToken
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="booking_action"
                                    value="reject"
                                >

                                <input
                                    type="hidden"
                                    name="booking_id"
                                    value="<?= (int)$bookingDetail['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="provider-booking-reject-button"
                                >
                                    Reject Request
                                </button>

                            </form>

                        </div>

                    <?php elseif ($bookingDetail['status'] === 'accepted'): ?>

                        <div class="provider-booking-next-step">

                            <span class="provider-booking-next-label">
                                Next step
                            </span>

                            <h3>Contact your client</h3>

                            <p>
                                The booking has been accepted.
                                You can now coordinate with the client.
                            </p>

                        </div>


                        <a
                            href="dashboard.php?page=messages"
                            class="provider-booking-message-button"
                        >
                            <i class="fa-regular fa-comment"></i>
                            Message Client
                        </a>


                        <form
                            method="POST"
                            action="dashboard.php?page=bookings&id=<?= (int)$bookingDetail['id'] ?>"
                            class="provider-booking-action-form provider-booking-start-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= providerBookingEscape(
                                    $bookingCsrfToken
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="booking_action"
                                value="start"
                            >

                            <input
                                type="hidden"
                                name="booking_id"
                                value="<?= (int)$bookingDetail['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="provider-booking-start-button"
                            >
                                <i class="fa-solid fa-play"></i>
                                Start Service
                            </button>

                        </form>

                    <?php elseif ($bookingDetail['status'] === 'in_progress'): ?>

                        <div class="provider-booking-next-step">

                            <span class="provider-booking-next-label">
                                Current booking
                            </span>

                            <h3>Service in progress</h3>

                            <p>
                                Mark the booking as completed when the
                                service has been finished.
                            </p>

                        </div>


                        <a
                            href="dashboard.php?page=messages"
                            class="provider-booking-message-button"
                        >
                            <i class="fa-regular fa-comment"></i>
                            Message Client
                        </a>


                        <form
                            method="POST"
                            action="dashboard.php?page=bookings&id=<?= (int)$bookingDetail['id'] ?>"
                            class="provider-booking-action-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= providerBookingEscape(
                                    $bookingCsrfToken
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="booking_action"
                                value="complete"
                            >

                            <input
                                type="hidden"
                                name="booking_id"
                                value="<?= (int)$bookingDetail['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="provider-booking-complete-button"
                            >
                                <i class="fa-solid fa-check-double"></i>
                                Mark as Completed
                            </button>

                        </form>

                    <?php elseif ($bookingDetail['status'] === 'completed'): ?>

                        <div class="provider-booking-completed-state">

                            <i class="fa-solid fa-circle-check"></i>

                            <h3>Booking completed</h3>

                            <p>
                                This service request has been completed
                                successfully.
                            </p>

                        </div>

                    <?php elseif ($bookingDetail['status'] === 'rejected'): ?>

                        <div class="provider-booking-closed-state">

                            <i class="fa-solid fa-ban"></i>

                            <h3>Request rejected</h3>

                            <p>
                                This booking request is no longer active.
                            </p>

                        </div>

                    <?php elseif ($bookingDetail['status'] === 'cancelled'): ?>

                        <div class="provider-booking-closed-state">

                            <i class="fa-solid fa-ban"></i>

                            <h3>Booking cancelled</h3>

                            <p>
                                This booking is no longer active.
                            </p>

                        </div>

                    <?php endif; ?>

                </aside>

            </div>

        </section>


    <?php else: ?>

        <!-- =========================================================
             BOOKING LIST
        ========================================================== -->

        <section class="provider-bookings-page-header">

            <div>

                <span class="provider-page-eyebrow">
                    Bookings
                </span>

                <h1>Service requests</h1>

                <p>
                    Review requests from clients and manage your active work.
                </p>

            </div>

        </section>


        <!-- NEW REQUESTS -->

        <?php if (!empty($pendingBookings)): ?>

            <section class="provider-booking-section provider-new-requests">

                <div class="provider-booking-section-header">

                    <div>

                        <h2>New requests</h2>

                        <span>
                            <?= count($pendingBookings) ?>
                            request<?= count($pendingBookings) === 1 ? '' : 's' ?>
                            waiting for your response
                        </span>

                    </div>

                </div>


                <div class="provider-booking-list">

                    <?php foreach ($pendingBookings as $booking): ?>

                        <article class="provider-booking-card provider-booking-card-new">

                            <div class="provider-booking-card-main">

                                <div class="provider-booking-card-heading">

                                    <div>

                                        <span class="provider-booking-new-label">
                                            New Request
                                        </span>

                                        <h3>
                                            <?= providerBookingEscape(
                                                $booking['service_name']
                                                ?? 'Service Request'
                                            ) ?>
                                        </h3>

                                    </div>


                                    <?php if ($booking['price'] !== null): ?>

                                        <strong class="provider-booking-card-price">
                                            TZS
                                            <?= number_format(
                                                (float)$booking['price'],
                                                0
                                            ) ?>
                                        </strong>

                                    <?php endif; ?>

                                </div>


                                <div class="provider-booking-card-client">

                                    <i class="fa-regular fa-user"></i>

                                    <span>
                                        <?= providerBookingEscape(
                                            $booking['client_name']
                                        ) ?>
                                    </span>

                                </div>


                                <div class="provider-booking-card-meta">

                                    <?php if (!empty($booking['location'])): ?>

                                        <span>
                                            <i class="fa-solid fa-location-dot"></i>
                                            <?= providerBookingEscape(
                                                $booking['location']
                                            ) ?>
                                        </span>

                                    <?php endif; ?>


                                    <?php
                                    $requestTimestamp = strtotime(
                                        (string)$booking['booking_date']
                                    );

                                    $requestIsAsap =
                                        $requestTimestamp !== false &&
                                        abs($requestTimestamp - time()) <= 300;
                                    ?>

                                    <span>

                                        <?php if ($requestIsAsap): ?>

                                            <i class="fa-solid fa-bolt"></i>
                                            ASAP

                                        <?php else: ?>

                                            <i class="fa-regular fa-clock"></i>
                                            <?= date(
                                                'M j, g:i A',
                                                $requestTimestamp
                                            ) ?>

                                        <?php endif; ?>

                                    </span>

                                </div>


                                <div class="provider-booking-card-footer">

                                    <span>
                                        <?= providerBookingEscape(
                                            providerBookingRelativeTime(
                                                (string)$booking['created_at']
                                            )
                                        ) ?>
                                    </span>

                                    <a
                                        href="dashboard.php?page=bookings&id=<?= (int)$booking['id'] ?>"
                                        class="provider-booking-view-link"
                                    >
                                        View Request
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>


        <!-- OTHER BOOKINGS -->

        <section class="provider-booking-section provider-other-bookings">

            <div class="provider-booking-section-header">

                <div>

                    <h2>Your bookings</h2>

                    <span>
                        Recent service activity
                    </span>

                </div>

            </div>


            <?php if (!empty($otherBookings)): ?>

                <div class="provider-booking-list">

                    <?php foreach ($otherBookings as $booking): ?>

                        <article class="provider-booking-card">

                            <div class="provider-booking-card-main">

                                <div class="provider-booking-card-heading">

                                    <div>

                                        <span class="<?= providerBookingStatusClass(
                                            (string)$booking['status']
                                        ) ?>">

                                            <?= providerBookingEscape(
                                                providerBookingStatusLabel(
                                                    (string)$booking['status']
                                                )
                                            ) ?>

                                        </span>

                                        <h3>
                                            <?= providerBookingEscape(
                                                $booking['service_name']
                                                ?? 'Service Request'
                                            ) ?>
                                        </h3>

                                    </div>


                                    <?php if ($booking['price'] !== null): ?>

                                        <strong class="provider-booking-card-price">
                                            TZS
                                            <?= number_format(
                                                (float)$booking['price'],
                                                0
                                            ) ?>
                                        </strong>

                                    <?php endif; ?>

                                </div>


                                <div class="provider-booking-card-client">

                                    <i class="fa-regular fa-user"></i>

                                    <span>
                                        <?= providerBookingEscape(
                                            $booking['client_name']
                                        ) ?>
                                    </span>

                                </div>


                                <div class="provider-booking-card-meta">

                                    <?php if (!empty($booking['location'])): ?>

                                        <span>
                                            <i class="fa-solid fa-location-dot"></i>
                                            <?= providerBookingEscape(
                                                $booking['location']
                                            ) ?>
                                        </span>

                                    <?php endif; ?>


                                    <span>
                                        <i class="fa-regular fa-clock"></i>

                                        <?php
                                        $otherBookingTimestamp = strtotime(
                                            (string)$booking['booking_date']
                                        );

                                        if ($otherBookingTimestamp !== false) {
                                            echo date(
                                                'M j, Y \a\t g:i A',
                                                $otherBookingTimestamp
                                            );
                                        }
                                        ?>

                                    </span>

                                </div>


                                <div class="provider-booking-card-footer">

                                    <span>
                                        Updated
                                        <?= providerBookingEscape(
                                            providerBookingRelativeTime(
                                                (string)$booking['updated_at']
                                            )
                                        ) ?>
                                    </span>

                                    <a
                                        href="dashboard.php?page=bookings&id=<?= (int)$booking['id'] ?>"
                                        class="provider-booking-view-link"
                                    >
                                        View Booking
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="provider-bookings-empty">

                    <div class="provider-bookings-empty-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>

                    <h3>No bookings yet</h3>

                    <p>
                        New service requests from clients will appear here.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</div>