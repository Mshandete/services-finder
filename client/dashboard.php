<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireRole('client');

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
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle); ?> | FindPro</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Client Dashboard CSS -->
    <link
        rel="stylesheet"
        href="../assets/css/client-dashboard.css"
    >

</head>

<body>

<div class="client-dashboard">

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>

    <main class="client-main">

        <?php include __DIR__ . '/components/topbar.php'; ?>

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

<nav class="mobile-bottom-nav">

    <a
        href="dashboard.php?page=home"
        class="bottom-nav-link <?= $page === 'home' ? 'active' : ''; ?>"
    >
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a
        href="dashboard.php?page=services"
        class="bottom-nav-link <?= $page === 'services' ? 'active' : ''; ?>"
    >
        <i class="fa-solid fa-briefcase"></i>
        <span>Services</span>
    </a>

    <a
        href="dashboard.php?page=bookings"
        class="bottom-nav-link <?= $page === 'bookings' ? 'active' : ''; ?>"
    >
        <i class="fa-solid fa-calendar-check"></i>
        <span>Bookings</span>
    </a>

    <a
        href="dashboard.php?page=messages"
        class="bottom-nav-link <?= $page === 'messages' ? 'active' : ''; ?>"
    >
        <i class="fa-solid fa-comment-dots"></i>
        <span>Messages</span>
    </a>

    <a
        href="dashboard.php?page=profile"
        class="bottom-nav-link <?= $page === 'profile' ? 'active' : ''; ?>"
    >
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
    </a>

</nav>

<script src="../assets/js/client-dashboard.js"></script>

</body>

</html>