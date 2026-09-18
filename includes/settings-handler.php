<?php

/**
 * ==========================================
 * FINDPRO GLOBAL SETTINGS HANDLER
 * ==========================================
 *
 * Handles user preferences such as:
 *
 * - Theme
 * - Language
 *
 * Database:
 * users.theme
 * users.language
 */


/**
 * ==========================================
 * REQUIRE DATABASE
 * ==========================================
 */

if (!isset($pdo) || !($pdo instanceof PDO)) {

    $databaseFile = __DIR__ . '/../config/database.php';

    if (file_exists($databaseFile)) {
        require_once $databaseFile;
    }
}


/**
 * ==========================================
 * REQUIRE SESSION
 * ==========================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * ==========================================
 * CHECK LOGGED-IN USER
 * ==========================================
 */

function settingsUserId(): ?int
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = (int) $_SESSION['user_id'];

    return $userId > 0 ? $userId : null;
}


/**
 * ==========================================
 * GET USER SETTINGS
 * ==========================================
 */

function getUserSettings(): array
{
    global $pdo;

    $userId = settingsUserId();

    if ($userId === null) {
        return [
            'theme' => 'light',
            'language' => 'en'
        ];
    }


    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return [
            'theme' => 'light',
            'language' => 'en'
        ];
    }


    try {

        $stmt = $pdo->prepare(
            "SELECT theme, language
             FROM users
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$userId]);

        $settings = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$settings) {
            return [
                'theme' => 'light',
                'language' => 'en'
            ];
        }


        return [
            'theme' => (
                in_array(
                    $settings['theme'],
                    ['light', 'dark'],
                    true
                )
            )
                ? $settings['theme']
                : 'light',

            'language' => (
                in_array(
                    $settings['language'],
                    ['en', 'sw'],
                    true
                )
            )
                ? $settings['language']
                : 'en'
        ];

    } catch (PDOException $e) {

        return [
            'theme' => 'light',
            'language' => 'en'
        ];
    }
}


/**
 * ==========================================
 * GET USER THEME
 * ==========================================
 */

function getUserTheme(): string
{
    $settings = getUserSettings();

    return $settings['theme'];
}


/**
 * ==========================================
 * SET USER THEME
 * ==========================================
 */

function setUserTheme(string $theme): bool
{
    global $pdo;

    $userId = settingsUserId();

    if ($userId === null) {
        return false;
    }


    if (!in_array($theme, ['light', 'dark'], true)) {
        return false;
    }


    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return false;
    }


    try {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET theme = ?
             WHERE id = ?
             LIMIT 1"
        );

        $success = $stmt->execute([
            $theme,
            $userId
        ]);


        if ($success) {

            /*
             * Keep the theme immediately available
             * during the current session.
             */

            $_SESSION['theme'] = $theme;
        }


        return $success;

    } catch (PDOException $e) {

        return false;
    }
}


/**
 * ==========================================
 * GET CURRENT THEME
 * ==========================================
 */

function getCurrentTheme(): string
{
    /*
     * Session is faster than querying the database
     * every time.
     */

    if (
        isset($_SESSION['theme']) &&
        in_array(
            $_SESSION['theme'],
            ['light', 'dark'],
            true
        )
    ) {
        return $_SESSION['theme'];
    }


    /*
     * Load from database.
     */

    $theme = getUserTheme();

    $_SESSION['theme'] = $theme;

    return $theme;
}


/**
 * ==========================================
 * SET USER LANGUAGE
 * ==========================================
 *
 * This function works together with
 * includes/language.php.
 */

function saveUserLanguage(string $language): bool
{
    global $pdo;

    $userId = settingsUserId();

    if ($userId === null) {
        return false;
    }


    if (!in_array($language, ['en', 'sw'], true)) {
        return false;
    }


    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return false;
    }


    try {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET language = ?
             WHERE id = ?
             LIMIT 1"
        );

        $success = $stmt->execute([
            $language,
            $userId
        ]);


        if ($success) {

            $_SESSION['language'] = $language;

            setcookie(
                'findpro_language',
                $language,
                [
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => (
                        !empty($_SERVER['HTTPS']) &&
                        $_SERVER['HTTPS'] !== 'off'
                    ),
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]
            );
        }


        return $success;

    } catch (PDOException $e) {

        return false;
    }
}


/**
 * ==========================================
 * SAVE COMPLETE SETTINGS
 * ==========================================
 *
 * Allows multiple settings to be updated
 * in one request.
 *
 * Example:
 *
 * saveUserSettings([
 *     'theme' => 'dark',
 *     'language' => 'sw'
 * ]);
 */

function saveUserSettings(array $settings): bool
{
    global $pdo;

    $userId = settingsUserId();

    if ($userId === null) {
        return false;
    }


    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return false;
    }


    $theme = $settings['theme'] ?? null;
    $language = $settings['language'] ?? null;


    /*
     * Validate theme if supplied.
     */

    if (
        $theme !== null &&
        !in_array($theme, ['light', 'dark'], true)
    ) {
        return false;
    }


    /*
     * Validate language if supplied.
     */

    if (
        $language !== null &&
        !in_array($language, ['en', 'sw'], true)
    ) {
        return false;
    }


    /*
     * Nothing to update.
     */

    if ($theme === null && $language === null) {
        return false;
    }


    try {

        $pdo->beginTransaction();


        /*
         * Update theme.
         */

        if ($theme !== null) {

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET theme = ?
                 WHERE id = ?
                 LIMIT 1"
            );

            $stmt->execute([
                $theme,
                $userId
            ]);

            $_SESSION['theme'] = $theme;
        }


        /*
         * Update language.
         */

        if ($language !== null) {

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET language = ?
                 WHERE id = ?
                 LIMIT 1"
            );

            $stmt->execute([
                $language,
                $userId
            ]);

            $_SESSION['language'] = $language;
        }


        $pdo->commit();


        /*
         * Keep language preference in cookie.
         */

        if ($language !== null) {

            setcookie(
                'findpro_language',
                $language,
                [
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => (
                        !empty($_SERVER['HTTPS']) &&
                        $_SERVER['HTTPS'] !== 'off'
                    ),
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]
            );
        }


        return true;

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return false;
    }
}


/**
 * ==========================================
 * APPLY SETTINGS FROM POST
 * ==========================================
 *
 * Useful for settings.php forms.
 *
 * Returns:
 *
 * [
 *     'success' => true/false,
 *     'message' => '...'
 * ]
 */

function handleSettingsRequest(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        return [
            'success' => false,
            'message' => 'Invalid request.'
        ];
    }


    if (settingsUserId() === null) {

        return [
            'success' => false,
            'message' => 'You must be logged in.'
        ];
    }


    $settings = [];


    /*
     * Theme
     */

    if (isset($_POST['theme'])) {
        $settings['theme'] = trim(
            (string) $_POST['theme']
        );
    }


    /*
     * Language
     */

    if (isset($_POST['language'])) {
        $settings['language'] = trim(
            (string) $_POST['language']
        );
    }


    if (empty($settings)) {

        return [
            'success' => false,
            'message' => 'No settings were provided.'
        ];
    }


    $success = saveUserSettings($settings);


    return [
        'success' => $success,
        'message' => $success
            ? 'Settings updated successfully.'
            : 'Unable to update settings.'
    ];
}
