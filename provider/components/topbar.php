<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current Provider Data
|--------------------------------------------------------------------------
*/

$providerId =
    (int)($_SESSION['user_id'] ?? 0);


$providerName =
    $_SESSION['full_name'] ?? 'Provider';


$providerProfileImage =
    null;


$providerAvailability =
    'available';


/*
|--------------------------------------------------------------------------
| Load Provider Profile
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


    $providerProfile =
        $profileStmt->fetch(
            PDO::FETCH_ASSOC
        );


    if ($providerProfile) {

        $providerProfileImage =
            $providerProfile['profile_image']
            ?? null;


        $providerAvailability =
            $providerProfile['availability']
            ?? 'available';

    }

}


/*
|--------------------------------------------------------------------------
| Profile Image Path
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Use absolute browser path because topbar.php
| is included through provider/dashboard.php
|
*/

$profileImage =
    '/services-finder/assets/images/providers/default.jpg';


if (
    !empty($providerProfileImage) &&
    $providerProfileImage !== 'default.jpg'
) {

    $profileImage =
        '/services-finder/assets/images/providers/' .
        rawurlencode(
            basename(
                $providerProfileImage
            )
        );

}

?>

<header class="provider-topbar">

    <div class="provider-topbar-inner">


        <!-- =====================================================
             LEFT
        ====================================================== -->

        <div class="provider-topbar-left">


            <!-- MENU BUTTON -->

            <button
                type="button"
                class="provider-menu-toggle"
                id="menuToggle"
                aria-label="Open navigation"
                aria-controls="sidebar"
                aria-expanded="false">

                <i class="fa-solid fa-bars"></i>

            </button>


            <!-- SEARCH -->

            <div class="provider-search">

                <i class="fa-solid fa-magnifying-glass provider-search-icon"></i>

                <input
                    type="search"
                    placeholder="Search..."
                    aria-label="Search">

            </div>


        </div>



        <!-- =====================================================
             RIGHT
        ====================================================== -->

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


                <!-- PROFILE IMAGE -->

                <div class="provider-user-avatar">

                    <?php if (
                        !empty($providerProfileImage) &&
                        $providerProfileImage !== 'default.jpg'
                    ): ?>

                        <img
                            src="<?= htmlspecialchars($profileImage) ?>"
                            alt="<?= htmlspecialchars($providerName) ?>">

                    <?php else: ?>

                        <div class="provider-user-avatar-placeholder">

                            <i class="fa-solid fa-user"></i>

                        </div>

                    <?php endif; ?>

                </div>



                <!-- USER INFORMATION -->

                <div class="provider-user-info">

                    <strong>

                        <?= htmlspecialchars(
                            $providerName
                        ) ?>

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