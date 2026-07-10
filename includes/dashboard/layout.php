<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FindPro Dashboard Layout
|--------------------------------------------------------------------------
|
| This is the main dashboard layout.
| Every dashboard page (Client, Provider, Admin) loads this file.
|
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Default Variables
|--------------------------------------------------------------------------
*/

$pageTitle = $pageTitle ?? "Dashboard";

$contentPage = $contentPage ?? null;

if (!$contentPage || !file_exists($contentPage)) {
    die("Dashboard content page not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once __DIR__ . '/header.php'; ?>

</head>

<body>

<div class="dashboard-wrapper">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>


    <div class="dashboard-main">

        <!-- Top Navigation -->
        <?php require_once __DIR__ . '/topbar.php'; ?>


        <!-- Main Content -->
        <main class="dashboard-content">

            <?php require_once $contentPage; ?>

        </main>


        <!-- Footer -->
        <?php require_once __DIR__ . '/footer.php'; ?>

    </div>

</div>

<script src="../assets/js/dashboard.js"></script>

</body>
</html>