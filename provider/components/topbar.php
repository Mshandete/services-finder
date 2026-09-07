<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current Provider Data
|--------------------------------------------------------------------------
*/

$providerId = (int)($_SESSION['user_id'] ?? 0);

$providerName = $_SESSION['full_name'] ?? 'Provider';

$providerProfileImage = null;


/*
|--------------------------------------------------------------------------
| Load Provider Profile Image
|--------------------------------------------------------------------------
*/

if ($providerId > 0) {

    $profileStmt = $pdo->prepare("
        SELECT
            profile_image,
            availability
        FROM provider_profiles
        WHERE user_id = ?
        LIMIT 1
    ");

    $profileStmt->execute([
        $providerId
    ]);

    $providerProfile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if ($providerProfile) {

        $providerProfileImage =
            $providerProfile['profile_image'] ?? null;

        $providerAvailability =
            $providerProfile['availability'] ?? 'available';

    } else {

        $providerAvailability = 'available';

    }

} else {

    $providerAvailability = 'available';

}


/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
*/

$defaultProfileImage =
    '../assets/images/default.jpg';


if (
    !empty($providerProfileImage) &&
    $providerProfileImage !== 'default.jpg'
) {

    $profileImage =
        '../assets/images/providers/' .
        basename($providerProfileImage);

} else {

    $profileImage = $defaultProfileImage;

}
?>

<header class="provider-topbar">

    <div class="provider-topbar-inner">


        <!-- LEFT -->

        <div class="provider-topbar-left">

            <button
                type="button"
                class="provider-menu-toggle"
                id="menuToggle"
                aria-label="Open navigation"
                aria-controls="sidebar"
                aria-expanded="false">

                <i class="fa-solid fa-bars"></i>

            </button>


            <div class="provider-search">

                <i class="fa-solid fa-magnifying-glass provider-search-icon"></i>

                <input
                    type="search"
                    placeholder="Search..."
                    aria-label="Search">

            </div>

        </div>



        <!-- RIGHT -->

        <div class="provider-topbar-actions">


            <!-- AVAILABILITY -->

            <div class="provider-availability">

                <span class="availability-indicator"></span>

                <span>
                    <?= htmlspecialchars(
                        ucfirst($providerAvailability)
                    ) ?>
                </span>

            </div>



            <!-- NOTIFICATIONS -->

            <button
                type="button"
                class="provider-notification"
                aria-label="Notifications">

                <i class="fa-regular fa-bell"></i>

                <span class="notification-badge">
                    0
                </span>

            </button>



            <!-- USER -->

            <a
                href="dashboard.php?page=profile"
                class="provider-user">

                <div class="provider-user-avatar">

                    <img
                        src="<?= htmlspecialchars($profileImage) ?>"
                        alt="<?= htmlspecialchars($providerName) ?>">

                </div>


                <div class="provider-user-info">

                    <strong>
                        <?= htmlspecialchars($providerName) ?>
                    </strong>

                    <span>
                        Provider
                    </span>

                </div>


                <i class="fa-solid fa-chevron-down"></i>

            </a>


        </div>

    </div>

</header>