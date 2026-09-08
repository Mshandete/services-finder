<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current Provider
|--------------------------------------------------------------------------
*/

$dashboardProviderId = (int)(
    $_SESSION['user_id'] ?? 0
);


/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$dashboardProvider = null;

$dashboardFullName = 'Provider';
$dashboardProfileImage = null;
$dashboardLocation = '';
$dashboardAvailability = 'available';

$dashboardVerified = 0;
$dashboardRating = 0.0;
$dashboardReviews = 0;

$dashboardProfileCompletion = 0;

$dashboardServiceName = 'No service added';


/*
|--------------------------------------------------------------------------
| Load Provider Information
|--------------------------------------------------------------------------
*/

if ($dashboardProviderId > 0) {

    $dashboardProviderStmt = $pdo->prepare("
        SELECT

            u.id,
            u.full_name,
            u.profile_completed,

            p.profile_image,
            p.location_name,
            p.availability,
            p.is_verified,
            p.average_rating,
            p.total_reviews

        FROM users u

        LEFT JOIN provider_profiles p
            ON p.user_id = u.id

        WHERE u.id = ?
        AND u.role = 'provider'

        LIMIT 1
    ");

    $dashboardProviderStmt->execute([
        $dashboardProviderId
    ]);

    $dashboardProvider =
        $dashboardProviderStmt->fetch(
            PDO::FETCH_ASSOC
        );


    /*
    |--------------------------------------------------------------------------
    | Prepare Provider Data
    |--------------------------------------------------------------------------
    */

    if ($dashboardProvider) {

        $dashboardFullName =
            $dashboardProvider['full_name']
            ?? 'Provider';


        $dashboardProfileImage =
            $dashboardProvider['profile_image']
            ?? null;


        $dashboardLocation =
            $dashboardProvider['location_name']
            ?? '';


        $dashboardAvailability =
            $dashboardProvider['availability']
            ?? 'available';


        $dashboardVerified = (int)(
            $dashboardProvider['is_verified']
            ?? 0
        );


        $dashboardRating = (float)(
            $dashboardProvider['average_rating']
            ?? 0
        );


        $dashboardReviews = (int)(
            $dashboardProvider['total_reviews']
            ?? 0
        );


        $dashboardProfileCompletion = (int)(
            $dashboardProvider['profile_completed']
            ?? 0
        );

    }

}


/*
|--------------------------------------------------------------------------
| Load Provider Service
|--------------------------------------------------------------------------
*/

if ($dashboardProviderId > 0) {

    $dashboardServiceStmt = $pdo->prepare("
        SELECT service_name

        FROM provider_services

        WHERE provider_user_id = ?
        AND status = 'active'

        ORDER BY id DESC

        LIMIT 1
    ");

    $dashboardServiceStmt->execute([
        $dashboardProviderId
    ]);

    $dashboardService =
        $dashboardServiceStmt->fetch(
            PDO::FETCH_ASSOC
        );


    if (
        $dashboardService &&
        !empty($dashboardService['service_name'])
    ) {

        $dashboardServiceName =
            $dashboardService['service_name'];

    }

}


/*
|--------------------------------------------------------------------------
| Profile Image Path
|--------------------------------------------------------------------------
*/

$dashboardProfileImagePath = '';

$dashboardHasProfileImage = false;


if (
    !empty($dashboardProfileImage) &&
    $dashboardProfileImage !== 'default.jpg'
) {

    $dashboardProfileImagePath =
        '../assets/images/providers/' .
        basename($dashboardProfileImage);

    $dashboardHasProfileImage = true;

}


/*
|--------------------------------------------------------------------------
| Profile Completion
|--------------------------------------------------------------------------
*/

$dashboardCompletionPercentage =
    $dashboardProfileCompletion === 1
        ? 100
        : 0;


/*
|--------------------------------------------------------------------------
| Location
|--------------------------------------------------------------------------
*/

$dashboardLocationText =
    !empty($dashboardLocation)
        ? $dashboardLocation
        : 'Location not added';

?>

<!-- PROVIDER PROFILE CARD -->

<section class="provider-overview-card">

    <div class="provider-info">


        <!-- PROVIDER AVATAR -->

        <div class="provider-avatar">

            <?php if ($dashboardHasProfileImage): ?>

                <img
                    src="<?= htmlspecialchars(
                        $dashboardProfileImagePath
                    ) ?>"
                    alt="<?= htmlspecialchars(
                        $dashboardFullName
                    ) ?>">

            <?php else: ?>

                <div class="provider-avatar-placeholder">

                    <i class="fa-solid fa-user"></i>

                </div>

            <?php endif; ?>

        </div>


        <!-- PROVIDER DETAILS -->

        <div class="provider-details">


            <!-- NAME -->

            <div class="provider-name-row">

                <h2>

                    <?= htmlspecialchars(
                        $dashboardFullName
                    ) ?>

                </h2>


                <!-- VERIFIED -->

                <?php if ($dashboardVerified === 1): ?>

                    <span class="verified-badge">

                        <i class="fa-solid fa-circle-check"></i>

                        Verified Provider

                    </span>

                <?php else: ?>

                    <span class="provider-pending-badge">

                        <i class="fa-regular fa-clock"></i>

                        Verification Pending

                    </span>

                <?php endif; ?>

            </div>


            <!-- SERVICE -->

            <p class="provider-service">

                <i class="fa-solid fa-briefcase"></i>

                <?= htmlspecialchars(
                    $dashboardServiceName
                ) ?>

            </p>


            <!-- LOCATION -->

            <p class="provider-location">

                <i class="fa-solid fa-location-dot"></i>

                <?= htmlspecialchars(
                    $dashboardLocationText
                ) ?>

            </p>


            <!-- RATING -->

            <p class="provider-rating">

                <i class="fa-solid fa-star"></i>

                <?= number_format(
                    $dashboardRating,
                    1
                ) ?>

                <span>

                    (
                    <?= number_format(
                        $dashboardReviews
                    ) ?>
                    reviews)

                </span>

            </p>


        </div>

    </div>


    <!-- PROFILE PROGRESS -->

    <div class="profile-progress">


        <div class="progress-info">

            <div>

                <span>
                    Profile Status
                </span>

                <strong>

                    <?= $dashboardCompletionPercentage ?>%
                    Completed

                </strong>

            </div>

        </div>


        <div class="progress-bar">

            <div
                class="progress-fill"
                style="
                    width:
                    <?= $dashboardCompletionPercentage ?>%;
                ">

            </div>

        </div>


    </div>


    <!-- EDIT PROFILE -->

    <a
        href="dashboard.php?page=profile"
        class="edit-profile-btn">

        <i class="fa-solid fa-pen"></i>

        Edit Profile

    </a>


</section>