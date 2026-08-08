<?php

declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <div class="dashboard-main">
        <?php require_once __DIR__ . '/topbar.php'; ?>
        <main class="dashboard-content">
            <?php require_once $contentPage; ?>
        </main>
        <?php require_once __DIR__ . '/footer.php'; ?>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>

</body>
</html>