<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit();
}

$email    = mysqli_real_escape_string($connect, $_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Please provide email and password.']);
    exit();
}

$sql    = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($connect, $sql);
$user   = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['full_name'];
    $_SESSION['role']    = $user['role'];
    $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;

    $redirect = '';
    if ($user['role'] === 'admin')   $redirect = '/eventix/admin/dashboard.php';
    if ($user['role'] === 'manager') $redirect = '/eventix/manager/dashboard.php';
    if ($user['role'] === 'customer') $redirect = '/eventix/index.php'; // Will refresh the page essentially

    echo json_encode(['success' => true, 'redirect' => $redirect, 'role' => $user['role']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
}
?>
