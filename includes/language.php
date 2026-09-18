<?php

/**
 * ==========================================
 * FINDPRO GLOBAL LANGUAGE SYSTEM
 * ==========================================
 *
 * Supported languages:
 * en = English
 * sw = Kiswahili
 *
 * This file manages the user's language
 * preference globally.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * ==========================================
 * SUPPORTED LANGUAGES
 * ==========================================
 */

function supportedLanguages(): array
{
    return [
        'en' => 'English',
        'sw' => 'Kiswahili',
    ];
}


/**
 * ==========================================
 * CHECK LANGUAGE
 * ==========================================
 */

function isSupportedLanguage(string $language): bool
{
    return array_key_exists($language, supportedLanguages());
}


/**
 * ==========================================
 * GET CURRENT LANGUAGE
 * ==========================================
 *
 * Priority:
 *
 * 1. Logged-in user's database preference
 * 2. Session
 * 3. Cookie
 * 4. Browser language
 * 5. English
 */

function getCurrentLanguage(): string
{
    static $language = null;

    if ($language !== null) {
        return $language;
    }


    /*
     * --------------------------------------
     * 1. Logged-in user's database setting
     * --------------------------------------
     */

    if (isset($_SESSION['user_id'])) {

        $userLanguage = getUserLanguageFromDatabase(
            (int) $_SESSION['user_id']
        );

        if (
            $userLanguage !== null &&
            isSupportedLanguage($userLanguage)
        ) {

            $language = $userLanguage;

            $_SESSION['language'] = $language;

            return $language;
        }
    }


    /*
     * --------------------------------------
     * 2. Session
     * --------------------------------------
     */

    if (
        isset($_SESSION['language']) &&
        isSupportedLanguage($_SESSION['language'])
    ) {

        $language = $_SESSION['language'];

        return $language;
    }


    /*
     * --------------------------------------
     * 3. Cookie
     * --------------------------------------
     */

    if (
        isset($_COOKIE['findpro_language']) &&
        isSupportedLanguage($_COOKIE['findpro_language'])
    ) {

        $language = $_COOKIE['findpro_language'];

        $_SESSION['language'] = $language;

        return $language;
    }


    /*
     * --------------------------------------
     * 4. Browser language
     * --------------------------------------
     */

    $browserLanguage = detectBrowserLanguage();

    if ($browserLanguage !== null) {

        $language = $browserLanguage;

        $_SESSION['language'] = $language;

        return $language;
    }


    /*
     * --------------------------------------
     * 5. Default
     * --------------------------------------
     */

    $language = 'en';

    return $language;
}


/**
 * ==========================================
 * GET USER LANGUAGE FROM DATABASE
 * ==========================================
 */

function getUserLanguageFromDatabase(int $userId): ?string
{
    global $pdo;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return null;
    }


    try {

        $stmt = $pdo->prepare(
            "SELECT language
             FROM users
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$userId]);

        $language = $stmt->fetchColumn();


        if (
            $language !== false &&
            isSupportedLanguage((string) $language)
        ) {

            return (string) $language;
        }

    } catch (PDOException $e) {

        /*
         * Do not break the entire application
         * if language preference cannot be loaded.
         */

        return null;
    }


    return null;
}


/**
 * ==========================================
 * SET LANGUAGE
 * ==========================================
 */

function setLanguage(string $language): bool
{
    $language = strtolower(trim($language));


    /*
     * Invalid language
     */

    if (!isSupportedLanguage($language)) {
        return false;
    }


    /*
     * Save to session
     */

    $_SESSION['language'] = $language;


    /*
     * Save to cookie
     */

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


    /*
     * Save to database
     * if the user is logged in.
     */

    if (isset($_SESSION['user_id'])) {

        $userId = (int) $_SESSION['user_id'];

        return updateUserLanguageInDatabase(
            $userId,
            $language
        );
    }


    return true;
}


/**
 * ==========================================
 * UPDATE USER LANGUAGE IN DATABASE
 * ==========================================
 */

function updateUserLanguageInDatabase(
    int $userId,
    string $language
): bool {

    global $pdo;


    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return false;
    }


    if (!isSupportedLanguage($language)) {
        return false;
    }


    try {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET language = ?
             WHERE id = ?
             LIMIT 1"
        );

        return $stmt->execute([
            $language,
            $userId
        ]);

    } catch (PDOException $e) {

        return false;
    }
}


/**
 * ==========================================
 * DETECT BROWSER LANGUAGE
 * ==========================================
 */

function detectBrowserLanguage(): ?string
{
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return null;
    }


    $languages = explode(
        ',',
        strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    );


    foreach ($languages as $language) {

        $language = trim(
            explode(';', $language)[0]
        );


        /*
         * Swahili
         */

        if (str_starts_with($language, 'sw')) {
            return 'sw';
        }


        /*
         * English
         */

        if (str_starts_with($language, 'en')) {
            return 'en';
        }
    }


    return null;
}


/**
 * ==========================================
 * GET AVAILABLE LANGUAGES
 * ==========================================
 */

function getAvailableLanguages(): array
{
    return supportedLanguages();
}


/**
 * ==========================================
 * GET CURRENT LANGUAGE NAME
 * ==========================================
 */

function getCurrentLanguageName(): string
{
    $languages = supportedLanguages();

    $currentLanguage = getCurrentLanguage();

    return $languages[$currentLanguage] ?? 'English';
}


/**
 * ==========================================
 * GET OTHER LANGUAGE
 * ==========================================
 */

function getOtherLanguage(): string
{
    return getCurrentLanguage() === 'en'
        ? 'sw'
        : 'en';
}


/**
 * ==========================================
 * LANGUAGE HTML ATTRIBUTE
 * ==========================================
 *
 * Example:
 *
 * <html lang="sw">
 *
 */

function getLanguageHtmlAttribute(): string
{
    return htmlspecialchars(
        getCurrentLanguage(),
        ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * ==========================================
 * GLOBAL LANGUAGE JAVASCRIPT
 * ==========================================
 *
 * Makes the current language available
 * to the frontend.
 */

function renderLanguageScript(): void
{
    $language = getCurrentLanguage();

    echo '<script>';
    echo 'window.FindProLanguage = ' .
        json_encode(
            $language,
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        );
    echo ';</script>';
}
