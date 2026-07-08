<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/functions.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($role)) {
        $message = "All fields are required!";
    } else {

        // Check duplicate
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);

        if ($stmt->rowCount() > 0) {
            $message = "User already exists!";
        } else {

            $hashedPassword = hashPassword($password);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, phone, password, role)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $full_name,
                $email,
                $phone,
                $hashedPassword,
                $role
            ]);

            $user_id = $pdo->lastInsertId();

            if ($role === "provider") {
                $stmt = $pdo->prepare("
                    INSERT INTO provider_profiles (user_id)
                    VALUES (?)
                ");
                $stmt->execute([$user_id]);
            }

            if ($role === "client") {
                $stmt = $pdo->prepare("
                    INSERT INTO client_profiles (user_id)
                    VALUES (?)
                ");
                $stmt->execute([$user_id]);
            }

            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Services Finder</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

<div class="register-container">
    <div class="register-box">
        <h2>Create Account</h2>

        <?php if ($message): ?>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-box">
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>

            <div class="input-box">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-box">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="input-box">
                <select name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="client">Client</option>
                    <option value="provider">Provider</option>
                </select>
            </div>

            <button class="btn-submit" type="submit">Create Account</button>
        </form>

        <div class="bottom-text">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</div>

</body>
</html>