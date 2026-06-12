<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: /eventix/" . userRole() . "/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($connect, $_POST['email']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $sql);
    $user   = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['full_name'];
        $_SESSION['role']    = $user['role'];

        $_SESSION['profile_picture'] = $user['profile_picture'] ?? null; // Added support for profile picture

        $redirect = $_GET['redirect'] ?? '';
        if ($redirect && strpos($redirect, 'venue.php') === 0) {
            if ($user['role'] === 'customer') {
                $venue_id = explode('id=', $redirect)[1] ?? 0;
                header("Location: /eventix/customer/book.php?id=" . (int)$venue_id);
            } else {
                header("Location: /eventix/" . $redirect);
            }
        } else {
            if ($user['role'] === 'admin')   header("Location: /eventix/admin/dashboard.php");
            if ($user['role'] === 'manager') header("Location: /eventix/manager/dashboard.php");
            if ($user['role'] === 'customer') header("Location: /eventix/customer/home.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Eventix</title>
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
                <h1>Every event<br>a memory.</h1>
                <p>Sign in to explore curated venues and manage your bookings with ease.</p>
            </div>
            <div class="auth-dots">
                <span class="dot"></span>
                <span class="dot active"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-form-wrap">
            <h1>Welcome<br>Back</h1>
            <p>Sign in to your account to continue.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-eye">
                        <input type="password" name="password" id="passInput" placeholder="••••••••" required>
                        <span onclick="togglePass()">👁</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Sign In →</button>
            </form>

            <p class="auth-switch">Don't have an account? <a href="/eventix/register.php">Register here</a></p>
            <a href="/eventix/login.php?role=admin" class="admin-portal-link">ADMIN PORTAL</a>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('passInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>