<?php
$currentPage = $page ?? 'home';
?>

<header class="client-topbar">

    <!-- Mobile Brand -->
    <div class="mobile-brand">
        <button
            type="button"
            class="mobile-menu-button"
            id="menuToggle"
            aria-label="Open menu"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <a href="dashboard.php?page=home" class="mobile-logo">
            <i class="fa-solid fa-location-dot"></i>
            <span>Find<span>Pro</span></span>
        </a>
    </div>

    <!-- Search -->
    <form class="top-search" action="dashboard.php" method="GET">
        <input type="hidden" name="page" value="services">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="search"
            name="search"
            placeholder="Search for services or providers..."
            autocomplete="off"
        >
    </form>

    <!-- Location -->
    <button type="button" class="location-selector">
        <i class="fa-solid fa-location-dot"></i>
        <span>My Location</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>

    <!-- Actions -->
    <div class="top-actions">

        <!-- Notifications -->
        <button
            type="button"
            class="top-icon-button notification-button"
            aria-label="Notifications"
        >
            <i class="fa-regular fa-bell"></i>

            <!-- Later: connect unread notifications -->
            <!--
            <span class="top-badge">3</span>
            -->
        </button>

        <!-- User Profile -->
        <a
            href="dashboard.php?page=profile"
            class="user-menu"
            aria-label="My Profile"
        >
            <div class="user-avatar">
                <img
                    src="../assets/images/default-avatar.png"
                    alt="Profile"
                >
            </div>

            <div class="user-info">
                <strong>My Account</strong>
                <span>Client</span>
            </div>

            <i class="fa-solid fa-chevron-down"></i>
        </a>

    </div>

</header>