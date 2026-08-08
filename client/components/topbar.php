<?php

$userName = $_SESSION['full_name'] ?? 'Client';
$userImage = $_SESSION['profile_image'] ?? '../assets/images/mavella.jpg';

?>

<header class="topbar">

    <div class="topbar-left">

        <button class="menu-toggle" id="menuToggle">

            <i class="bi bi-list"></i>

        </button>

        <div class="page-title">

            <h2>Welcome Back 👋</h2>

            <span>Find trusted professionals near you.</span>

        </div>

    </div>

    <div class="topbar-center">

        <form class="top-search">

            <i class="bi bi-search"></i>

            <input
                type="text"
                placeholder="Search plumber, electrician, cleaner..."
            >

        </form>

    </div>

    <div class="topbar-right">

        <button class="icon-btn notify">

            <i class="bi bi-bell"></i>

        </button>

        <button class="icon-btn">

            <i class="bi bi-chat-dots"></i>

        </button>

        <button class="icon-btn">

            <i class="bi bi-gear"></i>

        </button>

        <div class="user-dropdown">

            <img
                src="<?= htmlspecialchars($userImage); ?>"
                alt="User">

            <div class="user-details">

                <h4><?= htmlspecialchars($userName); ?></h4>

                <span>Client Account</span>

            </div>

            <i class="bi bi-chevron-down"></i>

        </div>

    </div>

</header>