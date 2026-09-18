<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

/*
|--------------------------------------------------------------------------
| Current Client
|--------------------------------------------------------------------------
*/

$clientUserId = (int)($_SESSION['user_id'] ?? 0);

if ($clientUserId <= 0) {
    exit('Unauthorized access.');
}

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

if (isset($pdo) && $pdo instanceof PDO) {
    $db = $pdo;
} elseif (isset($conn) && $conn instanceof PDO) {
    $db = $conn;
} else {
    exit('Database connection not available.');
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function client_profile_h(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['client_profile_csrf_token'])) {
    $_SESSION['client_profile_csrf_token'] =
        bin2hex(random_bytes(32));
}

$csrfToken =
    $_SESSION['client_profile_csrf_token'];

/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

/*
|--------------------------------------------------------------------------
| Current Profile
|--------------------------------------------------------------------------
*/

$profileStmt = $db->prepare("
    SELECT
        u.id,
        u.full_name,
        u.email,
        u.phone,
        u.account_status,
        u.created_at,
        u.profile_completed,

        cp.profile_image

    FROM users u

    LEFT JOIN client_profiles cp
        ON cp.user_id = u.id

    WHERE u.id = ?
      AND u.role = 'client'

    LIMIT 1
");

$profileStmt->execute([
    $clientUserId
]);

$client = $profileStmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    exit('Client profile not found.');
}

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($postedToken) ||
        !hash_equals($csrfToken, $postedToken)
    ) {
        $errorMessage = 'Security validation failed.';
    } else {

        $action = $_POST['action'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Update Personal Information
        |--------------------------------------------------------------------------
        */

        if ($action === 'update_profile') {

            $fullName = trim(
                (string)($_POST['full_name'] ?? '')
            );

            $email = trim(
                (string)($_POST['email'] ?? '')
            );

            $phone = trim(
                (string)($_POST['phone'] ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | Validation
            |--------------------------------------------------------------------------
            */

            if ($fullName === '') {

                $errorMessage =
                    'Full name is required.';

            } elseif (mb_strlen($fullName) < 2) {

                $errorMessage =
                    'Please enter a valid full name.';

            } elseif ($email === '') {

                $errorMessage =
                    'Email address is required.';

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $errorMessage =
                    'Please enter a valid email address.';

            } elseif ($phone === '') {

                $errorMessage =
                    'Phone number is required.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Check Email
                |--------------------------------------------------------------------------
                */

                $emailStmt = $db->prepare("
                    SELECT id
                    FROM users
                    WHERE email = ?
                      AND id != ?
                    LIMIT 1
                ");

                $emailStmt->execute([
                    $email,
                    $clientUserId
                ]);

                $emailExists =
                    $emailStmt->fetchColumn();

                if ($emailExists) {

                    $errorMessage =
                        'That email address is already in use.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Update User
                    |--------------------------------------------------------------------------
                    */

                    $updateStmt = $db->prepare("
                        UPDATE users
                        SET
                            full_name = ?,
                            email = ?,
                            phone = ?,
                            profile_completed = 1
                        WHERE id = ?
                          AND role = 'client'
                        LIMIT 1
                    ");

                    $updateStmt->execute([
                        $fullName,
                        $email,
                        $phone,
                        $clientUserId
                    ]);

                    $successMessage =
                        'Your profile has been updated successfully.';

                    /*
                    |--------------------------------------------------------------------------
                    | Refresh Data
                    |--------------------------------------------------------------------------
                    */

                    $profileStmt->execute([
                        $clientUserId
                    ]);

                    $client =
                        $profileStmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Profile Image
        |--------------------------------------------------------------------------
        */

        if ($action === 'update_profile_image') {

            if (
                !isset($_FILES['profile_image']) ||
                !is_array($_FILES['profile_image'])
            ) {

                $errorMessage =
                    'Please choose an image.';

            } else {

                $image = $_FILES['profile_image'];

                if (
                    !isset($image['error']) ||
                    $image['error'] !== UPLOAD_ERR_OK
                ) {

                    $errorMessage =
                        'Unable to upload the selected image.';

                } elseif (
                    !isset($image['size']) ||
                    $image['size'] > 5 * 1024 * 1024
                ) {

                    $errorMessage =
                        'Image size must not exceed 5 MB.';

                } else {

                    $tmpName =
                        (string)$image['tmp_name'];

                    $imageInfo =
                        @getimagesize($tmpName);

                    if ($imageInfo === false) {

                        $errorMessage =
                            'The selected file is not a valid image.';

                    } else {

                        $allowedMimeTypes = [
                            'image/jpeg' => 'jpg',
                            'image/png'  => 'png',
                            'image/webp' => 'webp'
                        ];

                        $mimeType =
                            $imageInfo['mime'] ?? '';

                        if (
                            !isset(
                                $allowedMimeTypes[$mimeType]
                            )
                        ) {

                            $errorMessage =
                                'Only JPG, PNG and WebP images are allowed.';

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Upload Directory
                            |--------------------------------------------------------------------------
                            */

                            $uploadDirectory =
                                __DIR__
                                . '/../../assets/images/profiles/';

                            if (
                                !is_dir($uploadDirectory) &&
                                !mkdir(
                                    $uploadDirectory,
                                    0755,
                                    true
                                )
                            ) {

                                $errorMessage =
                                    'Unable to create the profile image directory.';

                            } else {

                                $extension =
                                    $allowedMimeTypes[$mimeType];

                                $newFileName =
                                    'client_'
                                    . $clientUserId
                                    . '_'
                                    . bin2hex(
                                        random_bytes(8)
                                    )
                                    . '.'
                                    . $extension;

                                $destination =
                                    $uploadDirectory
                                    . $newFileName;

                                if (
                                    !move_uploaded_file(
                                        $tmpName,
                                        $destination
                                    )
                                ) {

                                    $errorMessage =
                                        'Unable to save the uploaded image.';

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Get Old Image
                                    |--------------------------------------------------------------------------
                                    */

                                    $oldImage =
                                        trim(
                                            (string)(
                                                $client['profile_image']
                                                ?? ''
                                            )
                                        );

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Update / Create Client Profile
                                    |--------------------------------------------------------------------------
                                    */

                                    $profileExistsStmt =
                                        $db->prepare("
                                            SELECT user_id
                                            FROM client_profiles
                                            WHERE user_id = ?
                                            LIMIT 1
                                        ");

                                    $profileExistsStmt->execute([
                                        $clientUserId
                                    ]);

                                    $profileExists =
                                        $profileExistsStmt->fetchColumn();

                                    if ($profileExists) {

                                        $imageUpdateStmt =
                                            $db->prepare("
                                                UPDATE client_profiles
                                                SET
                                                    profile_image = ?
                                                WHERE user_id = ?
                                                LIMIT 1
                                            ");

                                        $imageUpdateStmt->execute([
                                            $newFileName,
                                            $clientUserId
                                        ]);

                                    } else {

                                        $imageInsertStmt =
                                            $db->prepare("
                                                INSERT INTO client_profiles (
                                                    user_id,
                                                    profile_image
                                                )
                                                VALUES (?, ?)
                                            ");

                                        $imageInsertStmt->execute([
                                            $clientUserId,
                                            $newFileName
                                        ]);
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Delete Old Custom Image
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        $oldImage !== '' &&
                                        !in_array(
                                            strtolower($oldImage),
                                            [
                                                'default.jpg',
                                                'default.png',
                                                'default.webp'
                                            ],
                                            true
                                        )
                                    ) {

                                        $oldPath =
                                            $uploadDirectory
                                            . basename($oldImage);

                                        if (
                                            is_file($oldPath)
                                        ) {
                                            @unlink($oldPath);
                                        }
                                    }

                                    $successMessage =
                                        'Profile photo updated successfully.';

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Refresh Data
                                    |--------------------------------------------------------------------------
                                    */

                                    $profileStmt->execute([
                                        $clientUserId
                                    ]);

                                    $client =
                                        $profileStmt->fetch(
                                            PDO::FETCH_ASSOC
                                        );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Profile Image
|--------------------------------------------------------------------------
*/

$profileImage =
    trim(
        (string)(
            $client['profile_image']
            ?? ''
        )
    );

$profileImageUrl =
    '../assets/images/default-avatar.jpg';

if ($profileImage !== '') {

    $profileImageUrl =
        '../assets/images/profiles/'
        . rawurlencode($profileImage);
}

/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

$nameParts =
    preg_split(
        '/\s+/',
        trim((string)$client['full_name'])
    );

$initials = '';

if (!empty($nameParts)) {

    $initials =
        strtoupper(
            mb_substr(
                (string)$nameParts[0],
                0,
                1
            )
        );

    if (count($nameParts) > 1) {

        $initials .=
            strtoupper(
                mb_substr(
                    (string)$nameParts[count($nameParts) - 1],
                    0,
                    1
                )
            );
    }
}

/*
|--------------------------------------------------------------------------
| Member Since
|--------------------------------------------------------------------------
*/

$memberSince = '';

if (!empty($client['created_at'])) {

    $createdTimestamp =
        strtotime(
            (string)$client['created_at']
        );

    if ($createdTimestamp !== false) {

        $memberSince =
            date(
                'M Y',
                $createdTimestamp
            );
    }
}

?>

<section class="client-profile-page">

    <!-- =========================================================
         PROFILE HEADER
         ========================================================= -->

    <div class="client-profile-header">

        <div>

            <h1>My Profile</h1>

            <p>
                Manage your personal information and profile photo.
            </p>

        </div>

    </div>


    <!-- =========================================================
         ALERTS
         ========================================================= -->

    <?php if ($successMessage !== ''): ?>

        <div class="profile-alert profile-alert-success">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                <?= client_profile_h($successMessage) ?>
            </span>

        </div>

    <?php endif; ?>


    <?php if ($errorMessage !== ''): ?>

        <div class="profile-alert profile-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <span>
                <?= client_profile_h($errorMessage) ?>
            </span>

        </div>

    <?php endif; ?>


    <!-- =========================================================
         PROFILE LAYOUT
         ========================================================= -->

    <div class="client-profile-layout">

        <!-- =====================================================
             PROFILE SUMMARY
             ===================================================== -->

        <aside class="client-profile-summary">

            <div class="profile-avatar-wrap">

                <?php if (
                    $profileImage !== '' &&
                    $profileImage !== 'default.jpg'
                ): ?>

                    <img
                        src="<?= client_profile_h($profileImageUrl) ?>"
                        alt="<?= client_profile_h($client['full_name']) ?>"
                        class="client-profile-avatar"
                    >

                <?php else: ?>

                    <div class="client-profile-avatar profile-initials">
                        <?= client_profile_h($initials) ?>
                    </div>

                <?php endif; ?>


                <label
                    for="profileImageInput"
                    class="profile-photo-button"
                    title="Change profile photo"
                >
                    <i class="fa-solid fa-camera"></i>
                </label>

            </div>


            <div class="profile-summary-info">

                <h2>
                    <?= client_profile_h(
                        (string)$client['full_name']
                    ) ?>
                </h2>

                <p>
                    <?= client_profile_h(
                        (string)$client['email']
                    ) ?>
                </p>

            </div>


            <div class="profile-status">

                <span
                    class="profile-status-dot <?= $client['account_status'] === 'active' ? 'active' : '' ?>"
                ></span>

                <?= client_profile_h(
                    ucfirst(
                        (string)$client['account_status']
                    )
                ) ?>

            </div>


            <?php if ($memberSince !== ''): ?>

                <div class="profile-member-since">

                    <span>
                        <i class="fa-regular fa-calendar"></i>
                    </span>

                    <div>

                        <small>
                            Member since
                        </small>

                        <strong>
                            <?= client_profile_h($memberSince) ?>
                        </strong>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Hidden Upload Form -->

            <form
                method="POST"
                enctype="multipart/form-data"
                id="profileImageForm"
            >

                <input
                    type="hidden"
                    name="action"
                    value="update_profile_image"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= client_profile_h($csrfToken) ?>"
                >

                <input
                    type="file"
                    name="profile_image"
                    id="profileImageInput"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
                >

            </form>

        </aside>


        <!-- =====================================================
             PROFILE INFORMATION
             ===================================================== -->

        <div class="client-profile-main">

            <div class="profile-section">

                <div class="profile-section-header">

                    <div>

                        <span class="profile-section-icon">
                            <i class="fa-regular fa-user"></i>
                        </span>

                        <div>

                            <h2>
                                Personal information
                            </h2>

                            <p>
                                Keep your account details up to date.
                            </p>

                        </div>

                    </div>

                </div>


                <form
                    method="POST"
                    class="profile-form"
                    autocomplete="off"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="update_profile"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= client_profile_h($csrfToken) ?>"
                    >


                    <div class="profile-form-grid">

                        <!-- Full Name -->

                        <div class="profile-field profile-field-full">

                            <label for="full_name">
                                Full name
                            </label>

                            <div class="profile-input-wrap">

                                <i class="fa-regular fa-user"></i>

                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    value="<?= client_profile_h(
                                        (string)$client['full_name']
                                    ) ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>

                        </div>


                        <!-- Email -->

                        <div class="profile-field">

                            <label for="email">
                                Email address
                            </label>

                            <div class="profile-input-wrap">

                                <i class="fa-regular fa-envelope"></i>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= client_profile_h(
                                        (string)$client['email']
                                    ) ?>"
                                    maxlength="120"
                                    required
                                >

                            </div>

                        </div>


                        <!-- Phone -->

                        <div class="profile-field">

                            <label for="phone">
                                Phone number
                            </label>

                            <div class="profile-input-wrap">

                                <i class="fa-solid fa-phone"></i>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="<?= client_profile_h(
                                        (string)$client['phone']
                                    ) ?>"
                                    maxlength="20"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <div class="profile-form-footer">

                        <p>
                            <i class="fa-solid fa-lock"></i>
                            Your account information is private.
                        </p>

                        <button
                            type="submit"
                            class="profile-save-button"
                        >
                            <i class="fa-solid fa-check"></i>
                            Save changes
                        </button>

                    </div>

                </form>

            </div>


            <!-- =================================================
                 ACCOUNT INFORMATION
                 ================================================= -->

            <div class="profile-section profile-account-section">

                <div class="profile-section-header">

                    <div>

                        <span class="profile-section-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>

                        <div>

                            <h2>
                                Account
                            </h2>

                            <p>
                                Basic information about your FindPro account.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="profile-account-list">

                    <div class="profile-account-row">

                        <div class="profile-account-label">

                            <i class="fa-solid fa-circle-info"></i>

                            <span>
                                Account status
                            </span>

                        </div>

                        <span
                            class="account-status-badge <?= $client['account_status'] === 'active' ? 'active' : 'inactive' ?>"
                        >
                            <?= client_profile_h(
                                ucfirst(
                                    (string)$client['account_status']
                                )
                            ) ?>
                        </span>

                    </div>


                    <div class="profile-account-row">

                        <div class="profile-account-label">

                            <i class="fa-regular fa-envelope"></i>

                            <span>
                                Email verification
                            </span>

                        </div>

                        <span
                            class="account-status-badge <?= !empty($client['email_verified_at']) ? 'active' : 'pending' ?>"
                        >

                            <?php if (
                                !empty($client['email_verified_at'])
                            ): ?>

                                Verified

                            <?php else: ?>

                                Not verified

                            <?php endif; ?>

                        </span>

                    </div>


                    <div class="profile-account-row">

                        <div class="profile-account-label">

                            <i class="fa-solid fa-id-card"></i>

                            <span>
                                Profile completion
                            </span>

                        </div>

                        <span
                            class="account-status-badge <?= !empty($client['profile_completed']) ? 'active' : 'pending' ?>"
                        >

                            <?php if (
                                !empty($client['profile_completed'])
                            ): ?>

                                Complete

                            <?php else: ?>

                                Incomplete

                            <?php endif; ?>

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<script>

document.addEventListener('DOMContentLoaded', () => {

    const imageInput =
        document.getElementById('profileImageInput');

    const imageForm =
        document.getElementById('profileImageForm');


    /*
    |--------------------------------------------------------------------------
    | Profile Image Upload
    |--------------------------------------------------------------------------
    */

    if (imageInput && imageForm) {

        imageInput.addEventListener(
            'change',
            () => {

                if (
                    imageInput.files &&
                    imageInput.files.length > 0
                ) {

                    const file =
                        imageInput.files[0];

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
                            'Please choose a JPG, PNG or WebP image.'
                        );

                        imageInput.value = '';

                        return;
                    }


                    if (
                        file.size >
                        5 * 1024 * 1024
                    ) {

                        alert(
                            'Image size must not exceed 5 MB.'
                        );

                        imageInput.value = '';

                        return;
                    }


                    imageForm.submit();
                }

            }
        );
    }

});

</script>