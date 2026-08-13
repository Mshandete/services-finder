<?php
declare(strict_types=1);

/**
 * FindPro - Provider Dashboard
 *
 * This file is responsible for:
 * 1. Authenticating the provider.
 * 2. Loading the logged-in provider.
 * 3. Preparing dashboard data.
 * 4. Routing dashboard views.
 *
 * IMPORTANT:
 * Dashboard values should come from the database.
 * Do NOT hard-code earnings, bookings, jobs, ratings, etc.
 */

require_once "../includes/auth.php";
requireRole("provider");

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Provider ID
|--------------------------------------------------------------------------
| We expect the logged-in user's ID to be stored in the session.
*/
$providerId = $_SESSION['user_id'] ?? null;

if (!$providerId) {
    header("Location: ../auth/login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Dashboard Page
|--------------------------------------------------------------------------
| This allows:
|
| /provider/dashboard.php
| /provider/dashboard.php?page=services
| /provider/dashboard.php?page=bookings
| /provider/dashboard.php?page=earnings
| etc.
*/

$page = $_GET['page'] ?? 'home';

$allowedPages = [
    'home',
    'services',
    'bookings',
    'earnings',
    'messages',
    'reviews',
    'profile',
    'settings'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}


/*
|--------------------------------------------------------------------------
| Provider Information
|--------------------------------------------------------------------------
*/

$provider = null;

try {

    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.first_name,
            u.last_name,
            u.email
        FROM users u
        WHERE u.id = ?
        LIMIT 1
    ");

    $stmt->execute([$providerId]);

    $provider = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Provider dashboard user query failed: " . $e->getMessage());

    $provider = null;
}


/*
|--------------------------------------------------------------------------
| Safe Provider Name
|--------------------------------------------------------------------------
*/

$providerName = 'Provider';

if ($provider) {

    $fullName = trim(
        ($provider['first_name'] ?? '') . ' ' .
        ($provider['last_name'] ?? '')
    );

    if ($fullName !== '') {
        $providerName = $fullName;
    }
}


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
|
| These values are initialized here and will be populated
| from the real database.
|
| We deliberately DO NOT put fake values here.
|
*/

$dashboardStats = [
    'total_earnings'  => 0,
    'new_requests'    => 0,
    'completed_jobs'  => 0,
    'average_rating'  => 0,
    'review_count'    => 0
];


/*
|--------------------------------------------------------------------------
| Provider Profile
|--------------------------------------------------------------------------
*/

$providerProfile = null;

try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM provider_profiles
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$providerId]);

    $providerProfile = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Provider profile query failed: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| Provider Services Count
|--------------------------------------------------------------------------
*/

$totalServices = 0;

try {

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM provider_services
        WHERE provider_id = ?
    ");

    $stmt->execute([$providerId]);

    $totalServices = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    error_log("Provider services query failed: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| Availability
|--------------------------------------------------------------------------
|
| We don't assume a specific column yet.
| It will be connected to the exact provider_profiles
| schema when we wire the database fully.
|
*/

$isAvailable = true;


/*
|--------------------------------------------------------------------------
| View Paths
|--------------------------------------------------------------------------
*/

$viewPath = __DIR__ . "/views/{$page}.php";


/*
|--------------------------------------------------------------------------
| Dashboard Layout
|--------------------------------------------------------------------------
|
| These components will be created separately.
|
*/

$sidebarPath = __DIR__ . "/components/sidebar.php";
$topbarPath  = __DIR__ . "/components/topbar.php";
$footerPath  = __DIR__ . "/components/footer.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(ucfirst($page)) ?> | FindPro
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/provider-dashboard.css"
    >

</head>

<body>

<div class="provider-app">


    <!-- Sidebar -->
    <aside class="provider-sidebar">

        <?php

        if (file_exists($sidebarPath)) {
            require $sidebarPath;
        }

        ?>

    </aside>


    <!-- Main Application -->
    <div class="provider-main">


        <!-- Topbar -->
        <header class="provider-topbar">

            <?php

            if (file_exists($topbarPath)) {
                require $topbarPath;
            }

            ?>

        </header>


        <!-- Page Content -->
        <main class="provider-content">

            <?php

            if (file_exists($viewPath)) {

                require $viewPath;

            } else {

                /*
                 * Temporary fallback.
                 *
                 * Once home.php and the other view files
                 * exist, this will no longer be shown.
                 */

                ?>

                <section class="dashboard-placeholder">

                    <h1>
                        <?= htmlspecialchars(ucfirst($page)) ?>
                    </h1>

                    <p>
                        This section is ready to be connected
                        to the FindPro data layer.
                    </p>

                </section>

                <?php
            }

            ?>

        </main>


        <!-- Footer -->
        <footer class="provider-footer">

            <?php

            if (file_exists($footerPath)) {
                require $footerPath;
            }

            ?>

        </footer>


    </div>

</div>


<script src="../assets/js/provider-dashboard.js"></script>

</body>

</html>