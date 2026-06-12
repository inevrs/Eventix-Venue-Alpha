<?php
function requireLogin($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /eventix/login.php");
        exit();
    }
    if ($role && $_SESSION['role'] !== $role) {
        header("Location: /eventix/login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function userRole() {
    return $_SESSION['role'] ?? null;
}

function userName() {
    return $_SESSION['name'] ?? 'User';
}

function userInitial() {
    return strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1));
}
?>
