<?php
declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| Clear Remember Token
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {

    require_once "../config/database.php";

    $stmt = $pdo->prepare("
        UPDATE users
        SET remember_token = NULL
        WHERE id = ?
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);
}


/*
|--------------------------------------------------------------------------
| Remove Remember Cookie
|--------------------------------------------------------------------------
*/

if (isset($_COOKIE['remember_token'])) {

    setcookie(
        'remember_token',
        '',
        time() - 3600,
        '/',
        '',
        false,
        true
    );
}


/*
|--------------------------------------------------------------------------
| Clear Session
|--------------------------------------------------------------------------
*/

$_SESSION = [];


/*
|--------------------------------------------------------------------------
| Destroy Session Cookie
|--------------------------------------------------------------------------
*/

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


/*
|--------------------------------------------------------------------------
| Destroy Session
|--------------------------------------------------------------------------
*/

session_destroy();


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header("Location: login.php");
exit();