<?php

declare(strict_types=1);


/**
 * ==========================================
 * FINDPRO CLIENT DASHBOARD
 * ==========================================
 */


/**
 * ==========================================
 * DATABASE
 * ==========================================
 */

require_once __DIR__ . '/../config/database.php';


/**
 * ==========================================
 * AUTHENTICATION
 * ==========================================
 */

require_once __DIR__ . '/../includes/auth.php';

requireRole('client');


/**
 * ==========================================
 * GLOBAL LANGUAGE
 * ==========================================
 */

require_once __DIR__ . '/../includes/language.php';


/**
 * ==========================================
 * GLOBAL SETTINGS
 * ==========================================
 */

require_once __DIR__ . '/../includes/settings-handler.php';


/**
 * ==========================================
 * CURRENT USER SETTINGS
 * ==========================================
 */

$currentLanguage = getCurrentLanguage();
$currentTheme = getCurrentTheme();


/**
 * ==========================================
 * CURRENT PAGE
 * ==========================================
 */

$page = $_GET['page'] ?? 'home';


$allowedPages = [
    'home',
    'services',
    'bookings',
    'favorites',
    'messages',
    'profile',
    'settings'
];


if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}


/**
 * ==========================================
 * PAGE TITLES
 * ==========================================
 */

$pageTitles = [
    'home' => 'Home',
    'services' => 'Find Services',
    'bookings' => 'My Bookings',
    'favorites' => 'Favorites',
    'messages' => 'Messages',
    'profile' => 'My Profile',
    'settings' => 'Settings'
];


$pageTitle = $pageTitles[$page] ?? 'FindPro Client';

?>

<!DOCTYPE html>

<html
    lang="<?= htmlspecialchars($currentLanguage, ENT_QUOTES, 'UTF-8'); ?>"
    data-theme="<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle); ?> | FindPro
    </title>


    <!-- =====================================
         FONT AWESOME
         ====================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- =====================================
         CLIENT STYLES
         ====================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/client-dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/messages.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/client-profile.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/setting.css"
    >


    <!-- =====================================
         GLOBAL LANGUAGE
         ====================================== -->

    <?php renderLanguageScript(); ?>


    <!-- =====================================
         APPLY THEME BEFORE PAGE LOAD
         ====================================== -->

    <script>
        document.documentElement.dataset.theme =
            <?= json_encode($currentTheme); ?>;
    </script>

</head>


<body
    class="theme-<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>"
    data-theme="<?= htmlspecialchars($currentTheme, ENT_QUOTES, 'UTF-8'); ?>"
>

<div class="client-dashboard">


    <!-- =====================================
         SIDEBAR
         ====================================== -->

    <?php include __DIR__ . '/components/sidebar.php'; ?>


    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>


    <!-- =====================================
         MAIN CONTENT
         ====================================== -->

    <main class="client-main">


        <!-- TOPBAR -->

        <?php include __DIR__ . '/components/topbar.php'; ?>


        <!-- PAGE CONTENT -->

        <div class="dashboard-content">

            <?php

            $viewPath = __DIR__ . "/views/{$page}.php";

            if (file_exists($viewPath)) {

                include $viewPath;

            } else {

                include __DIR__ . '/views/home.php';

            }

            ?>

        </div>

    </main>

</div>


<!-- =========================================
     MOBILE BOTTOM NAVIGATION
     ========================================== -->

<nav class="mobile-bottom-nav">


    <!-- HOME -->

    <a
        href="dashboard.php?page=home"
        class="bottom-nav-link <?= $page === 'home' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-house"></i>

        <span>Home</span>

    </a>


    <!-- SERVICES -->

    <a
        href="dashboard.php?page=services"
        class="bottom-nav-link <?= $page === 'services' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-briefcase"></i>

        <span>Services</span>

    </a>


    <!-- BOOKINGS -->

    <a
        href="dashboard.php?page=bookings"
        class="bottom-nav-link <?= $page === 'bookings' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-calendar-check"></i>

        <span>Bookings</span>

    </a>


    <!-- MESSAGES -->

    <a
        href="dashboard.php?page=messages"
        class="bottom-nav-link <?= $page === 'messages' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-comment-dots"></i>

        <span>Messages</span>

    </a>


    <!-- PROFILE -->

    <a
        href="dashboard.php?page=profile"
        class="bottom-nav-link <?= $page === 'profile' ? 'active' : ''; ?>"
    >

        <i class="fa-solid fa-user"></i>

        <span>Profile</span>

    </a>

</nav>


<!-- =========================================
     CLIENT JAVASCRIPT
     ========================================== -->

<script src="../assets/js/client-dashboard.js"></script>


</body>

</html>