<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$clientId = (int)($_SESSION['user_id'] ?? 0);

if ($clientId <= 0) {
    exit('Unauthorized.');
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['booking_csrf_token'])) {
    $_SESSION['booking_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['booking_csrf_token'];

/*
|--------------------------------------------------------------------------
| Request variables
|--------------------------------------------------------------------------
*/

$action = $_GET['action'] ?? 'list';
$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| CREATE BOOKING
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_booking') {

    $postedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrfToken, $postedToken)) {
        $error = 'Invalid request. Please try again.';
    } else {

        $postedServiceId = (int)($_POST['service_id'] ?? 0);
        $bookingType = $_POST['booking_type'] ?? 'asap';
        $location = trim((string)($_POST['location'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($postedServiceId <= 0) {
            $error = 'Please select a valid service.';
        } elseif (!in_array($bookingType, ['asap', 'schedule'], true)) {
            $error = 'Invalid booking type.';
        } elseif ($location === '') {
            $error = 'Please enter your service location.';
        } else {

            /*
            |--------------------------------------------------------------------------
            | Get selected service + provider
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    ps.id,
                    ps.provider_user_id,
                    ps.service_name,
                    ps.price,
                    ps.price_type,
                    ps.status
                FROM provider_services ps
                WHERE ps.id = ?
                  AND ps.status = 'active'
                LIMIT 1
            ");

            $stmt->execute([$postedServiceId]);

            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {

                $error = 'This service is no longer available.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Prevent client from booking themselves
                |--------------------------------------------------------------------------
                */

                if ((int)$service['provider_user_id'] === $clientId) {

                    $error = 'You cannot book your own service.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Booking date
                    |--------------------------------------------------------------------------
                    */

                    if ($bookingType === 'asap') {

                        $bookingDate = date('Y-m-d H:i:s');

                    } else {

                        $scheduledDate = trim(
                            (string)($_POST['scheduled_date'] ?? '')
                        );

                        $scheduledTime = trim(
                            (string)($_POST['scheduled_time'] ?? '')
                        );

                        if ($scheduledDate === '' || $scheduledTime === '') {

                            $error = 'Please select a date and time.';

                        } else {

                            $bookingDate = $scheduledDate . ' ' . $scheduledTime;

                            $timestamp = strtotime($bookingDate);

                            if ($timestamp === false || $timestamp < time()) {
                                $error = 'Please select a future date and time.';
                            }
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create booking
                    |--------------------------------------------------------------------------
                    */

                    if ($error === '') {

                        $stmt = $pdo->prepare("
                            INSERT INTO bookings (
                                client_user_id,
                                provider_user_id,
                                service_id,
                                booking_date,
                                location,
                                status,
                                notes
                            )
                            VALUES (?, ?, ?, ?, ?, 'pending', ?)
                        ");

                        $stmt->execute([
                            $clientId,
                            (int)$service['provider_user_id'],
                            (int)$service['id'],
                            $bookingDate,
                            $location,
                            $notes !== '' ? $notes : null
                        ]);

                        $newBookingId = (int)$pdo->lastInsertId();

                        header(
                            'Location: dashboard.php?page=bookings&id=' . $newBookingId . '&created=1'
                        );
                        exit;
                    }
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| CREATED MESSAGE
|--------------------------------------------------------------------------
*/

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $success = 'Your service request has been sent.';
}

/*
|--------------------------------------------------------------------------
| QUICK BOOKING SERVICE
|--------------------------------------------------------------------------
*/

$selectedService = null;

if ($action === 'create' && $serviceId > 0) {

    $stmt = $pdo->prepare("
        SELECT
            ps.id,
            ps.provider_user_id,
            ps.service_name,
            ps.description,
            ps.price,
            ps.price_type,
            ps.service_image,

            u.full_name AS provider_name

        FROM provider_services ps

        INNER JOIN users u
            ON u.id = ps.provider_user_id

        WHERE ps.id = ?
          AND ps.status = 'active'

        LIMIT 1
    ");

    $stmt->execute([$serviceId]);

    $selectedService = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$selectedService) {
        $error = 'The selected service could not be found.';
    }
}

/*
|--------------------------------------------------------------------------
| BOOKING DETAILS
|--------------------------------------------------------------------------
*/

$booking = null;

if ($bookingId > 0) {

    $stmt = $pdo->prepare("
        SELECT
            b.id,
            b.booking_date,
            b.location,
            b.status,
            b.notes,
            b.created_at,

            ps.service_name,
            ps.price,
            ps.price_type,
            ps.service_image,

            u.full_name AS provider_name

        FROM bookings b

        INNER JOIN provider_services ps
            ON ps.id = b.service_id

        INNER JOIN users u
            ON u.id = b.provider_user_id

        WHERE b.id = ?
          AND b.client_user_id = ?

        LIMIT 1
    ");

    $stmt->execute([
        $bookingId,
        $clientId
    ]);

    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $error = 'Booking not found.';
    }
}

/*
|--------------------------------------------------------------------------
| ALL CLIENT BOOKINGS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        b.id,
        b.booking_date,
        b.location,
        b.status,
        b.notes,

        ps.service_name,
        ps.price,
        ps.price_type,
        ps.service_image,

        u.full_name AS provider_name

    FROM bookings b

    INNER JOIN provider_services ps
        ON ps.id = b.service_id

    INNER JOIN users u
        ON u.id = b.provider_user_id

    WHERE b.client_user_id = ?

    ORDER BY b.created_at DESC
");

$stmt->execute([$clientId]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="client-bookings">

    <?php if ($error !== ''): ?>

        <div class="booking-alert booking-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>

    <?php endif; ?>


    <?php if ($success !== ''): ?>

        <div class="booking-alert booking-alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>
                <strong>Request sent</strong>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        </div>

    <?php endif; ?>


    <?php if ($action === 'create' && $selectedService): ?>

        <!-- =========================================================
             QUICK BOOKING
        ========================================================== -->

        <section class="quick-booking">

            <div class="quick-booking-header">

                <a
                    href="dashboard.php?page=services"
                    class="booking-back"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

                <div>
                    <span class="section-label">QUICK BOOKING</span>
                    <h1>Request this service</h1>
                    <p>It only takes a few seconds.</p>
                </div>

            </div>


            <div class="quick-booking-layout">

                <!-- SERVICE SUMMARY -->

                <div class="booking-service-summary">

                    <div class="booking-service-image">

                        <?php if (!empty($selectedService['service_image'])): ?>

                            <img
                                src="../assets/images/services/<?= htmlspecialchars($selectedService['service_image']) ?>"
                                alt="<?= htmlspecialchars($selectedService['service_name']) ?>"
                            >

                        <?php else: ?>

                            <div class="booking-image-placeholder">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="booking-service-info">

                        <span class="booking-small-label">
                            SERVICE
                        </span>

                        <h2>
                            <?= htmlspecialchars($selectedService['service_name']) ?>
                        </h2>

                        <p>
                            by
                            <strong>
                                <?= htmlspecialchars($selectedService['provider_name']) ?>
                            </strong>
                        </p>


                        <?php if ($selectedService['price'] !== null): ?>

                            <div class="booking-price">

                                <strong>
                                    TSh <?= number_format((float)$selectedService['price']) ?>
                                </strong>

                                <?php if (!empty($selectedService['price_type'])): ?>

                                    <span>
                                        / <?= htmlspecialchars($selectedService['price_type']) ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- FORM -->

                <form
                    method="POST"
                    class="quick-booking-form"
                    id="quickBookingForm"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="create_booking"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken) ?>"
                    >

                    <input
                        type="hidden"
                        name="service_id"
                        value="<?= (int)$selectedService['id'] ?>"
                    >


                    <div class="booking-field">

                        <label>
                            When do you need it?
                        </label>

                        <div class="booking-type-options">

                            <label class="booking-type-option active">

                                <input
                                    type="radio"
                                    name="booking_type"
                                    value="asap"
                                    checked
                                >

                                <span class="booking-type-content">

                                    <i class="fa-solid fa-bolt"></i>

                                    <span>
                                        <strong>ASAP</strong>
                                        <small>As soon as possible</small>
                                    </span>

                                </span>

                            </label>


                            <label class="booking-type-option">

                                <input
                                    type="radio"
                                    name="booking_type"
                                    value="schedule"
                                >

                                <span class="booking-type-content">

                                    <i class="fa-regular fa-calendar"></i>

                                    <span>
                                        <strong>Schedule</strong>
                                        <small>Choose date & time</small>
                                    </span>

                                </span>

                            </label>

                        </div>

                    </div>


                    <div
                        class="schedule-fields"
                        id="scheduleFields"
                        hidden
                    >

                        <div class="booking-field">

                            <label for="scheduledDate">
                                Date
                            </label>

                            <input
                                type="date"
                                id="scheduledDate"
                                name="scheduled_date"
                                min="<?= date('Y-m-d') ?>"
                            >

                        </div>


                        <div class="booking-field">

                            <label for="scheduledTime">
                                Time
                            </label>

                            <input
                                type="time"
                                id="scheduledTime"
                                name="scheduled_time"
                            >

                        </div>

                    </div>


                    <div class="booking-field">

                        <label for="bookingLocation">
                            Where do you need the service?
                        </label>

                        <div class="input-with-icon">

                            <i class="fa-solid fa-location-dot"></i>

                            <input
                                type="text"
                                id="bookingLocation"
                                name="location"
                                placeholder="Enter your location"
                                maxlength="150"
                                required
                            >

                        </div>

                    </div>


                    <div class="booking-field">

                        <div class="field-label-row">

                            <label for="bookingNotes">
                                Anything the provider should know?
                            </label>

                            <span>Optional</span>

                        </div>

                        <textarea
                            id="bookingNotes"
                            name="notes"
                            rows="4"
                            maxlength="1000"
                            placeholder="Add a short note..."
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="send-booking-button"
                    >
                        <span>Send Request</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>


                    <p class="booking-form-note">
                        The provider will receive your request and respond as soon as possible.
                    </p>

                </form>

            </div>

        </section>


    <?php elseif ($booking): ?>

        <!-- =========================================================
             BOOKING DETAILS
        ========================================================== -->

        <?php

        $bookingStatus = (string)$booking['status'];

        $statusClass = match ($bookingStatus) {
            'accepted' => 'status-accepted',
            'in_progress' => 'status-progress',
            'completed' => 'status-completed',
            'rejected' => 'status-rejected',
            'cancelled' => 'status-cancelled',
            default => 'status-pending',
        };

        $statusText = match ($bookingStatus) {
            'in_progress' => 'In Progress',
            default => ucfirst($bookingStatus),
        };

        ?>

        <section class="booking-details">

            <div class="booking-details-header">

                <a
                    href="dashboard.php?page=bookings"
                    class="booking-back"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

                <div>

                    <span class="section-label">
                        BOOKING #<?= (int)$booking['id'] ?>
                    </span>

                    <h1>Booking details</h1>

                </div>

            </div>


            <div class="booking-details-card">

                <div class="booking-detail-service">

                    <?php if (!empty($booking['service_image'])): ?>

                        <img
                            src="../assets/images/services/<?= htmlspecialchars($booking['service_image']) ?>"
                            alt="<?= htmlspecialchars($booking['service_name']) ?>"
                        >

                    <?php else: ?>

                        <div class="booking-image-placeholder">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>

                    <?php endif; ?>


                    <div>

                        <span class="booking-small-label">
                            SERVICE
                        </span>

                        <h2>
                            <?= htmlspecialchars($booking['service_name']) ?>
                        </h2>

                        <p>
                            Provider:
                            <strong>
                                <?= htmlspecialchars($booking['provider_name']) ?>
                            </strong>
                        </p>

                    </div>

                </div>


                <div class="booking-status-large <?= $statusClass ?>">
                    <span></span>
                    <?= htmlspecialchars($statusText) ?>
                </div>


                <div class="booking-details-grid">

                    <div>

                        <span>Date & time</span>

                        <strong>
                            <?= date(
                                'd M Y, H:i',
                                strtotime($booking['booking_date'])
                            ) ?>
                        </strong>

                    </div>


                    <div>

                        <span>Location</span>

                        <strong>
                            <?= htmlspecialchars($booking['location'] ?: 'Not provided') ?>
                        </strong>

                    </div>


                    <div>

                        <span>Price</span>

                        <strong>

                            <?php if ($booking['price'] !== null): ?>

                                TSh <?= number_format((float)$booking['price']) ?>

                            <?php else: ?>

                                Contact provider

                            <?php endif; ?>

                        </strong>

                    </div>


                    <div>

                        <span>Requested</span>

                        <strong>
                            <?= date(
                                'd M Y, H:i',
                                strtotime($booking['created_at'])
                            ) ?>
                        </strong>

                    </div>

                </div>


                <?php if (!empty($booking['notes'])): ?>

                    <div class="booking-notes">

                        <span>Note</span>

                        <p>
                            <?= nl2br(htmlspecialchars($booking['notes'])) ?>
                        </p>

                    </div>

                <?php endif; ?>


                <?php if ($bookingStatus === 'accepted'): ?>

                    <div class="booking-next-step">

                        <div>

                            <i class="fa-regular fa-comments"></i>

                            <div>
                                <strong>Provider accepted your request</strong>
                                <span>You can now continue the conversation.</span>
                            </div>

                        </div>

<a
    href="dashboard.php?page=messages&provider_id=<?= (int)$booking['provider_user_id'] ?>&booking_id=<?= (int)$booking['id'] ?>"
>
    Message Provider
</a>

                    </div>

                <?php endif; ?>

            </div>

        </section>


    <?php else: ?>

        <!-- =========================================================
             BOOKING LIST
        ========================================================== -->

        <section class="bookings-page">

            <div class="bookings-header">

                <div>

                    <span class="section-label">
                        MY BOOKINGS
                    </span>

                    <h1>Your bookings</h1>

                    <p>
                        Track your service requests in one place.
                    </p>

                </div>


                <a
                    href="dashboard.php?page=services"
                    class="find-service-button"
                >
                    <i class="fa-solid fa-plus"></i>
                    Find a Service
                </a>

            </div>


            <?php if (empty($bookings)): ?>

                <div class="empty-bookings">

                    <div class="empty-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>

                    <h2>No bookings yet</h2>

                    <p>
                        Find a service and send your first request.
                    </p>

                    <a
                        href="dashboard.php?page=services"
                        class="primary-button"
                    >
                        Explore Services
                    </a>

                </div>

            <?php else: ?>

                <div class="bookings-list">

                    <?php foreach ($bookings as $item): ?>

                        <?php

                        $status = (string)$item['status'];

                        $statusClass = match ($status) {
                            'accepted' => 'status-accepted',
                            'in_progress' => 'status-progress',
                            'completed' => 'status-completed',
                            'rejected' => 'status-rejected',
                            'cancelled' => 'status-cancelled',
                            default => 'status-pending',
                        };

                        $statusText = match ($status) {
                            'in_progress' => 'In Progress',
                            default => ucfirst($status),
                        };

                        ?>

                        <article class="booking-item">

                            <div class="booking-service-image">

                                <?php if (!empty($item['service_image'])): ?>

                                    <img
                                        src="../assets/images/services/<?= htmlspecialchars($item['service_image']) ?>"
                                        alt="<?= htmlspecialchars($item['service_name']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="booking-image-placeholder">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="booking-main">

                                <div class="booking-top">

                                    <div>

                                        <span class="booking-small-label">
                                            SERVICE REQUEST
                                        </span>

                                        <h2>
                                            <?= htmlspecialchars($item['service_name']) ?>
                                        </h2>

                                    </div>

                                    <span class="booking-status <?= $statusClass ?>">
                                        <?= htmlspecialchars($statusText) ?>
                                    </span>

                                </div>


                                <div class="booking-provider">

                                    <i class="fa-regular fa-user"></i>

                                    <span>
                                        <?= htmlspecialchars($item['provider_name']) ?>
                                    </span>

                                </div>


                                <div class="booking-meta">

                                    <span>

                                        <i class="fa-regular fa-calendar"></i>

                                        <?= date(
                                            'd M Y, H:i',
                                            strtotime($item['booking_date'])
                                        ) ?>

                                    </span>


                                    <span>

                                        <i class="fa-solid fa-location-dot"></i>

                                        <?= htmlspecialchars($item['location'] ?: 'Location not provided') ?>

                                    </span>

                                </div>

                            </div>


                            <div class="booking-actions">

                                <a
                                    href="dashboard.php?page=bookings&id=<?= (int)$item['id'] ?>"
                                    class="view-booking"
                                >
                                    View
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</div>