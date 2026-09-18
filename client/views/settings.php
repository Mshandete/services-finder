<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/language.php';
require_once __DIR__ . '/../../includes/settings-handler.php';

requireRole('client');


/**
 * ==========================================
 * HANDLE SETTINGS UPDATE
 * ==========================================
 */

$settingsMessage = '';
$settingsSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = handleSettingsRequest();

    $settingsSuccess = $result['success'];
    $settingsMessage = $result['message'];
}


/**
 * ==========================================
 * GET CURRENT SETTINGS
 * ==========================================
 */

$currentSettings = getUserSettings();

$currentTheme = $currentSettings['theme'];
$currentLanguage = $currentSettings['language'];

?>

<section class="settings-page">

    <!-- =====================================
         PAGE HEADER
         ====================================== -->

    <div class="settings-header">

        <div>
            <span class="settings-eyebrow">
                Account Preferences
            </span>

            <h1>Settings</h1>

            <p>
                Manage your FindPro account preferences.
            </p>
        </div>

    </div>


    <!-- =====================================
         FEEDBACK MESSAGE
         ====================================== -->

    <?php if ($settingsMessage): ?>

        <div class="settings-alert <?= $settingsSuccess ? 'success' : 'error' ?>">

            <i class="fa-solid <?= $settingsSuccess
                ? 'fa-circle-check'
                : 'fa-circle-exclamation'
            ?>"></i>

            <span>
                <?= htmlspecialchars($settingsMessage) ?>
            </span>

        </div>

    <?php endif; ?>


    <!-- =====================================
         ACCOUNT
         ====================================== -->

    <div class="settings-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-regular fa-user"></i>
            </div>

            <div>
                <h2>Account</h2>
                <p>
                    Manage your personal account information.
                </p>
            </div>

        </div>


        <div class="settings-list">

            <a href="dashboard.php?page=profile" class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-regular fa-id-card"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Profile Information</strong>
                    <span>
                        Update your personal information and profile.
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right settings-arrow"></i>

            </a>


            <a href="dashboard.php?page=profile" class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-regular fa-address-book"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Email & Phone</strong>
                    <span>
                        Manage your contact information.
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right settings-arrow"></i>

            </a>


            <a href="dashboard.php?page=profile" class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Change Password</strong>
                    <span>
                        Update your account password.
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right settings-arrow"></i>

            </a>

        </div>

    </div>


    <!-- =====================================
         NOTIFICATIONS
         ====================================== -->

    <div class="settings-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-regular fa-bell"></i>
            </div>

            <div>
                <h2>Notifications</h2>
                <p>
                    Control how FindPro keeps you informed.
                </p>
            </div>

        </div>


        <div class="settings-list">

            <div class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Booking Notifications</strong>
                    <span>
                        Receive updates about your bookings.
                    </span>
                </div>

                <label class="settings-switch">
                    <input type="checkbox" checked>
                    <span class="settings-slider"></span>
                </label>

            </div>


            <div class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-regular fa-comment"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Messages</strong>
                    <span>
                        Get notified when someone sends you a message.
                    </span>
                </div>

                <label class="settings-switch">
                    <input type="checkbox" checked>
                    <span class="settings-slider"></span>
                </label>

            </div>


            <div class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Service Updates</strong>
                    <span>
                        Receive important FindPro updates.
                    </span>
                </div>

                <label class="settings-switch">
                    <input type="checkbox" checked>
                    <span class="settings-slider"></span>
                </label>

            </div>

        </div>

    </div>


    <!-- =====================================
         PRIVACY
         ====================================== -->

    <div class="settings-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div>
                <h2>Privacy</h2>
                <p>
                    Control your profile and location visibility.
                </p>
            </div>

        </div>


        <div class="settings-list">

            <div class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-regular fa-eye"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Profile Visibility</strong>
                    <span>
                        Allow other users to find your profile.
                    </span>
                </div>

                <label class="settings-switch">
                    <input type="checkbox" checked>
                    <span class="settings-slider"></span>
                </label>

            </div>


            <div class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-location-dot"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Location Privacy</strong>
                    <span>
                        Control how your location is displayed.
                    </span>
                </div>

                <label class="settings-switch">
                    <input type="checkbox" checked>
                    <span class="settings-slider"></span>
                </label>

            </div>

        </div>

    </div>


    <!-- =====================================
         APPEARANCE
         ====================================== -->

    <div class="settings-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>

            <div>
                <h2>Appearance</h2>
                <p>
                    Customize how FindPro looks and feels.
                </p>
            </div>

        </div>


        <div class="settings-list">


            <!-- =================================
                 THEME
                 ================================== -->

            <div class="settings-item settings-preference">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </div>

                <div class="settings-item-content">

                    <strong>Theme</strong>

                    <span>
                        Choose between light and dark appearance.
                    </span>

                </div>


                <form method="POST" class="theme-selector">

                    <button
                        type="submit"
                        name="theme"
                        value="light"
                        class="theme-option <?= $currentTheme === 'light' ? 'active' : '' ?>"
                        aria-label="Light theme"
                    >
                        <i class="fa-regular fa-sun"></i>
                        <span>Light</span>
                    </button>


                    <button
                        type="submit"
                        name="theme"
                        value="dark"
                        class="theme-option <?= $currentTheme === 'dark' ? 'active' : '' ?>"
                        aria-label="Dark theme"
                    >
                        <i class="fa-regular fa-moon"></i>
                        <span>Dark</span>
                    </button>

                </form>

            </div>


            <!-- =================================
                 LANGUAGE
                 ================================== -->

            <div class="settings-item settings-preference">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-language"></i>
                </div>

                <div class="settings-item-content">

                    <strong>Language</strong>

                    <span>
                        Choose your preferred language.
                    </span>

                </div>


                <form method="POST" class="language-selector">

                    <button
                        type="submit"
                        name="language"
                        value="en"
                        class="language-option <?= $currentLanguage === 'en' ? 'active' : '' ?>"
                    >
                        English
                    </button>


                    <button
                        type="submit"
                        name="language"
                        value="sw"
                        class="language-option <?= $currentLanguage === 'sw' ? 'active' : '' ?>"
                    >
                        Kiswahili
                    </button>

                </form>

            </div>

        </div>

    </div>


    <!-- =====================================
         SECURITY
         ====================================== -->

    <div class="settings-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-solid fa-lock"></i>
            </div>

            <div>
                <h2>Security</h2>
                <p>
                    Keep your FindPro account secure.
                </p>
            </div>

        </div>


        <div class="settings-list">

            <a href="dashboard.php?page=profile" class="settings-item">

                <div class="settings-item-icon">
                    <i class="fa-solid fa-key"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Password</strong>
                    <span>
                        Change your account password.
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right settings-arrow"></i>

            </a>

        </div>

    </div>


    <!-- =====================================
         ACCOUNT ACTIONS
         ====================================== -->

    <div class="settings-section settings-danger-section">

        <div class="settings-section-header">

            <div class="settings-section-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>

            <div>
                <h2>Account Actions</h2>
                <p>
                    Manage your FindPro session.
                </p>
            </div>

        </div>


        <div class="settings-list">

            <a
                href="../auth/logout.php"
                class="settings-item settings-logout"
            >

                <div class="settings-item-icon">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>

                <div class="settings-item-content">
                    <strong>Logout</strong>
                    <span>
                        Sign out of your FindPro account.
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right settings-arrow"></i>

            </a>

        </div>

    </div>

</section>