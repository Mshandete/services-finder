<?php

declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/settings-handler.php';

$currentLanguage = getCurrentLanguage();
$currentTheme = getCurrentTheme();

$page = $_GET['page'] ?? 'home';

$allowedPages = [
    'home',
    'services',
    'bookings',
    'messages',
    'profile',
    'settings'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLanguage, ENT_QUOTES, 'UTF-8'); ?>"
    data-theme="<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard | FindPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/provider-dashboard.css">
    <link rel="stylesheet" href="../assets/css/services.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/bookings.css">
    <link rel="stylesheet" href="../assets/css/messages.css">
    <link rel="stylesheet" href="../assets/css/setting.css">

    <?php renderLanguageScript(); ?>

    <script>
    document.documentElement.dataset.theme =
    <?= json_encode($currentTheme); ?>;
    </script>

    </head>
<body
    class="theme-<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>"
    data-theme="<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>"
>


<div class="provider-dashboard">

    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <!-- =====================================
         MAIN CONTENT
         ====================================== -->

    <main class="main-content">
        <?php include __DIR__ . '/components/topbar.php'; ?>

        <div class="page-content">
            <?php
            $viewPath = __DIR__ . "/views/{$page}.php";

            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                include __DIR__ . "/views/home.php";
            }
            ?>
        </div>

    </main>

</div>

<script src="../assets/js/provider-dashboard.js"></script>
</body>

</html>