<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('manager');

$manager_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$venue = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM venues WHERE id=$id AND manager_id=$manager_id"));
if (!$venue) { header("Location: venues.php"); exit(); }

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($connect, $_POST['name']);
    $location = mysqli_real_escape_string($connect, $_POST['location']);
    $capacity = (int)$_POST['capacity'];
    $price    = (float)$_POST['price_per_day'];
    $desc     = mysqli_real_escape_string($connect, $_POST['description']);
    $status   = mysqli_real_escape_string($connect, $_POST['status']);

    mysqli_query($connect, "UPDATE venues SET name='$name', location='$location', capacity=$capacity, price_per_day=$price, description='$desc', status='$status' WHERE id=$id AND manager_id=$manager_id");

    $upload_dir = '../uploads/venues/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // Replace thumbnail
    if (!empty($_FILES['thumbnail']['name'])) {
        $old = mysqli_fetch_assoc(mysqli_query($connect, "SELECT image_path FROM venue_images WHERE venue_id=$id AND is_thumbnail=1"));
        if ($old && file_exists('../' . $old['image_path'])) unlink('../' . $old['image_path']);
        mysqli_query($connect, "DELETE FROM venue_images WHERE venue_id=$id AND is_thumbnail=1");

        $ext      = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = 'venue_' . $id . '_thumb_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_dir . $filename)) {
            $path = mysqli_real_escape_string($connect, 'uploads/venues/' . $filename);
            mysqli_query($connect, "INSERT INTO venue_images (venue_id, image_path, is_thumbnail, sort_order) VALUES ($id, '$path', 1, 0)");
        }
    }

    // Add more gallery images
    if (!empty($_FILES['gallery']['name'][0])) {
        $max_order = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(MAX(sort_order),0) FROM venue_images WHERE venue_id=$id AND is_thumbnail=0"))[0];
        foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
            if ($_FILES['gallery']['error'][$i] !== 0) continue;
            $ext      = pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION);
            $filename = 'venue_' . $id . '_gallery_' . time() . '_' . $i . '.' . $ext;
            if (move_uploaded_file($tmp, $upload_dir . $filename)) {
                $path  = mysqli_real_escape_string($connect, 'uploads/venues/' . $filename);
                $order = $max_order + $i + 1;
                mysqli_query($connect, "INSERT INTO venue_images (venue_id, image_path, is_thumbnail, sort_order) VALUES ($id, '$path', 0, $order)");
            }
        }
    }

    // Delete individual gallery image
    if (isset($_POST['delete_image_id'])) {
        $img_id = (int)$_POST['delete_image_id'];
        $img = mysqli_fetch_assoc(mysqli_query($connect, "SELECT image_path FROM venue_images WHERE id=$img_id AND venue_id=$id"));
        if ($img && file_exists('../' . $img['image_path'])) unlink('../' . $img['image_path']);
        mysqli_query($connect, "DELETE FROM venue_images WHERE id=$img_id AND venue_id=$id");
    }

    $success = "Venue updated.";
    $venue = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM venues WHERE id=$id"));
}

$gallery = mysqli_query($connect, "SELECT * FROM venue_images WHERE venue_id=$id AND is_thumbnail=0 ORDER BY sort_order");
$thumb   = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM venue_images WHERE venue_id=$id AND is_thumbnail=1"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Venue — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="layout-sidebar">
    <aside class="sidebar">
        <p class="sidebar-section">Overview</p>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
        </ul>
        <p class="sidebar-section">My Business</p>
        <ul class="sidebar-menu">
            <li><a href="venues.php" class="active">🏛️ My Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="earnings.php">💰 Earnings</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Edit Venue</h1>
        <div style="margin-bottom: 24px;">
            <a href="venues.php" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>←</span> Back to My Venues
            </a>
        </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Venue Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($venue['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($venue['location']) ?>" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label>Capacity (pax)</label>
                            <input type="number" name="capacity" value="<?= $venue['capacity'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Price per Day (RM)</label>
                            <input type="number" name="price_per_day" step="0.01" value="<?= $venue['price_per_day'] ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?= htmlspecialchars($venue['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active"   <?= $venue['status']==='active'   ?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $venue['status']==='inactive' ?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Replace Thumbnail</label>
                        <?php if ($thumb): ?>
                            <img src="/eventix/<?= htmlspecialchars($thumb['image_path']) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:8px">
                        <?php endif; ?>
                        <input type="file" name="thumbnail" accept="image/*" style="padding:8px">
                    </div>
                    <div class="form-group">
                        <label>Add Gallery Pictures</label>
                        <input type="file" name="gallery[]" accept="image/*" multiple style="padding:8px">
                        <small style="color:var(--text-muted);font-size:12px">Adds to existing gallery</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Gallery management -->
            <div class="card">
                <h2 class="section-title">Gallery Images</h2>
                <?php
                $gallery_rows = [];
                while ($g = mysqli_fetch_assoc($gallery)) $gallery_rows[] = $g;
                if (empty($gallery_rows)): ?>
                    <p style="color:var(--text-muted);font-size:14px">No gallery images yet.</p>
                <?php else: ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <?php foreach ($gallery_rows as $g): ?>
                        <div style="position:relative">
                            <img src="/eventix/<?= htmlspecialchars($g['image_path']) ?>" style="width:100%;height:100px;object-fit:cover;border-radius:8px">
                            <form method="POST" style="position:absolute;top:6px;right:6px" onsubmit="return confirm('Delete this image?')">
                                <input type="hidden" name="delete_image_id" value="<?= $g['id'] ?>">
                                <button type="submit" style="background:rgba(200,0,0,0.8);color:white;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:14px;line-height:1">×</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
