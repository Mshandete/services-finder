<?php
session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Redirect if already logged in
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            exit();
        case 'provider':
            header("Location: ../provider/dashboard.php");
            exit();
        default:
            header("Location: ../client/dashboard.php");
            exit();
    }
}

/*
| Variables
*/
$login = "";
$errors = [];
$success = "";

/*
| Success Message from Registration
*/
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

/*
| Login
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($login)) {
        $errors[] = "Please enter your email or phone number.";
    }

    if (empty($password)) {
        $errors[] = "Please enter your password.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = ?
               OR phone = ?
            LIMIT 1
        ");

        $stmt->execute([$login, $login]);

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tumesawazisha ujumbe wa makosa hapa kulinda faragha ya akaunti
            if (!password_verify($password, $user['password'])) {
                $errors[] = "Invalid email/phone or password.";
            } else {

                if ($user['account_status'] != 'active') {
                    $errors[] = "Your account is currently inactive.";
                } else {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['phone'] = $user['phone'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fingerprint'] = hash(
                        'sha256',
                        ($_SERVER['REMOTE_ADDR'] ?? '') .
                        ($_SERVER['HTTP_USER_AGENT'] ?? '')
                    );                    

                    $update = $pdo->prepare("
                        UPDATE users
                        SET last_login = NOW()
                        WHERE id = ?
                    ");
                    $update->execute([$user['id']]);

                    /*
                    |--------------------------------------------------------------------------
                    | Remember Me (Imeboreshwa Kiusalama)
                    |--------------------------------------------------------------------------
                    */
                    if ($remember) {
                        $rawToken = bin2hex(random_bytes(32));
                        // Tunahifadhi hash kwenye DB, sio plain text!
                        $hashedToken = hash('sha256', $rawToken);

                        $rememberStmt = $pdo->prepare("
                            UPDATE users
                            SET remember_token = ?
                            WHERE id = ?
                        ");
                        $rememberStmt->execute([
                            $hashedToken,
                            $user['id']
                        ]);

                        // Tumeseti 'secure' kuwa true (kama unatumia HTTPS, badili kuwa true kwenye live server)
                        // Kwa sasa nimeacha false kama bado upo localhost, lakini weka true kwenye live server
                        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

                        setcookie(
                            "remember_token",
                            $rawToken, // Kwenye browser inaenda raw token
                            time() + (86400 * 30),
                            "/",
                            "",
                            $isSecure, // Inakuwa true kukiwa na HTTPS pekee
                            true // HttpOnly - inazuia JavaScript kuisoma
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Redirect by Role
                    |--------------------------------------------------------------------------
                    */
                    if ($user['role'] == "admin") {
                        header("Location: ../admin/dashboard.php");
                        exit();
                    }
                    if ($user['role'] == "provider") {
                        header("Location: ../provider/dashboard.php");
                        exit();
                    }

                    header("Location: ../client/dashboard.php");
                    exit();
                }
            }
        } else {
            // Tumesawazisha ujumbe wa makosa hapa pia
            $errors[] = "Invalid email/phone or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FindPro</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="logo">
            <h1>Find<span>Pro</span></h1>
            <p>Welcome Back</p>
        </div>

        <?php if(!empty($success)): ?>
            <div class="alert success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert error">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="login-form">
            <div class="form-group">
                <label for="login">
                    <i class="fas fa-user"></i>
                    Email or Phone
                </label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    placeholder="Enter your email or phone"
                    value="<?= htmlspecialchars($login) ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <div class="password-box">
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="options">
                <label class="remember">
                    <input type="checkbox" name="remember"> Remember Me
                </label>
                <a href="forgot-password.php">Forgot Password?</a>
            </div>

            <button class="login-btn" type="submit">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    const password = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");

    if(password.type === "password"){
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>