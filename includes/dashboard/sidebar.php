<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Dashboard Sidebar
|--------------------------------------------------------------------------
*/

$role = $_SESSION['role'] ?? 'client';

$currentPage = basename($_SERVER['PHP_SELF']);

$fullName = $_SESSION['full_name'] ?? 'User';

$initial = strtoupper(substr($fullName,0,1));

?>

<aside class="sidebar">

    <div class="sidebar-header">

        <a href="../index.php" class="logo">

            <span class="logo-icon">
                <i class="fa-solid fa-location-dot"></i>
            </span>

            <div>

                <h2>FindPro</h2>

                <small><?= ucfirst($role) ?></small>

            </div>

        </a>

    </div>

    <div class="user-box">

        <div class="avatar">

            <?= $initial ?>

        </div>

        <div class="user-info">

            <strong><?= htmlspecialchars($fullName) ?></strong>

            <span><?= ucfirst($role) ?></span>

        </div>

    </div>

    <nav class="sidebar-menu">

<?php if($role=="client"): ?>

<a class="<?= $currentPage=="dashboard.php" ? "active" : "" ?>"
href="../client/dashboard.php">

<i class="fa-solid fa-house"></i>

<span>Dashboard</span>

</a>

<a href="#">

<i class="fa-solid fa-magnifying-glass"></i>

<span>Find Services</span>

</a>

<a href="#">

<i class="fa-solid fa-calendar-check"></i>

<span>Bookings</span>

</a>

<a href="#">

<i class="fa-solid fa-heart"></i>

<span>Favorites</span>

</a>

<a href="#">

<i class="fa-solid fa-message"></i>

<span>Messages</span>

</a>

<a href="#">

<i class="fa-solid fa-user"></i>

<span>Profile</span>

</a>

<?php endif; ?>


<?php if($role=="provider"): ?>

<a class="<?= $currentPage=="dashboard.php" ? "active" : "" ?>"
href="../provider/dashboard.php">

<i class="fa-solid fa-house"></i>

<span>Dashboard</span>

</a>

<a href="#">

<i class="fa-solid fa-briefcase"></i>

<span>My Services</span>

</a>

<a href="#">

<i class="fa-solid fa-calendar-days"></i>

<span>Bookings</span>

</a>

<a href="#">

<i class="fa-solid fa-star"></i>

<span>Reviews</span>

</a>

<a href="#">

<i class="fa-solid fa-wallet"></i>

<span>Earnings</span>

</a>

<a href="#">

<i class="fa-solid fa-user-gear"></i>

<span>Profile</span>

</a>

<?php endif; ?>


<?php if($role=="admin"): ?>

<a class="<?= $currentPage=="dashboard.php" ? "active" : "" ?>"
href="../admin/dashboard.php">

<i class="fa-solid fa-chart-line"></i>

<span>Dashboard</span>

</a>

<a href="#">

<i class="fa-solid fa-users"></i>

<span>Users</span>

</a>

<a href="#">

<i class="fa-solid fa-user-tie"></i>

<span>Providers</span>

</a>

<a href="#">

<i class="fa-solid fa-list"></i>

<span>Categories</span>

</a>

<a href="#">

<i class="fa-solid fa-flag"></i>

<span>Reports</span>

</a>

<a href="#">

<i class="fa-solid fa-gear"></i>

<span>Settings</span>

</a>

<?php endif; ?>

    </nav>

    <div class="sidebar-footer">

        <a href="../auth/logout.php" class="logout">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>