<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FindPro Authentication Middleware
|--------------------------------------------------------------------------
|
| This file protects all authenticated pages.
| Include it at the top of every protected page.
|
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Authentication Check
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['user_id'])) {

    $_SESSION['error_message'] = "Please login to continue.";

    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Session Fingerprint
|--------------------------------------------------------------------------
|
| Prevent basic session hijacking.
|
*/

$currentFingerprint = hash(
    'sha256',
    ($_SERVER['REMOTE_ADDR'] ?? '') .
    ($_SERVER['HTTP_USER_AGENT'] ?? '')
);

if (!isset($_SESSION['fingerprint'])) {

    $_SESSION['fingerprint'] = $currentFingerprint;

} elseif ($_SESSION['fingerprint'] !== $currentFingerprint) {

    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Load Fresh User Data
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        email,
        phone,
        role,
        account_status,
        profile_completed
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION['user_id']
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| User Exists?
|--------------------------------------------------------------------------
*/

if (!$user) {

    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Account Status
|--------------------------------------------------------------------------
*/

if ($user['account_status'] !== 'active') {

    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Refresh Session Data
|--------------------------------------------------------------------------
*/

$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['phone'] = $user['phone'];
$_SESSION['role'] = $user['role'];
$_SESSION['profile_completed'] = (int)$user['profile_completed'];

/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/

function requireRole(string $role): void
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {

        header("Location: ../auth/login.php");
        exit();
    }
}