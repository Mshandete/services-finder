<?php
$currentPage = $page ?? 'home';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$clientUserId = $_SESSION['user_id'] ?? null;

$topbarProfileImage = '';
$topbarProfileName = 'My Account';

if ($clientUserId) {

    try {

        $stmt = $pdo->prepare("
            SELECT
                u.full_name,
                cp.profile_image
            FROM users u
            LEFT JOIN client_profiles cp
                ON cp.user_id = u.id
            WHERE u.id = ?
              AND u.role = 'client'
            LIMIT 1
        ");

        $stmt->execute([$clientUserId]);

        $topbarClient = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($topbarClient) {

            $topbarProfileName = trim(
                (string)($topbarClient['full_name'] ?? 'My Account')
            );

            $topbarProfileImage = trim(
                (string)($topbarClient['profile_image'] ?? '')
            );
        }

    } catch (PDOException $e) {

        $topbarProfileImage = '';
    }
}

/*
|--------------------------------------------------------------------------
| Profile Image URL
|--------------------------------------------------------------------------
|
| topbar.php is included by client/dashboard.php.
| Therefore browser-relative path starts from /client/
|
*/

$topbarProfileImageUrl = '../assets/images/default-avatar.png';

if (
    $topbarProfileImage !== '' &&
    $topbarProfileImage !== 'default.jpg' &&
    $topbarProfileImage !== 'default-avatar.png'
) {
    $topbarProfileImageUrl =
        '../assets/images/profiles/' .
        rawurlencode($topbarProfileImage);
}
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

            <span>
                Find<span>Pro</span>
            </span>
        </a>

    </div>


    <!-- Search -->
    <form
        class="top-search"
        action="dashboard.php"
        method="GET"
    >

        <input
            type="hidden"
            name="page"
            value="services"
        >

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="search"
            name="search"
            placeholder="Search for services or providers..."
            autocomplete="off"
        >

    </form>


    <!-- Location -->
    <button
        type="button"
        class="location-selector"
    >

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
                    src="<?= htmlspecialchars($topbarProfileImageUrl, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($topbarProfileName, ENT_QUOTES, 'UTF-8') ?>"
                >

            </div>


            <div class="user-info">

                <strong>
                    <?= htmlspecialchars($topbarProfileName, ENT_QUOTES, 'UTF-8') ?>
                </strong>

                <span>Client</span>

            </div>


            <i class="fa-solid fa-chevron-down"></i>

        </a>

    </div>

</header>