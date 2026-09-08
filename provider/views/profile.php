<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current Provider
|--------------------------------------------------------------------------
*/

$providerId = (int)($_SESSION['user_id'] ?? 0);

if ($providerId <= 0) {
    header('Location: ../auth/login.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['profile_csrf_token'])) {
    $_SESSION['profile_csrf_token'] = bin2hex(random_bytes(32));
}

$profileCsrfToken = $_SESSION['profile_csrf_token'];

$profileMessage = '';
$profileMessageType = '';


/*
|--------------------------------------------------------------------------
| Handle Profile Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $submittedToken = $_POST['csrf_token'] ?? '';

        if (
            empty($submittedToken) ||
            !hash_equals($profileCsrfToken, $submittedToken)
        ) {
            throw new RuntimeException(
                'Invalid request. Please refresh the page and try again.'
            );
        }


        $action = $_POST['action'] ?? '';


        if ($action !== 'update_profile') {
            throw new RuntimeException('Invalid profile request.');
        }


        /*
        |--------------------------------------------------------------------------
        | Get Form Data
        |--------------------------------------------------------------------------
        */

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $bio = trim($_POST['bio'] ?? '');
        $locationName = trim($_POST['location_name'] ?? '');

        $yearsExperience = (int)(
            $_POST['years_experience'] ?? 0
        );

        $availability = $_POST['availability'] ?? 'available';

    
        /*
        |--------------------------------------------------------------------------
        | Profile Image Upload
        |--------------------------------------------------------------------------
        */

        $newProfileImage = null;

        $uploadDirectory =
            __DIR__ . '/../../assets/images/providers/';


        if (!is_dir($uploadDirectory)) {

            if (!mkdir(
                $uploadDirectory,
                0775,
                true
            ) && !is_dir($uploadDirectory)) {

                throw new RuntimeException(
                    'Unable to create image directory.'
                );
            }
        }

        if (
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK
    ) {

        throw new RuntimeException(
            'Profile image upload failed.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | File Size
    |--------------------------------------------------------------------------
    */

    $maxFileSize = 5 * 1024 * 1024;


    if (
        $_FILES['profile_image']['size']
        > $maxFileSize
    ) {

        throw new RuntimeException(
            'Profile image must not be larger than 5MB.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate MIME Type
    |--------------------------------------------------------------------------
    */

    $allowedImageTypes = [

        'image/jpeg' => 'jpg',

        'image/png' => 'png',

        'image/webp' => 'webp'

    ];


    $fileInfo = finfo_open(
        FILEINFO_MIME_TYPE
    );


    $mimeType = finfo_file(
        $fileInfo,
        $_FILES['profile_image']['tmp_name']
    );


    finfo_close($fileInfo);


    if (
        !isset(
            $allowedImageTypes[$mimeType]
        )
    ) {

        throw new RuntimeException(
            'Invalid image format. Please use JPG, PNG or WEBP.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Secure Filename
    |--------------------------------------------------------------------------
    */

    $extension =
        $allowedImageTypes[$mimeType];


    $newProfileImage =
        'provider_' .
        $providerId .
        '_' .
        bin2hex(random_bytes(8)) .
        '.' .
        $extension;


    $destination =
        $uploadDirectory .
        $newProfileImage;


    /*
    |--------------------------------------------------------------------------
    | Move File
    |--------------------------------------------------------------------------
    */

    if (
        !move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $destination
        )
    ) {

        throw new RuntimeException(
            'Unable to save profile image.'
        );
    }
}

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if ($fullName === '') {
            throw new RuntimeException(
                'Full name is required.'
            );
        }

        if (mb_strlen($fullName) < 3) {
            throw new RuntimeException(
                'Full name must contain at least 3 characters.'
            );
        }


        if ($email === '') {
            throw new RuntimeException(
                'Email address is required.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'Please enter a valid email address.'
            );
        }


        if ($phone === '') {
            throw new RuntimeException(
                'Phone number is required.'
            );
        }

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            throw new RuntimeException(
                'Please enter a valid phone number.'
            );
        }


        if ($yearsExperience < 0 || $yearsExperience > 100) {
            throw new RuntimeException(
                'Please enter a valid number of years of experience.'
            );
        }


        $allowedAvailability = [
            'available',
            'busy',
            'offline'
        ];

        if (!in_array($availability, $allowedAvailability, true)) {
            $availability = 'available';
        }


        /*
        |--------------------------------------------------------------------------
        | Check Email Ownership
        |--------------------------------------------------------------------------
        */

        $emailStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $emailStmt->execute([
            $email,
            $providerId
        ]);

        if ($emailStmt->fetch()) {
            throw new RuntimeException(
                'That email address is already being used by another account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Phone Ownership
        |--------------------------------------------------------------------------
        */

        $phoneStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE phone = ?
            AND id != ?
            LIMIT 1
        ");

        $phoneStmt->execute([
            $phone,
            $providerId
        ]);

        if ($phoneStmt->fetch()) {
            throw new RuntimeException(
                'That phone number is already being used by another account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | Update Users
        |--------------------------------------------------------------------------
        */

        $userStmt = $pdo->prepare("
            UPDATE users
            SET
                full_name = ?,
                email = ?,
                phone = ?
            WHERE id = ?
            AND role = 'provider'
        ");

$userStmt->execute([
    $fullName,
    $email,
    $phone,
    $providerId
]);


$_SESSION['full_name'] =
    $fullName;s

        /*
        |--------------------------------------------------------------------------
        | Check Provider Profile
        |--------------------------------------------------------------------------
        */

        $profileCheckStmt = $pdo->prepare("
            SELECT user_id
            FROM provider_profiles
            WHERE user_id = ?
            LIMIT 1
        ");

        $profileCheckStmt->execute([
            $providerId
        ]);

        $profileExists = $profileCheckStmt->fetchColumn();


        /*
        |--------------------------------------------------------------------------
        | Create / Update Provider Profile
        |--------------------------------------------------------------------------
        */

/*
|--------------------------------------------------------------------------
| Update With New Image
|--------------------------------------------------------------------------
*/

if ($profileExists) {

    if ($newProfileImage !== null) {

        $profileStmt = $pdo->prepare("
            UPDATE provider_profiles
            SET
                bio = ?,
                profile_image = ?,
                years_experience = ?,
                location_name = ?,
                availability = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");

        $profileStmt->execute([
            $bio !== '' ? $bio : null,
            $newProfileImage,
            $yearsExperience,
            $locationName !== ''
                ? $locationName
                : null,
            $availability,
            $providerId
        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update Without Changing Image
        |--------------------------------------------------------------------------
        */

        $profileStmt = $pdo->prepare("
            UPDATE provider_profiles
            SET
                bio = ?,
                years_experience = ?,
                location_name = ?,
                availability = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");

        $profileStmt->execute([
            $bio !== '' ? $bio : null,
            $yearsExperience,
            $locationName !== ''
                ? $locationName
                : null,
            $availability,
            $providerId
        ]);

    }

} else {

    /*
    |--------------------------------------------------------------------------
    | Create New Provider Profile
    |--------------------------------------------------------------------------
    */

    $profileImageToSave =
        $newProfileImage ?? 'default.jpg';

    $profileStmt = $pdo->prepare("
        INSERT INTO provider_profiles
        (
            user_id,
            bio,
            profile_image,
            years_experience,
            location_name,
            availability,
            created_at,
            updated_at
        )
        VALUES
        (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $profileStmt->execute([
        $providerId,
        $bio !== '' ? $bio : null,
        $profileImageToSave,
        $yearsExperience,
        $locationName !== ''
            ? $locationName
            : null,
        $availability
    ]);

}


        /*
        |--------------------------------------------------------------------------
        | Calculate Profile Completion
        |--------------------------------------------------------------------------
        */

        $completionItems = [
            $fullName !== '',
            $email !== '',
            $phone !== '',
            $bio !== '',
            $locationName !== '',
            $yearsExperience > 0
        ];

        $completedItems = count(
            array_filter($completionItems)
        );

        $totalItems = count($completionItems);

        $completionPercentage = (int)round(
            ($completedItems / $totalItems) * 100
        );


        /*
        |--------------------------------------------------------------------------
        | Update Profile Completion Flag
        |--------------------------------------------------------------------------
        */

        $profileCompleted = $completionPercentage >= 100 ? 1 : 0;

        $completionStmt = $pdo->prepare("
            UPDATE users
            SET profile_completed = ?
            WHERE id = ?
            AND role = 'provider'
        ");

        $completionStmt->execute([
            $profileCompleted,
            $providerId
        ]);


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $pdo->commit();


        $_SESSION['profile_message'] =
            'Profile updated successfully.';

        $_SESSION['profile_message_type'] =
            'success';


        /*
        |--------------------------------------------------------------------------
        | Prevent Form Resubmission
        |--------------------------------------------------------------------------
        */

        header(
            'Location: dashboard.php?page=profile'
        );

        exit();


    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $profileMessage = $e->getMessage();
        $profileMessageType = 'error';
    }
}


/*
|--------------------------------------------------------------------------
| Flash Message
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['profile_message'])) {

    $profileMessage =
        $_SESSION['profile_message'];

    $profileMessageType =
        $_SESSION['profile_message_type'] ?? 'success';

    unset(
        $_SESSION['profile_message'],
        $_SESSION['profile_message_type']
    );
}

/*
|--------------------------------------------------------------------------
| Load Current Provider Profile
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.id,
        u.full_name,
        u.email,
        u.phone,
        u.role,
        u.profile_completed,

        p.bio,
        p.profile_image,
        p.cover_image,
        p.years_experience,
        p.latitude,
        p.longitude,
        p.location_name,
        p.location_updated_at,
        p.availability,
        p.is_verified,
        p.average_rating,
        p.total_reviews,
        p.created_at AS profile_created_at

    FROM users u

    LEFT JOIN provider_profiles p
        ON p.user_id = u.id

    WHERE u.id = ?
    AND u.role = 'provider'

    LIMIT 1
");

$stmt->execute([$providerId]);

$provider = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Safety Check
|--------------------------------------------------------------------------
*/

if (!$provider) {
    echo '
        <div class="profile-error">
            <h2>Provider profile not found</h2>
            <p>Your provider account could not be loaded.</p>
        </div>
    ';

    return;
}


/*
|--------------------------------------------------------------------------
| Prepare Profile Data
|--------------------------------------------------------------------------
*/

$fullName = $provider['full_name'] ?? 'Provider';
$email = $provider['email'] ?? '';
$phone = $provider['phone'] ?? '';

$bio = $provider['bio'] ?? '';
$locationName = $provider['location_name'] ?? '';

$yearsExperience = (int)($provider['years_experience'] ?? 0);

$availability = strtolower(
    $provider['availability'] ?? 'available'
);

$isVerified = (int)($provider['is_verified'] ?? 0);

$averageRating = (float)(
    $provider['average_rating'] ?? 0
);

$totalReviews = (int)(
    $provider['total_reviews'] ?? 0
);

$profileCompleted = (int)(
    $provider['profile_completed'] ?? 0
);

$profileImage = $provider['profile_image'] ?? 'default.jpg';

$coverImage = $provider['cover_image'] ?? 'default-cover.jpg';


/*
|--------------------------------------------------------------------------
| Image Paths
|--------------------------------------------------------------------------
*/

$profileImagePath =
    '/services-finder/assets/images/providers/default.jpg';


if (
    !empty($profileImage) &&
    $profileImage !== 'default.jpg'
) {

    $profileImagePath =
        '/services-finder/assets/images/providers/' .
        rawurlencode(
            basename($profileImage)
        );

}


/*
|--------------------------------------------------------------------------
| Cover Image Path
|--------------------------------------------------------------------------
*/

$coverImagePath =
    '/services-finder/assets/images/providers/default-cover.jpg';


if (
    !empty($coverImage) &&
    $coverImage !== 'default-cover.jpg'
) {

    $coverImagePath =
        '/services-finder/assets/images/providers/' .
        rawurlencode(
            basename($coverImage)
        );

}


/*
|--------------------------------------------------------------------------
| Profile Completion
|--------------------------------------------------------------------------
*/

$completionItems = [
    !empty($fullName),
    !empty($email),
    !empty($phone),
    !empty($bio),
    !empty($locationName),
    $yearsExperience > 0
];

$completedItems = count(
    array_filter($completionItems)
);

$totalItems = count($completionItems);

$completionPercentage = (int)round(
    ($completedItems / $totalItems) * 100
);


/*
|--------------------------------------------------------------------------
| Availability
|--------------------------------------------------------------------------
*/

$availabilityLabels = [
    'available' => 'Available',
    'busy' => 'Busy',
    'offline' => 'Offline'
];

$availabilityLabel =
    $availabilityLabels[$availability]
    ?? 'Available';
?>


<div class="profile-page">

<?php if ($profileMessage !== ''): ?>

    <div class="profile-alert <?= $profileMessageType === 'success'
        ? 'profile-alert-success'
        : 'profile-alert-error'
    ?>">

        <div class="profile-alert-icon">
            <i class="fa-solid <?= $profileMessageType === 'success'
                ? 'fa-circle-check'
                : 'fa-circle-exclamation'
            ?>"></i>
        </div>

        <span>
            <?= htmlspecialchars($profileMessage) ?>
        </span>

        <button
            type="button"
            class="profile-alert-close"
            id="closeProfileAlert">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>

<?php endif; ?>

    <!-- =========================================================
         PROFILE HEADER
    ========================================================== -->

    <section class="profile-hero">

        <div class="profile-cover">

            <?php if (
                !empty($coverImage) &&
                $coverImage !== 'default-cover.jpg'
            ): ?>

                <img
                    src="<?= htmlspecialchars($coverImagePath) ?>"
                    alt="Profile Cover">

            <?php else: ?>

                <div class="default-profile-cover"></div>

            <?php endif; ?>

        </div>


        <div class="profile-header-content">


            <!-- PROFILE IMAGE -->

<div class="profile-avatar-wrapper">

    <div class="profile-avatar">

        <?php if (
            !empty($profileImage) &&
            $profileImage !== 'default.jpg'
        ): ?>

            <img
                src="<?= htmlspecialchars($profileImagePath) ?>"
                alt="<?= htmlspecialchars($fullName) ?>">

        <?php else: ?>

            <div
                class="profile-avatar-placeholder"
                id="profileImagePlaceholder">

                <i class="fa-solid fa-user"></i>

            </div>

        <?php endif; ?>

    </div>


    <!-- CHANGE PHOTO BUTTON -->

    <button
        type="button"
        class="change-profile-photo-btn"
        id="changeProfilePhotoBtn"
        aria-label="Change profile photo">

        <i class="fa-solid fa-camera"></i>

    </button>


    <?php if ($isVerified === 1): ?>

        <span class="verified-badge">

            <i class="fa-solid fa-circle-check"></i>

        </span>

    <?php endif; ?>

</div>

            <!-- PROVIDER INFORMATION -->

            <div class="profile-main-info">

                <div class="profile-name-row">

                    <h1>
                        <?= htmlspecialchars($fullName) ?>
                    </h1>


                    <?php if ($isVerified === 1): ?>

                        <span class="profile-verified">

                            <i class="fa-solid fa-circle-check"></i>

                            Verified

                        </span>

                    <?php endif; ?>

                </div>


                <p class="profile-role">

                    <i class="fa-solid fa-briefcase"></i>

                    Service Provider

                </p>


                <div class="profile-location">

                    <i class="fa-solid fa-location-dot"></i>

                    <?php if (!empty($locationName)): ?>

                        <?= htmlspecialchars($locationName) ?>

                    <?php else: ?>

                        Location not added

                    <?php endif; ?>

                </div>


            </div>



            <!-- PROFILE ACTIONS -->

            <div class="profile-header-actions">

                <span class="profile-availability availability-<?= htmlspecialchars($availability) ?>">

                    <span class="availability-dot"></span>

                    <?= htmlspecialchars($availabilityLabel) ?>

                </span>


                <button
                    type="button"
                    class="edit-profile-btn"
                    id="openEditProfile">

                    <i class="fa-solid fa-pen"></i>

                    Edit Profile

                </button>

            </div>


        </div>

    </section>



    <!-- =========================================================
         PROFILE CONTENT
    ========================================================== -->

    <div class="profile-layout">


        <!-- =====================================================
             LEFT COLUMN
        ====================================================== -->

        <div class="profile-main-column">


            <!-- ABOUT -->

            <section class="profile-card">

                <div class="profile-card-header">

                    <div>

                        <h2>

                            <i class="fa-regular fa-user"></i>

                            About Me

                        </h2>

                        <p>
                            Information about your professional background.
                        </p>

                    </div>

                </div>


                <div class="profile-card-body">

                    <?php if (!empty($bio)): ?>

                        <p class="profile-bio">

                            <?= nl2br(
                                htmlspecialchars($bio)
                            ) ?>

                        </p>

                    <?php else: ?>

                        <div class="profile-empty-state">

                            <i class="fa-regular fa-clipboard"></i>

                            <p>
                                Tell clients about yourself,
                                your skills and your experience.
                            </p>

                            <button
                                type="button"
                                class="profile-text-button open-edit-profile">

                                Add Bio

                            </button>

                        </div>

                    <?php endif; ?>

                </div>

            </section>



            <!-- PERSONAL INFORMATION -->

            <section class="profile-card">

                <div class="profile-card-header">

                    <div>

                        <h2>

                            <i class="fa-regular fa-address-card"></i>

                            Personal Information

                        </h2>

                        <p>
                            Your basic account information.
                        </p>

                    </div>

                </div>


                <div class="profile-card-body">

                    <div class="profile-info-grid">


                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-regular fa-user"></i>

                            </div>


                            <div>

                                <span>
                                    Full Name
                                </span>

                                <strong>
                                    <?= htmlspecialchars($fullName) ?>
                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-regular fa-envelope"></i>

                            </div>


                            <div>

                                <span>
                                    Email Address
                                </span>

                                <strong>
                                    <?= htmlspecialchars($email) ?>
                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-solid fa-phone"></i>

                            </div>


                            <div>

                                <span>
                                    Phone Number
                                </span>

                                <strong>
                                    <?= htmlspecialchars($phone) ?>
                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-solid fa-location-dot"></i>

                            </div>


                            <div>

                                <span>
                                    Location
                                </span>

                                <strong>

                                    <?= !empty($locationName)
                                        ? htmlspecialchars($locationName)
                                        : 'Not added'
                                    ?>

                                </strong>

                            </div>

                        </div>


                    </div>

                </div>

            </section>



            <!-- PROFESSIONAL INFORMATION -->

            <section class="profile-card">

                <div class="profile-card-header">

                    <div>

                        <h2>

                            <i class="fa-solid fa-briefcase"></i>

                            Professional Information

                        </h2>

                        <p>
                            Information clients can use to know you better.
                        </p>

                    </div>

                </div>


                <div class="profile-card-body">

                    <div class="profile-info-grid">


                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-solid fa-award"></i>

                            </div>


                            <div>

                                <span>
                                    Experience
                                </span>

                                <strong>

                                    <?= $yearsExperience > 0
                                        ? $yearsExperience . ' Years'
                                        : 'Not added'
                                    ?>

                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-solid fa-star"></i>

                            </div>


                            <div>

                                <span>
                                    Rating
                                </span>

                                <strong>

                                    <?= number_format(
                                        $averageRating,
                                        1
                                    ) ?>

                                    / 5

                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-regular fa-comment"></i>

                            </div>


                            <div>

                                <span>
                                    Reviews
                                </span>

                                <strong>

                                    <?= number_format(
                                        $totalReviews
                                    ) ?>

                                </strong>

                            </div>

                        </div>



                        <div class="profile-info-item">

                            <div class="profile-info-icon">

                                <i class="fa-solid fa-circle-check"></i>

                            </div>


                            <div>

                                <span>
                                    Verification
                                </span>

                                <strong>

                                    <?= $isVerified === 1
                                        ? 'Verified'
                                        : 'Pending Verification'
                                    ?>

                                </strong>

                            </div>

                        </div>


                    </div>

                </div>

            </section>


        </div>



        <!-- =====================================================
             RIGHT COLUMN
        ====================================================== -->

        <aside class="profile-side-column">


            <!-- PROFILE COMPLETION -->

            <section class="profile-card profile-completion-card">

                <div class="profile-card-header">

                    <div>

                        <h2>
                            Complete Your Profile
                        </h2>

                        <p>
                            Complete your profile to help clients trust you.
                        </p>

                    </div>

                </div>


                <div class="profile-card-body">


                    <div class="profile-progress-header">

                        <strong>
                            <?= $completionPercentage ?>% Complete
                        </strong>

                        <span>

                            <?= $completedItems ?>

                            /

                            <?= $totalItems ?>

                        </span>

                    </div>


                    <div class="profile-progress-bar">

                        <div
                            class="profile-progress-fill"
                            style="width: <?= $completionPercentage ?>%">
                        </div>

                    </div>


                    <ul class="profile-completion-list">


                        <li class="<?= !empty($bio) ? 'completed' : '' ?>">

                            <i class="fa-solid fa-check"></i>

                            Add your bio

                        </li>


                        <li class="<?= !empty($locationName) ? 'completed' : '' ?>">

                            <i class="fa-solid fa-check"></i>

                            Add your location

                        </li>


                        <li class="<?= $yearsExperience > 0 ? 'completed' : '' ?>">

                            <i class="fa-solid fa-check"></i>

                            Add experience

                        </li>


                        <li class="<?= $profileImage !== 'default.jpg' ? 'completed' : '' ?>">

                            <i class="fa-solid fa-check"></i>

                            Add profile photo

                        </li>


                    </ul>


                    <button
                        type="button"
                        class="complete-profile-btn open-edit-profile">

                        Complete Profile

                    </button>


                </div>

            </section>



            <!-- PROFILE STATUS -->

            <section class="profile-card profile-status-card">

                <div class="profile-card-header">

                    <div>

                        <h2>
                            Profile Status
                        </h2>

                    </div>

                </div>


                <div class="profile-card-body">


                    <div class="profile-status-row">

                        <span>
                            Availability
                        </span>

                        <strong class="status-text-<?= htmlspecialchars($availability) ?>">

                            <?= htmlspecialchars($availabilityLabel) ?>

                        </strong>

                    </div>


                    <div class="profile-status-row">

                        <span>
                            Verification
                        </span>

                        <strong>

                            <?= $isVerified === 1
                                ? 'Verified'
                                : 'Pending'
                            ?>

                        </strong>

                    </div>


                    <div class="profile-status-row">

                        <span>
                            Profile
                        </span>

                        <strong>

                            <?= $completionPercentage === 100
                                ? 'Complete'
                                : 'Incomplete'
                            ?>

                        </strong>

                    </div>


                </div>

            </section>


        </aside>


    </div>


</div>

<!-- =========================================================
     EDIT PROFILE MODAL
========================================================== -->

<div
    class="profile-modal"
    id="editProfileModal"
    aria-hidden="true">

    <div class="profile-modal-backdrop"></div>


    <div
        class="profile-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="editProfileTitle">


        <!-- HEADER -->

        <div class="profile-modal-header">

            <div>

                <span class="profile-modal-icon">
                    <i class="fa-solid fa-user-pen"></i>
                </span>

                <div>

                    <h2 id="editProfileTitle">
                        Edit Profile
                    </h2>

                    <p>
                        Update your personal and professional information.
                    </p>

                </div>

            </div>


            <button
                type="button"
                class="profile-modal-close"
                data-close-profile-modal>

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <!-- FORM -->

        <form
            method="POST"
            action="dashboard.php?page=profile"
            id="editProfileForm"
            class="profile-edit-form"
            enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($profileCsrfToken) ?>">
            <input type="hidden" name="action" value="update_profile">

<input
    type="file"
    id="profileImageInput"
    name="profile_image"
    accept="image/jpeg,image/png,image/webp"
    hidden>

            <!-- PERSONAL INFORMATION -->

            <div class="profile-form-section">
                <div class="profile-form-section-title">
                    <i class="fa-regular fa-address-card"></i>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Information used on your FindPro account.</p>
                    </div>
                </div>


                <div class="profile-form-grid">


                    <!-- FULL NAME -->

                    <div class="profile-form-group full-width">
                        <label for="editFullName">
                            Full Name
                            <span>*</span>
                        </label>

                        <div class="profile-input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="editFullName" name="full_name"
                                value="<?= htmlspecialchars($fullName) ?>"
                                placeholder="Enter your full name" maxlength="100" required>

                        </div>

                    </div>


                    <!-- EMAIL -->

                    <div class="profile-form-group">

                        <label for="editEmail">
                            Email Address
                            <span>*</span>
                        </label>

                        <div class="profile-input-wrapper">

                            <i class="fa-regular fa-envelope"></i>

                            <input type="email" id="editEmail" name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                placeholder="example@email.com" maxlength="150" required>
                        </div>
                    </div>


                    <!-- PHONE -->

                    <div class="profile-form-group">

                        <label for="editPhone">
                            Phone Number
                            <span>*</span>
                        </label>

                        <div class="profile-input-wrapper">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="editPhone" name="phone"
                                value="<?= htmlspecialchars($phone) ?>"
                                placeholder="07XXXXXXXX" maxlength="15" required>
                        </div>

                    </div>

                </div>

            </div>


            <!-- PROFESSIONAL INFORMATION -->

            <div class="profile-form-section">

                <div class="profile-form-section-title">

                    <i class="fa-solid fa-briefcase"></i>

                    <div>

                        <h3>
                            Professional Information
                        </h3>

                        <p>
                            Help clients understand your experience and availability.
                        </p>

                    </div>

                </div>


                <div class="profile-form-grid">


                    <!-- BIO -->

                    <div class="profile-form-group full-width">

                        <label for="editBio">
                            About You
                        </label>

                        <textarea id="editBio" name="bio" rows="5" maxlength="1000"
                            placeholder="Tell clients about your skills, experience and the services you provide..."><?= htmlspecialchars($bio) ?></textarea>

                        <div class="profile-character-count">

                            <span id="bioCharacterCount">
                                <?= mb_strlen($bio) ?>
                            </span>

                            / 1000

                        </div>

                    </div>


                    <!-- EXPERIENCE -->

                    <div class="profile-form-group">

                        <label for="editExperience">
                            Years of Experience
                        </label>

                        <div class="profile-input-wrapper">

                            <i class="fa-solid fa-award"></i>

                            <input type="number" id="editExperience" name="years_experience"
                                value="<?= $yearsExperience ?>"
                                min="0" max="100" step="1" placeholder="Example: 5">

                        </div>

                    </div>


                    <!-- LOCATION -->

                    <div class="profile-form-group">

                        <label for="editLocation">
                            Location
                        </label>

                        <div class="profile-input-wrapper">

                            <i class="fa-solid fa-location-dot"></i>

                            <input type="text" id="editLocation" name="location_name"
                                value="<?= htmlspecialchars($locationName) ?>"
                                placeholder="Example: Arusha, Tanzania" maxlength="150">
                        </div>

                    </div>

                    <!-- AVAILABILITY -->
                    <div class="profile-form-group full-width">
                        <label for="editAvailability">Availability</label>
                        <select id="editAvailability" name="availability">
                            <option value="available" <?= $availability === 'available'
                                    ? 'selected'
                                    : ''
                                ?>>
                                Available
                            </option>

                            <option
                                value="busy"
                                <?= $availability === 'busy'
                                    ? 'selected'
                                    : ''
                                ?>>

                                Busy
                            </option>
                            <option
                                value="offline"
                                <?= $availability === 'offline'
                                    ? 'selected'
                                    : ''
                                ?>>
                                Offline
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="profile-modal-footer">
                <button type="button" class="profile-cancel-btn" data-close-profile-modal>
                    Cancel
                </button>
                <button type="submit" class="profile-save-btn" id="saveProfileBtn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const modal =
        document.getElementById('editProfileModal');

    const openButtons =
        document.querySelectorAll(
            '#openEditProfile, .open-edit-profile'
        );

    const closeButtons =
        document.querySelectorAll(
            '[data-close-profile-modal]'
        );

    const backdrop = modal
        ? modal.querySelector(
            '.profile-modal-backdrop'
        )
        : null;

    const form =
        document.getElementById(
            'editProfileForm'
        );

    const bio =
        document.getElementById(
            'editBio'
        );

    const bioCount =
        document.getElementById(
            'bioCharacterCount'
        );


    /*
    |--------------------------------------------------------------------------
    | Profile Image Elements
    |--------------------------------------------------------------------------
    */

    const changeProfilePhotoBtn =
        document.getElementById(
            'changeProfilePhotoBtn'
        );

    const profileImageInput =
        document.getElementById(
            'profileImageInput'
        );

    const profileImagePreview =
        document.getElementById(
            'profileImagePreview'
        );

    const profileImagePlaceholder =
        document.getElementById(
            'profileImagePlaceholder'
        );

    const profileImageFileName =
        document.getElementById(
            'profileImageFileName'
        );


    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    function openProfileModal() {

        if (!modal) {
            return;
        }

        modal.classList.add(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'profile-modal-open'
        );


        const firstInput =
            document.getElementById(
                'editFullName'
            );


        if (firstInput) {

            setTimeout(
                function () {

                    firstInput.focus();

                },
                100
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    function closeProfileModal() {

        if (!modal) {
            return;
        }

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'profile-modal-open'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Open Edit Profile Buttons
    |--------------------------------------------------------------------------
    */

    openButtons.forEach(
        function (button) {

            button.addEventListener(
                'click',
                function () {

                    openProfileModal();

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Close Modal Buttons
    |--------------------------------------------------------------------------
    */

    closeButtons.forEach(
        function (button) {

            button.addEventListener(
                'click',
                function () {

                    closeProfileModal();

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Close Modal By Backdrop
    |--------------------------------------------------------------------------
    */

    if (backdrop) {

        backdrop.addEventListener(
            'click',
            function () {

                closeProfileModal();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal With Escape
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                modal &&
                modal.classList.contains(
                    'is-open'
                )
            ) {

                closeProfileModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Bio Character Counter
    |--------------------------------------------------------------------------
    */

    if (bio && bioCount) {

        bio.addEventListener(
            'input',
            function () {

                bioCount.textContent =
                    bio.value.length;

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Change Profile Photo Button
    |--------------------------------------------------------------------------
    */

    if (
        changeProfilePhotoBtn &&
        profileImageInput
    ) {

        changeProfilePhotoBtn.addEventListener(
            'click',
            function () {

                profileImageInput.click();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Profile Image Preview
    |--------------------------------------------------------------------------
    */

    if (profileImageInput) {

        profileImageInput.addEventListener(
            'change',
            function () {

                const file =
                    this.files[0];


                /*
                |--------------------------------------------------------------------------
                | No File Selected
                |--------------------------------------------------------------------------
                */

                if (!file) {

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Validate File Size
                |--------------------------------------------------------------------------
                */

                const maxSize =
                    5 * 1024 * 1024;


                if (
                    file.size > maxSize
                ) {

                    alert(
                        'Profile image must not be larger than 5MB.'
                    );

                    this.value = '';

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Validate File Type
                |--------------------------------------------------------------------------
                */

                const allowedTypes = [

                    'image/jpeg',
                    'image/png',
                    'image/webp'

                ];


                if (
                    !allowedTypes.includes(
                        file.type
                    )
                ) {

                    alert(
                        'Please select a JPG, PNG or WEBP image.'
                    );

                    this.value = '';

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Show File Name
                |--------------------------------------------------------------------------
                */

                if (
                    profileImageFileName
                ) {

                    profileImageFileName.textContent =
                        file.name;

                }


                /*
                |--------------------------------------------------------------------------
                | Preview Image
                |--------------------------------------------------------------------------
                */

                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {


                        /*
                        |--------------------------------------------------------------------------
                        | Update Preview
                        |--------------------------------------------------------------------------
                        */

                        if (
                            profileImagePreview
                        ) {

                            profileImagePreview.src =
                                event.target.result;

                            profileImagePreview.style.display =
                                'block';

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Hide Placeholder
                        |--------------------------------------------------------------------------
                        */

                        if (
                            profileImagePlaceholder
                        ) {

                            profileImagePlaceholder.style.display =
                                'none';

                        }

                    };


                reader.readAsDataURL(
                    file
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Double Submit
    |--------------------------------------------------------------------------
    */

    if (form) {

        form.addEventListener(
            'submit',
            function () {


                const saveButton =
                    document.getElementById(
                        'saveProfileBtn'
                    );


                if (!saveButton) {

                    return;

                }


                saveButton.disabled =
                    true;


                saveButton.innerHTML =
                    `
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Saving...
                    `;

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Close Alert
    |--------------------------------------------------------------------------
    */

    const alertClose =
        document.getElementById(
            'closeProfileAlert'
        );


    if (alertClose) {

        alertClose.addEventListener(
            'click',
            function () {


                const alert =
                    this.closest(
                        '.profile-alert'
                    );


                if (alert) {

                    alert.remove();

                }

            }
        );

    }


});
</script>