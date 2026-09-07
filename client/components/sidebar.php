<?php

/*
|--------------------------------------------------------------------------
| CLIENT SIDEBAR
|--------------------------------------------------------------------------
|
| This component is loaded by:
|
| client/dashboard.php
|
| It uses the $page variable from the dashboard router
| to determine the active navigation item.
|
*/

$currentPage = $page ?? 'home';

?>

<aside
    class="client-sidebar"
    id="clientSidebar"
>


    <!-- =================================================
         SIDEBAR LOGO
    ================================================= -->

    <div class="sidebar-logo">

        <a href="dashboard.php?page=home">

            <div class="logo-icon">

                <i class="fa-solid fa-location-dot"></i>

            </div>


            <div class="logo-text">

                <h2>
                    Find<span>Pro</span>
                </h2>

                <p>
                    Find trusted professionals
                </p>

            </div>

        </a>

    </div>



    <!-- =================================================
         SIDEBAR NAVIGATION
    ================================================= -->

    <nav class="sidebar-navigation">


        <!-- HOME -->

        <a
            href="dashboard.php?page=home"
            class="sidebar-link <?= $currentPage === 'home' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-house"></i>

            <span>Home</span>

        </a>



        <!-- FIND SERVICES -->

        <a
            href="dashboard.php?page=services"
            class="sidebar-link <?= $currentPage === 'services' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

            <span>Find Services</span>

        </a>



        <!-- BOOKINGS -->

        <a
            href="dashboard.php?page=bookings"
            class="sidebar-link <?= $currentPage === 'bookings' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-calendar-check"></i>

            <span>My Bookings</span>

        </a>



        <!-- FAVORITES -->

        <a
            href="dashboard.php?page=favorites"
            class="sidebar-link <?= $currentPage === 'favorites' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-heart"></i>

            <span>Favorites</span>

        </a>



        <!-- MESSAGES -->

        <a
            href="dashboard.php?page=messages"
            class="sidebar-link <?= $currentPage === 'messages' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-comment-dots"></i>

            <span>Messages</span>


            <!--
                Message badge can later be connected
                to unread messages from the database.
            -->

            <!--
            <span class="menu-badge">
                3
            </span>
            -->

        </a>


    </nav>



    <!-- =================================================
         SIDEBAR DIVIDER
    ================================================= -->

    <div class="sidebar-divider"></div>



    <!-- =================================================
         ACCOUNT NAVIGATION
    ================================================= -->

    <nav class="sidebar-navigation sidebar-account-navigation">


        <!-- PROFILE -->

        <a
            href="dashboard.php?page=profile"
            class="sidebar-link <?= $currentPage === 'profile' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-user"></i>

            <span>My Profile</span>

        </a>



        <!-- SETTINGS -->

        <a
            href="dashboard.php?page=settings"
            class="sidebar-link <?= $currentPage === 'settings' ? 'active' : ''; ?>"
        >

            <i class="fa-solid fa-gear"></i>

            <span>Settings</span>

        </a>


    </nav>



    <!-- =================================================
         SIDEBAR BOTTOM
    ================================================= -->

    <div class="sidebar-bottom">


        <!-- SUPPORT CARD -->

        <div class="support-card">


            <div class="support-icon">

                <i class="fa-solid fa-headset"></i>

            </div>


            <h3>
                Need Help?
            </h3>


            <p>
                Our support team is ready
                to help you.
            </p>


            <a
                href="#"
                class="support-button"
            >

                Contact Support

            </a>


        </div>


    </div>


</aside>