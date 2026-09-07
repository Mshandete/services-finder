<?php
session_start();

require_once "../config/database.php";

/* Initialize Variables */

$full_name = "";
$email = "";
$phone = "";
$role = "";

$errors = [];
$success = "";

/* Register User */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    /*  Validation */

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (strlen($full_name) < 3) {
        $errors[] = "Full name must contain at least 3 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $allowed_roles = ['client', 'provider'];

    if (!in_array($role, $allowed_roles)) {
        $errors[] = "Invalid account type selected.";
    }

    /* Check Existing Email */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists.";
        }

    }

    /* Check Existing Phone */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE phone = ?
            LIMIT 1
        ");

        $stmt->execute([$phone]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Phone number already exists.";
        }

    }

/* Create Account */

if (empty($errors)) {

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    try {

        /*
        |--------------------------------------------------------------------------
        | Start Transaction
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO users
            (
                full_name,
                email,
                phone,
                password,
                role
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $full_name,
            $email,
            $phone,
            $hashedPassword,
            $role
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get Newly Created User ID
        |--------------------------------------------------------------------------
        */

        $userId = (int)$pdo->lastInsertId();

        /*
        |--------------------------------------------------------------------------
        | Create Provider Profile Automatically
        |--------------------------------------------------------------------------
        */

        if ($role === 'provider') {

            $profileStmt = $pdo->prepare("
                INSERT INTO provider_profiles
                (
                    user_id
                )
                VALUES
                (
                    ?
                )
            ");

            $profileStmt->execute([
                $userId
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Commit Transaction
        |--------------------------------------------------------------------------
        */

        $pdo->commit();

        /*
        |--------------------------------------------------------------------------
        | Registration Success
        |--------------------------------------------------------------------------
        */

        $_SESSION['success_message'] =
            "Registration successful. Please login.";

        header("Location: login.php");
        exit();


    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback If Something Fails
        |--------------------------------------------------------------------------
        */

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $errors[] =
            "Registration failed. Please try again.";
    }
}

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | FindPro</title>
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

    <div class="register-container">

        <div class="register-card">

        <div class="logo">
        <h1>Find<span>Pro</span></h1>
        <p>Create your account</p>

        </div>

        <?php if(!empty($errors)): ?>

        <div class="alert error">

        <ul>
        <?php foreach($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>

<?php endif; ?>

<form action="" method="POST" class="register-form">
    <div class="form-group">
        <label for="full_name">
            <i class="fas fa-user"></i> Full Name
        </label>
        <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?= htmlspecialchars($full_name) ?>" required>
    </div>

    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope"></i> Email Address
        </label>
        <input type="email" id="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($email) ?>" required>
    </div>


    <div class="form-group">
        <label for="phone">
            <i class="fas fa-phone"></i> Phone Number
        </label>
        <input type="text" id="phone" name="phone" placeholder="07XXXXXXXX" value="<?= htmlspecialchars($phone) ?>" required>
    </div>

    <div class="form-group">
        <label for="role">
            <i class="fas fa-users"></i>
            Account Type
        </label>

        <select id="role" name="role" required>
            <option value="">Select Account Type</option>
            <option value="client"
                <?= ($role=="client") ? "selected" : "" ?>>
                Client
            </option>

            <option value="provider"
                <?= ($role=="provider") ? "selected" : "" ?>>
                Service Provider
            </option>
        </select>
    </div>

    <div class="form-group">
        <label for="password">
            <i class="fas fa-lock"></i>
            Password
        </label>

        <div class="password-box">
            <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('password',this)"></i>
        </div>
    </div>

    <div class="form-group">
        <label for="confirm_password">
            <i class="fas fa-lock"></i>
            Confirm Password
        </label>

        <div class="password-box">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password',this)"></i>
        </div>
    </div>

    <button type="submit" class="register-btn">
        <i class="fas fa-user-plus"></i>
        Create Account
    </button>
</form>

<div class="login-link">
    Already have an account?
    <a href="login.php">Login Here</a>
</div>

</div>

</div>

<script>
function togglePassword(id, icon)
{
    let input = document.getElementById(id);
    if(input.type==="password")
    {
        input.type="text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
    else
    {
        input.type="password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

</script>
</body>

</html>

