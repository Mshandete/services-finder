<?php
$currentPage = $_GET['page'] ?? 'home';

function active($page, $currentPage){
    return $page === $currentPage ? 'active' : '';
}
?>

<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">

        <a href="dashboard.php?page=home">

            <img src="../assets/images/msagambegu.jpg" alt="FindPro Logo">

            <div class="logo-text">
                <h2>FindPro</h2>
                <span>Service Finder</span>
            </div>

        </a>

    </div>

    <!-- Menu -->
    <nav class="sidebar-menu">

        <ul>

            <li>
                <a href="dashboard.php?page=home" class="<?= active('home',$currentPage); ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=services" class="<?= active('services',$currentPage); ?>">
                    <i class="bi bi-search"></i>
                    <span>Find Services</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=bookings" class="<?= active('bookings',$currentPage); ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=favorites" class="<?= active('favorites',$currentPage); ?>">
                    <i class="bi bi-heart"></i>
                    <span>Favorites</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=messages" class="<?= active('messages',$currentPage); ?>">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=profile" class="<?= active('profile',$currentPage); ?>">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </li>

            <li>
                <a href="dashboard.php?page=settings" class="<?= active('settings',$currentPage); ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>

        </ul>

    </nav>

    <!-- Bottom -->
    <div class="sidebar-bottom">

        <div class="help-card">

            <div class="help-icon">
                <i class="bi bi-headset"></i>
            </div>

            <h4>Need Help?</h4>

            <p>Contact our support team anytime.</p>

            <a href="#" class="btn btn-primary btn-sm">
                Contact Us
            </a>

        </div>

        <a href="../auth/logout.php" class="logout-btn">

            <i class="bi bi-box-arrow-right"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>