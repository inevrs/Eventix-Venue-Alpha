<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = mysqli_real_escape_string($connect, $_POST['bio']);
    
    // Handle file upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profiles/';
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
    
    if (!$error) {
        if ($profile_picture) {
            $sql = "UPDATE users SET bio='$bio', profile_picture='$profile_picture' WHERE id=$user_id";
            $_SESSION['profile_picture'] = $profile_picture;
        } else {
            $sql = "UPDATE users SET bio='$bio' WHERE id=$user_id";
        }
        
        if (mysqli_query($connect, $sql)) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Database error. Please try again.";
        }
    }
}

// Fetch current user data
$query = mysqli_query($connect, "SELECT * FROM users WHERE id=$user_id");
$user = mysqli_fetch_assoc($query);

// Sync session with DB (in case they logged in before the column was added)
if (!isset($_SESSION['profile_picture'])) {
    $_SESSION['profile_picture'] = $user['profile_picture'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your public profile and details</p>
    </div>

    <div class="profile-wrap">
        <div class="profile-avatar-sec">
            <div class="profile-avatar-large">
                <?php if ($user['profile_picture']): ?>
                    <img src="/eventix/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Avatar">
                <?php else: ?>
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <h3 style="font-size: 20px; margin-bottom: 4px;"><?= htmlspecialchars($user['full_name']) ?></h3>
            <p style="color: var(--text-muted); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;"><?= htmlspecialchars($user['role']) ?></p>
        </div>
        
        <div class="profile-details-sec card">
            <h2 class="section-title" style="margin-bottom: 24px; font-size: 20px;">Edit Details</h2>
            
            <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" style="padding: 10px;">
                    <small style="color: var(--text-muted); display: block; margin-top: 6px;">Leave blank if you don't want to change it.</small>
                </div>
                
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="5" placeholder="Tell us a little about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: var(--gray-bg); color: var(--text-muted); cursor: not-allowed;">
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
