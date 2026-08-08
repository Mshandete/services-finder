<?php
// provider/components/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="/services-finder/provider/dashboard.php" class="logo">
            <span class="logo-icon">🟢</span>
            <h2>FindPro</h2>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?= ($current_page == 'dashboard.php' || $current_page == 'home.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/dashboard.php">
                    <span class="nav-icon">🏠</span>
                    <span>Home</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'requests.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/views/requests.php">
                    <span class="nav-icon">🔍</span>
                    <span>Service Requests</span>
                    <span class="badge">2</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'schedule.php' || $current_page == 'bookings.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/views/schedule.php">
                    <span class="nav-icon">📅</span>
                    <span>Bookings / My Jobs</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'earnings.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/views/earnings.php">
                    <span class="nav-icon">💳</span>
                    <span>Earnings</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'services.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/views/services.php">
                    <span class="nav-icon">🛠️</span>
                    <span>Services Manager</span>
                </a>
            </li>
            <li class="<?= ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="/services-finder/provider/views/settings.php">
                    <span class="nav-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-action">
        <button class="btn-create-service">+ Create New Service</button>
    </div>
</aside>