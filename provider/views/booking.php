<?php
declare(strict_types=1);

/**
 * FindPro - Provider Bookings
 *
 * Handles:
 * - Viewing provider bookings
 * - Filtering bookings
 * - Accepting pending bookings
 * - Cancelling pending bookings
 * - Completing accepted bookings
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$providerId = (int)($_SESSION['user_id'] ?? 0);

if ($providerId <= 0) {
    throw new RuntimeException('Provider session not found.');
}

/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
|
| dashboard.php should already load database.php and provide $pdo.
| This fallback keeps the page safe if it is opened independently.
|
*/
if (!isset($pdo) || !($pdo instanceof PDO)) {
    require_once __DIR__ . '/../../config/database.php';
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['booking_csrf_token'])) {
    $_SESSION['booking_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['booking_csrf_token'];

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function bookingStatusLabel(string $status): string
{
    return match ($status) {
        'pending'   => 'Pending',
        'accepted'  => 'Accepted',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default     => ucfirst($status),
    };
}

function bookingStatusClass(string $status): string
{
    return match ($status) {
        'pending'   => 'status-pending',
        'accepted'  => 'status-accepted',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
        default     => 'status-default',
    };
}

function formatBookingDate(string $date): string
{
    $timestamp = strtotime($date);

    if (!$timestamp) {
        return 'Date unavailable';
    }

    return date('d M Y', $timestamp);
}

function formatBookingTime(string $date): string
{
    $timestamp = strtotime($date);

    if (!$timestamp) {
        return 'Time unavailable';
    }

    return date('h:i A', $timestamp);
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$successMessage = $_SESSION['booking_success'] ?? null;
$errorMessage   = $_SESSION['booking_error'] ?? null;

unset($_SESSION['booking_success'], $_SESSION['booking_error']);

/*
|--------------------------------------------------------------------------
| HANDLE BOOKING ACTION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($postedToken) ||
        !hash_equals($csrfToken, $postedToken)
    ) {
        $_SESSION['booking_error'] = 'Security verification failed. Please try again.';
        header('Location: dashboard.php?page=booking');
        exit;
    }

    $action = trim((string)($_POST['action'] ?? ''));
    $bookingId = (int)($_POST['booking_id'] ?? 0);

    if ($bookingId <= 0) {
        $_SESSION['booking_error'] = 'Invalid booking selected.';
        header('Location: dashboard.php?page=booking');
        exit;
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | ACCEPT PENDING BOOKING
        |--------------------------------------------------------------------------
        */

        if ($action === 'accept_booking') {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET
                    status = 'accepted',
                    updated_at = NOW()
                WHERE
                    id = ?
                    AND provider_user_id = ?
                    AND status = 'pending'
            ");

            $stmt->execute([
                $bookingId,
                $providerId
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['booking_success'] = 'Booking accepted successfully.';
            } else {
                $_SESSION['booking_error'] =
                    'This booking could not be accepted. It may have already been updated.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CANCEL PENDING BOOKING
        |--------------------------------------------------------------------------
        */

        elseif ($action === 'cancel_booking') {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET
                    status = 'cancelled',
                    updated_at = NOW()
                WHERE
                    id = ?
                    AND provider_user_id = ?
                    AND status = 'pending'
            ");

            $stmt->execute([
                $bookingId,
                $providerId
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['booking_success'] = 'Booking cancelled successfully.';
            } else {
                $_SESSION['booking_error'] =
                    'This booking could not be cancelled. It may have already been updated.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MARK ACCEPTED BOOKING AS COMPLETED
        |--------------------------------------------------------------------------
        */

        elseif ($action === 'complete_booking') {

            $stmt = $pdo->prepare("
                UPDATE bookings
                SET
                    status = 'completed',
                    updated_at = NOW()
                WHERE
                    id = ?
                    AND provider_user_id = ?
                    AND status = 'accepted'
            ");

            $stmt->execute([
                $bookingId,
                $providerId
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['booking_success'] = 'Booking marked as completed.';
            } else {
                $_SESSION['booking_error'] =
                    'This booking could not be completed.';
            }
        }

        else {
            $_SESSION['booking_error'] = 'Unknown booking action.';
        }

    } catch (PDOException $e) {

        error_log(
            'FindPro booking action error: ' . $e->getMessage()
        );

        $_SESSION['booking_error'] =
            'Something went wrong while updating the booking.';
    }

    header('Location: dashboard.php?page=booking');
    exit;
}

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$allowedFilters = [
    'all',
    'pending',
    'accepted',
    'completed',
    'cancelled'
];

$currentFilter = strtolower(
    trim((string)($_GET['status'] ?? 'all'))
);

if (!in_array($currentFilter, $allowedFilters, true)) {
    $currentFilter = 'all';
}

/*
|--------------------------------------------------------------------------
| BOOKING COUNTS
|--------------------------------------------------------------------------
*/

$bookingCounts = [
    'all'       => 0,
    'pending'   => 0,
    'accepted'  => 0,
    'completed' => 0,
    'cancelled' => 0,
];

try {

    $countStmt = $pdo->prepare("
        SELECT
            status,
            COUNT(*) AS total
        FROM bookings
        WHERE provider_user_id = ?
        GROUP BY status
    ");

    $countStmt->execute([$providerId]);

    while ($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {

        $status = (string)$row['status'];

        if (isset($bookingCounts[$status])) {
            $bookingCounts[$status] = (int)$row['total'];
        }
    }

    $bookingCounts['all'] =
        $bookingCounts['pending']
        + $bookingCounts['accepted']
        + $bookingCounts['completed']
        + $bookingCounts['cancelled'];

} catch (PDOException $e) {

    error_log(
        'FindPro booking count error: ' . $e->getMessage()
    );
}

/*
|--------------------------------------------------------------------------
| FETCH BOOKINGS
|--------------------------------------------------------------------------
*/

$bookings = [];

try {

    $sql = "
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

            u.name AS client_name,

            ps.service_name,
            ps.price,
            ps.price_type,
            ps.service_image

        FROM bookings b

        LEFT JOIN users u
            ON u.id = b.client_user_id

        LEFT JOIN provider_services ps
            ON ps.id = b.service_id

        WHERE b.provider_user_id = ?
    ";

    $params = [$providerId];

    if ($currentFilter !== 'all') {
        $sql .= " AND b.status = ?";
        $params[] = $currentFilter;
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    |
    | Pending bookings appear first.
    |
    */

    $sql .= "
        ORDER BY
            CASE b.status
                WHEN 'pending' THEN 1
                WHEN 'accepted' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'cancelled' THEN 4
                ELSE 5
            END,

            CASE
                WHEN b.status IN ('pending', 'accepted')
                THEN b.booking_date
            END ASC,

            b.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log(
        'FindPro booking fetch error: ' . $e->getMessage()
    );

    $errorMessage =
        'Unable to load bookings at the moment.';
}
?>

<style>
/* =========================================================
   FINDPRO BOOKINGS PAGE
========================================================= */

.booking-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 4px 0 40px;
}

/* ---------------------------------------------------------
   HEADER
--------------------------------------------------------- */

.booking-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 25px;
}

.booking-page-title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #17251d;
    letter-spacing: -0.5px;
}

.booking-page-subtitle {
    margin: 7px 0 0;
    color: #718078;
    font-size: 14px;
    line-height: 1.6;
}

.booking-refresh-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border: 1px solid #e2e9e5;
    border-radius: 10px;
    background: #fff;
    color: #35443b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: .2s ease;
}

.booking-refresh-btn:hover {
    border-color: #2ecc71;
    color: #249b55;
}

/* ---------------------------------------------------------
   FLASH
--------------------------------------------------------- */

.booking-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.booking-alert-success {
    background: #eafaf0;
    color: #1e8748;
    border: 1px solid #c9efd8;
}

.booking-alert-error {
    background: #fff1f1;
    color: #b53b3b;
    border: 1px solid #f2cccc;
}

/* ---------------------------------------------------------
   SUMMARY CARDS
--------------------------------------------------------- */

.booking-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 24px;
}

.booking-summary-card {
    background: #fff;
    border: 1px solid #e7ece9;
    border-radius: 15px;
    padding: 17px;
    display: flex;
    align-items: center;
    gap: 13px;
    box-shadow: 0 4px 18px rgba(27, 48, 36, .04);
}

.booking-summary-icon {
    width: 45px;
    height: 45px;
    min-width: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eefaf3;
    color: #2ecc71;
    font-size: 18px;
}

.booking-summary-content {
    min-width: 0;
}

.booking-summary-number {
    display: block;
    font-size: 22px;
    font-weight: 800;
    color: #18271f;
    line-height: 1.1;
}

.booking-summary-label {
    display: block;
    margin-top: 4px;
    color: #78857e;
    font-size: 12px;
}

/* ---------------------------------------------------------
   FILTERS
--------------------------------------------------------- */

.booking-toolbar {
    background: #fff;
    border: 1px solid #e7ece9;
    border-radius: 15px;
    padding: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(27, 48, 36, .04);
}

.booking-filters {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.booking-filter {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 13px;
    border-radius: 9px;
    color: #65736b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: .2s ease;
}

.booking-filter:hover {
    background: #f3f8f5;
    color: #249b55;
}

.booking-filter.active {
    background: #2ecc71;
    color: #fff;
}

.booking-filter-count {
    min-width: 21px;
    height: 21px;
    padding: 0 5px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, .06);
    font-size: 11px;
}

.booking-filter.active .booking-filter-count {
    background: rgba(255, 255, 255, .2);
}

/* ---------------------------------------------------------
   BOOKING LIST
--------------------------------------------------------- */

.booking-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.booking-card {
    background: #fff;
    border: 1px solid #e5ebe7;
    border-radius: 17px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(24, 43, 32, .045);
    transition: transform .2s ease, box-shadow .2s ease;
    animation: bookingFadeUp .35s ease both;
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 9px 28px rgba(24, 43, 32, .07);
}

@keyframes bookingFadeUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.booking-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 17px;
}

.booking-client {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.booking-client-avatar {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    background: #eaf8f0;
    color: #269c56;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    font-weight: 800;
}

.booking-client-info {
    min-width: 0;
}

.booking-client-name {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #1c2b23;
}

.booking-client-label {
    margin-top: 3px;
    font-size: 12px;
    color: #89948e;
}

.booking-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.booking-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.status-pending {
    color: #b57a08;
    background: #fff7df;
}

.status-accepted {
    color: #1976c7;
    background: #eaf5ff;
}

.status-completed {
    color: #23884b;
    background: #eaf9ef;
}

.status-cancelled {
    color: #ad4444;
    background: #fff0f0;
}

.status-default {
    color: #68756d;
    background: #f1f4f2;
}

/* ---------------------------------------------------------
   SERVICE
--------------------------------------------------------- */

.booking-service {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px;
    background: #f8faf9;
    border-radius: 12px;
    margin-bottom: 16px;
}

.booking-service-image {
    width: 54px;
    height: 54px;
    min-width: 54px;
    border-radius: 10px;
    object-fit: cover;
    background: #e8efeb;
}

.booking-service-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2ecc71;
    font-size: 18px;
}

.booking-service-info {
    min-width: 0;
}

.booking-service-name {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #24342b;
}

.booking-service-price {
    margin-top: 4px;
    font-size: 12px;
    color: #7d8982;
}

.booking-service-price strong {
    color: #269c56;
}

/* ---------------------------------------------------------
   DETAILS
--------------------------------------------------------- */

.booking-details-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.booking-detail {
    padding: 12px;
    border: 1px solid #edf1ee;
    border-radius: 11px;
    background: #fff;
}

.booking-detail-label {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #8a958f;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 5px;
}

.booking-detail-label i {
    color: #2ecc71;
}

.booking-detail-value {
    display: block;
    color: #27372e;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.4;
    word-break: break-word;
}

/* ---------------------------------------------------------
   NOTES
--------------------------------------------------------- */

.booking-notes {
    margin-bottom: 16px;
    padding: 13px 14px;
    border-left: 3px solid #2ecc71;
    background: #f8fbf9;
    border-radius: 0 10px 10px 0;
}

.booking-notes-label {
    display: block;
    margin-bottom: 5px;
    color: #68766e;
    font-size: 11px;
    font-weight: 800;
}

.booking-notes-text {
    margin: 0;
    color: #56645c;
    font-size: 12px;
    line-height: 1.6;
}

/* ---------------------------------------------------------
   ACTIONS
--------------------------------------------------------- */

.booking-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #edf1ee;
}

.booking-created {
    color: #9aa39e;
    font-size: 11px;
}

.booking-action-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.booking-btn {
    border: 0;
    border-radius: 9px;
    padding: 9px 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: .2s ease;
    font-family: inherit;
}

.booking-btn:hover {
    transform: translateY(-1px);
}

.booking-btn-accept {
    background: #2ecc71;
    color: #fff;
}

.booking-btn-accept:hover {
    background: #25b961;
}

.booking-btn-cancel {
    background: #fff1f1;
    color: #b04444;
}

.booking-btn-cancel:hover {
    background: #ffe4e4;
}

.booking-btn-complete {
    background: #eaf5ff;
    color: #2175b5;
}

.booking-btn-complete:hover {
    background: #dceeff;
}

/* ---------------------------------------------------------
   EMPTY STATE
--------------------------------------------------------- */

.booking-empty {
    background: #fff;
    border: 1px dashed #dbe5df;
    border-radius: 17px;
    padding: 55px 20px;
    text-align: center;
}

.booking-empty-icon {
    width: 62px;
    height: 62px;
    margin: 0 auto 15px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef9f2;
    color: #2ecc71;
    font-size: 24px;
}

.booking-empty-title {
    margin: 0;
    color: #27362e;
    font-size: 17px;
    font-weight: 800;
}

.booking-empty-text {
    max-width: 430px;
    margin: 7px auto 0;
    color: #89948e;
    font-size: 13px;
    line-height: 1.6;
}

/* ---------------------------------------------------------
   RESPONSIVE
--------------------------------------------------------- */

@media (max-width: 1000px) {

    .booking-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .booking-details-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 680px) {

    .booking-page {
        padding: 0 0 30px;
    }

    .booking-page-header {
        align-items: flex-start;
    }

    .booking-page-title {
        font-size: 23px;
    }

    .booking-page-subtitle {
        font-size: 12px;
    }

    .booking-refresh-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        justify-content: center;
    }

    .booking-refresh-btn span {
        display: none;
    }

    .booking-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .booking-summary-card {
        padding: 13px;
        border-radius: 12px;
    }

    .booking-summary-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        font-size: 15px;
    }

    .booking-summary-number {
        font-size: 18px;
    }

    .booking-summary-label {
        font-size: 10px;
    }

    .booking-toolbar {
        padding: 9px;
    }

    .booking-filters {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .booking-filter {
        justify-content: center;
        padding: 9px 7px;
        font-size: 11px;
    }

    .booking-card {
        padding: 15px;
        border-radius: 14px;
    }

    .booking-card-top {
        gap: 10px;
    }

    .booking-client-avatar {
        width: 42px;
        height: 42px;
        min-width: 42px;
    }

    .booking-client-name {
        font-size: 14px;
    }

    .booking-status {
        padding: 5px 8px;
        font-size: 9px;
    }

    .booking-service {
        padding: 10px;
    }

    .booking-service-image {
        width: 45px;
        height: 45px;
        min-width: 45px;
    }

    .booking-details-grid {
        grid-template-columns: 1fr;
    }

    .booking-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .booking-created {
        order: 2;
    }

    .booking-action-buttons {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .booking-btn {
        width: 100%;
    }
}

@media (max-width: 420px) {

    .booking-summary-grid {
        grid-template-columns: 1fr 1fr;
    }

    .booking-summary-card {
        gap: 8px;
    }

    .booking-summary-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
    }

    .booking-card-top {
        align-items: flex-start;
    }

    .booking-client {
        gap: 8px;
    }

    .booking-client-label {
        font-size: 10px;
    }

    .booking-action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>


<div class="booking-page">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="booking-page-header">

        <div>
            <h1 class="booking-page-title">
                Bookings
            </h1>

            <p class="booking-page-subtitle">
                Manage your service requests, appointments and completed jobs.
            </p>
        </div>

        <a
            href="dashboard.php?page=booking"
            class="booking-refresh-btn"
            title="Refresh bookings"
        >
            <i class="fa-solid fa-rotate"></i>
            <span>Refresh</span>
        </a>

    </div>


    <!-- =====================================================
         FLASH MESSAGES
    ====================================================== -->

    <?php if ($successMessage): ?>

        <div class="booking-alert booking-alert-success">
            <i class="fa-solid fa-circle-check"></i>

            <span>
                <?= htmlspecialchars((string)$successMessage) ?>
            </span>
        </div>

    <?php endif; ?>


    <?php if ($errorMessage): ?>

        <div class="booking-alert booking-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>

            <span>
                <?= htmlspecialchars((string)$errorMessage) ?>
            </span>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="booking-summary-grid">

        <div class="booking-summary-card">

            <div class="booking-summary-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>

            <div class="booking-summary-content">
                <span class="booking-summary-number">
                    <?= $bookingCounts['all'] ?>
                </span>

                <span class="booking-summary-label">
                    Total Bookings
                </span>
            </div>

        </div>


        <div class="booking-summary-card">

            <div class="booking-summary-icon">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>

            <div class="booking-summary-content">
                <span class="booking-summary-number">
                    <?= $bookingCounts['pending'] ?>
                </span>

                <span class="booking-summary-label">
                    Pending Requests
                </span>
            </div>

        </div>


        <div class="booking-summary-card">

            <div class="booking-summary-icon">
                <i class="fa-solid fa-briefcase"></i>
            </div>

            <div class="booking-summary-content">
                <span class="booking-summary-number">
                    <?= $bookingCounts['accepted'] ?>
                </span>

                <span class="booking-summary-label">
                    Accepted
                </span>
            </div>

        </div>


        <div class="booking-summary-card">

            <div class="booking-summary-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="booking-summary-content">
                <span class="booking-summary-number">
                    <?= $bookingCounts['completed'] ?>
                </span>

                <span class="booking-summary-label">
                    Completed
                </span>
            </div>

        </div>

    </div>


    <!-- =====================================================
         FILTERS
    ====================================================== -->

    <div class="booking-toolbar">

        <div class="booking-filters">

            <a
                href="dashboard.php?page=booking&status=all"
                class="booking-filter <?= $currentFilter === 'all' ? 'active' : '' ?>"
            >
                All

                <span class="booking-filter-count">
                    <?= $bookingCounts['all'] ?>
                </span>
            </a>


            <a
                href="dashboard.php?page=booking&status=pending"
                class="booking-filter <?= $currentFilter === 'pending' ? 'active' : '' ?>"
            >
                Pending

                <span class="booking-filter-count">
                    <?= $bookingCounts['pending'] ?>
                </span>
            </a>


            <a
                href="dashboard.php?page=booking&status=accepted"
                class="booking-filter <?= $currentFilter === 'accepted' ? 'active' : '' ?>"
            >
                Accepted

                <span class="booking-filter-count">
                    <?= $bookingCounts['accepted'] ?>
                </span>
            </a>


            <a
                href="dashboard.php?page=booking&status=completed"
                class="booking-filter <?= $currentFilter === 'completed' ? 'active' : '' ?>"
            >
                Completed

                <span class="booking-filter-count">
                    <?= $bookingCounts['completed'] ?>
                </span>
            </a>


            <a
                href="dashboard.php?page=booking&status=cancelled"
                class="booking-filter <?= $currentFilter === 'cancelled' ? 'active' : '' ?>"
            >
                Cancelled

                <span class="booking-filter-count">
                    <?= $bookingCounts['cancelled'] ?>
                </span>
            </a>

        </div>

    </div>


    <!-- =====================================================
         BOOKING LIST
    ====================================================== -->

    <?php if (!empty($bookings)): ?>

        <div class="booking-list">

            <?php foreach ($bookings as $booking): ?>

                <?php

                $clientName = trim(
                    (string)($booking['client_name'] ?? '')
                );

                if ($clientName === '') {
                    $clientName = 'Unknown Client';
                }

                $serviceName = trim(
                    (string)($booking['service_name'] ?? '')
                );

                if ($serviceName === '') {
                    $serviceName = 'Service not specified';
                }

                $location = trim(
                    (string)($booking['location'] ?? '')
                );

                $notes = trim(
                    (string)($booking['notes'] ?? '')
                );

                $status = (string)$booking['status'];

                $clientInitial = strtoupper(
                    substr($clientName, 0, 1)
                );

                $serviceImage = trim(
                    (string)($booking['service_image'] ?? '')
                );

                ?>

                <article class="booking-card">

                    <!-- TOP -->
                    <div class="booking-card-top">

                        <div class="booking-client">

                            <div class="booking-client-avatar">
                                <?= htmlspecialchars($clientInitial) ?>
                            </div>

                            <div class="booking-client-info">

                                <h3 class="booking-client-name">
                                    <?= htmlspecialchars($clientName) ?>
                                </h3>

                                <div class="booking-client-label">
                                    Booking #<?= (int)$booking['id'] ?>
                                </div>

                            </div>

                        </div>


                        <span
                            class="booking-status <?= bookingStatusClass($status) ?>"
                        >
                            <?= htmlspecialchars(bookingStatusLabel($status)) ?>
                        </span>

                    </div>


                    <!-- SERVICE -->
                    <div class="booking-service">

                        <?php if ($serviceImage !== ''): ?>

                            <img
                                src="../assets/images/services/<?= htmlspecialchars($serviceImage) ?>"
                                alt="<?= htmlspecialchars($serviceName) ?>"
                                class="booking-service-image"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div class="booking-service-image booking-service-placeholder">
                                <i class="fa-solid fa-screwdriver-wrench"></i>
                            </div>

                        <?php endif; ?>


                        <div class="booking-service-info">

                            <h4 class="booking-service-name">
                                <?= htmlspecialchars($serviceName) ?>
                            </h4>

                            <?php if (
                                isset($booking['price']) &&
                                $booking['price'] !== null &&
                                $booking['price'] !== ''
                            ): ?>

                                <div class="booking-service-price">

                                    Price:

                                    <strong>
                                        <?= number_format(
                                            (float)$booking['price'],
                                            2
                                        ) ?>
                                    </strong>

                                    <?php if (!empty($booking['price_type'])): ?>

                                        •
                                        <?= htmlspecialchars(
                                            ucfirst((string)$booking['price_type'])
                                        ) ?>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <div class="booking-service-price">
                                    Service price not specified
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- DETAILS -->
                    <div class="booking-details-grid">

                        <!-- DATE -->
                        <div class="booking-detail">

                            <span class="booking-detail-label">
                                <i class="fa-regular fa-calendar"></i>
                                Date
                            </span>

                            <span class="booking-detail-value">
                                <?= htmlspecialchars(
                                    formatBookingDate(
                                        (string)$booking['booking_date']
                                    )
                                ) ?>
                            </span>

                        </div>


                        <!-- TIME -->
                        <div class="booking-detail">

                            <span class="booking-detail-label">
                                <i class="fa-regular fa-clock"></i>
                                Time
                            </span>

                            <span class="booking-detail-value">
                                <?= htmlspecialchars(
                                    formatBookingTime(
                                        (string)$booking['booking_date']
                                    )
                                ) ?>
                            </span>

                        </div>


                        <!-- LOCATION -->
                        <div class="booking-detail">

                            <span class="booking-detail-label">
                                <i class="fa-solid fa-location-dot"></i>
                                Location
                            </span>

                            <span class="booking-detail-value">

                                <?php if ($location !== ''): ?>

                                    <?= htmlspecialchars($location) ?>

                                <?php else: ?>

                                    Location not provided

                                <?php endif; ?>

                            </span>

                        </div>

                    </div>


                    <!-- NOTES -->
                    <?php if ($notes !== ''): ?>

                        <div class="booking-notes">

                            <span class="booking-notes-label">
                                Client Notes
                            </span>

                            <p class="booking-notes-text">
                                <?= nl2br(
                                    htmlspecialchars($notes)
                                ) ?>
                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- ACTIONS -->
                    <div class="booking-actions">

                        <span class="booking-created">

                            Requested
                            <?= htmlspecialchars(
                                formatBookingDate(
                                    (string)$booking['created_at']
                                )
                            ) ?>

                        </span>


                        <div class="booking-action-buttons">

                            <!-- PENDING ACTIONS -->
                            <?php if ($status === 'pending'): ?>

                                <form
                                    method="POST"
                                    action="dashboard.php?page=booking"
                                    onsubmit="return confirm('Accept this booking request?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="accept_booking"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$booking['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="booking-btn booking-btn-accept"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                        Accept
                                    </button>

                                </form>


                                <form
                                    method="POST"
                                    action="dashboard.php?page=booking"
                                    onsubmit="return confirm('Cancel this booking request?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="cancel_booking"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$booking['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="booking-btn booking-btn-cancel"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                        Cancel
                                    </button>

                                </form>

                            <?php endif; ?>


                            <!-- ACCEPTED ACTION -->
                            <?php if ($status === 'accepted'): ?>

                                <form
                                    method="POST"
                                    action="dashboard.php?page=booking"
                                    onsubmit="return confirm('Mark this booking as completed?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($csrfToken) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="complete_booking"
                                    >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$booking['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="booking-btn booking-btn-complete"
                                    >
                                        <i class="fa-solid fa-circle-check"></i>
                                        Mark Completed
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <!-- EMPTY STATE -->

        <div class="booking-empty">

            <div class="booking-empty-icon">

                <?php if ($currentFilter === 'pending'): ?>

                    <i class="fa-solid fa-hourglass-half"></i>

                <?php elseif ($currentFilter === 'accepted'): ?>

                    <i class="fa-solid fa-briefcase"></i>

                <?php elseif ($currentFilter === 'completed'): ?>

                    <i class="fa-solid fa-circle-check"></i>

                <?php elseif ($currentFilter === 'cancelled'): ?>

                    <i class="fa-solid fa-ban"></i>

                <?php else: ?>

                    <i class="fa-regular fa-calendar-xmark"></i>

                <?php endif; ?>

            </div>


            <h3 class="booking-empty-title">

                <?php if ($currentFilter === 'all'): ?>

                    No bookings yet

                <?php else: ?>

                    No <?= htmlspecialchars(
                        bookingStatusLabel($currentFilter)
                    ) ?> bookings

                <?php endif; ?>

            </h3>


            <p class="booking-empty-text">

                <?php if ($currentFilter === 'pending'): ?>

                    New booking requests from clients will appear here.

                <?php elseif ($currentFilter === 'accepted'): ?>

                    Bookings you accept will appear here.

                <?php elseif ($currentFilter === 'completed'): ?>

                    Completed jobs will appear here.

                <?php elseif ($currentFilter === 'cancelled'): ?>

                    Cancelled booking requests will appear here.

                <?php else: ?>

                    When clients book one of your services,
                    their booking requests will appear here.

                <?php endif; ?>

            </p>

        </div>

    <?php endif; ?>

</div>