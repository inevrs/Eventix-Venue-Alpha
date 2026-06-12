<?php
session_start();
require_once 'includes/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($connect, $_POST['full_name']);
    $email    = mysqli_real_escape_string($connect, $_POST['email']);
    $phone    = mysqli_real_escape_string($connect, $_POST['phone']);
    $role     = mysqli_real_escape_string($connect, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($connect, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already registered.";
    } else {
        $sql = "INSERT INTO users (full_name, email, phone, password, role) VALUES ('$name','$email','$phone','$password','$role')";
        if (mysqli_query($connect, $sql)) {
            $success = "Account created! <a href='/eventix/login.php'>Sign in here</a>.";
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
    <link rel="stylesheet" href="/eventix/css/auth.css">
</head>
<body class="auth-body">

<div class="auth-split">
    <div class="auth-left">
        <div class="auth-left-inner">
            <div class="auth-logo">
                <img src="/eventix/images/eventix_logo.jpg" alt="Eventix Logo" style="width: 120px; height: auto; margin-bottom: 10px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <h2>Eventix</h2>
                <span>EVENT MANAGEMENT SYSTEM</span>
            </div>
            <div class="auth-tagline">
                <h1>Join us<br>today.</h1>
                <p>Create your account and start discovering extraordinary event venues.</p>
            </div>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-form-wrap" style="width:420px">
            <h1>Create<br>Account</h1>
            <p>Fill in your details to get started.</p>

            <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="+60 12-345 6789">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 8 characters" required>
                </div>
                <div class="form-group">
                    <label>Register As</label>
                    <select name="role">
                        <option value="customer">Customer</option>
                        <option value="manager">Venue Manager</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="/eventix/login.php">Sign in</a></p>
        </div>
    </div>
</div>

</body>
</html>
