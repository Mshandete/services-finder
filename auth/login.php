<?php

require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/functions.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && verifyPassword($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];

        if ($user['role'] === 'provider') {
            header("Location: ../provider/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;

    } else {
        $message = "Invalid email or password!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Services Finder</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        }

        .login-box {
            width: 380px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .input-box {
            margin-bottom: 15px;
        }

        .input-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: #2c5364;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #2c5364;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #1b2b34;
        }

        .error {
            text-align: center;
            color: red;
            margin-bottom: 10px;
        }

        .bottom-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .bottom-text a {
            color: #2c5364;
            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h2>Login</h2>

    <?php if ($message): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button class="btn" type="submit">Login</button>

    </form>

    <div class="bottom-text">
        Don't have an account? <a href="register.php">Register</a>
    </div>

</div>

</body>
</html>