<?php

$currentPage = $_GET['page'] ?? 'home';

?>

<aside class="provider-sidebar" id="sidebar">

    <div class="provider-sidebar-inner">

        <!-- BRAND -->
        <div class="provider-brand">

            <a href="../index.php" class="provider-brand-link">

                <span class="provider-brand-icon">
                    <i class="fa-solid fa-location-dot"></i>
                </span>

                <span class="provider-brand-name">
                    Find<span>Pro</span>
                </span>

            </a>


            <button
                type="button"
                class="provider-sidebar-close"
                id="closeSidebar"
                aria-label="Close navigation"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <!-- NAVIGATION -->
        <nav class="provider-navigation">

            <ul>

                <!-- DASHBOARD -->
                <li>

                    <a
                        href="dashboard.php?page=home"
                        class="provider-nav-link <?= $currentPage === 'home' ? 'active' : '' ?>"
                        aria-current="<?= $currentPage === 'home' ? 'page' : 'false' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-solid fa-house"></i>
                        </span>

                        <span class="provider-nav-label">
                            Dashboard
                        </span>

                    </a>

                </li>


                <!-- MY SERVICES -->
                <li>

                    <a
                        href="dashboard.php?page=services"
                        class="provider-nav-link <?= $currentPage === 'services' ? 'active' : '' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>

                        <span class="provider-nav-label">
                            My Services
                        </span>

                    </a>

                </li>


                <!-- BOOKINGS -->
                <li>

                    <a
                        href="dashboard.php?page=bookings"
                        class="provider-nav-link <?= $currentPage === 'bookings' ? 'active' : '' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-regular fa-calendar"></i>
                        </span>

                        <span class="provider-nav-label">
                            Bookings
                        </span>

                    </a>

                </li>


                <!-- MESSAGES -->
                <li>

                    <a
                        href="dashboard.php?page=messages"
                        class="provider-nav-link <?= $currentPage === 'messages' ? 'active' : '' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-regular fa-comment"></i>
                        </span>

                        <span class="provider-nav-label">
                            Messages
                        </span>

                    </a>

                </li>


                <!-- PROFILE -->
                <li>

                    <a
                        href="dashboard.php?page=profile"
                        class="provider-nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-regular fa-user"></i>
                        </span>

                        <span class="provider-nav-label">
                            Profile
                        </span>

                    </a>

                </li>


                <!-- SETTINGS -->
                <li>

                    <a
                        href="dashboard.php?page=settings"
                        class="provider-nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>"
                    >

                        <span class="provider-nav-icon">
                            <i class="fa-solid fa-gear"></i>
                        </span>

                        <span class="provider-nav-label">
                            Settings
                        </span>

                    </a>

                </li>

            </ul>

        </nav>


        <!-- LOGOUT -->
        <div class="provider-sidebar-footer">

            <div class="provider-sidebar-divider"></div>

            <a
                href="../logout.php"
                class="provider-logout"
            >

                <span class="provider-nav-icon">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </span>

                <span class="provider-nav-label">
                    Logout
                </span>

            </a>

        </div>

    </div>

</aside>


<!-- MOBILE OVERLAY -->
<div
    class="provider-sidebar-overlay"
    id="sidebarOverlay"
></div>