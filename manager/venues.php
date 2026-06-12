<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('manager');

$manager_id = $_SESSION['user_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name     = mysqli_real_escape_string($connect, $_POST['name']);
    $location = mysqli_real_escape_string($connect, $_POST['location']);
    $capacity = (int)$_POST['capacity'];
    $price    = (float)$_POST['price_per_day'];
    $desc     = mysqli_real_escape_string($connect, $_POST['description']);

    $sql = "INSERT INTO venues (name, location, capacity, price_per_day, description, manager_id, status)
            VALUES ('$name','$location',$capacity,$price,'$desc',$manager_id,'active')";

    if (mysqli_query($connect, $sql)) {
        $venue_id = mysqli_insert_id($connect);

        $upload_dir = '../uploads/venues/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // Thumbnail
        if (!empty($_FILES['thumbnail']['name'])) {
            $ext      = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $filename = 'venue_' . $venue_id . '_thumb_' . time() . '.' . $ext;
            $dest     = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dest)) {
                $path = mysqli_real_escape_string($connect, 'uploads/venues/' . $filename);
                mysqli_query($connect, "INSERT INTO venue_images (venue_id, image_path, is_thumbnail, sort_order) VALUES ($venue_id, '$path', 1, 0)");
            }
        }

        // Gallery images
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $i => $tmp) {
                if ($_FILES['gallery']['error'][$i] !== 0) continue;
                $ext      = pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION);
                $filename = 'venue_' . $venue_id . '_gallery_' . time() . '_' . $i . '.' . $ext;
                $dest     = $upload_dir . $filename;
                if (move_uploaded_file($tmp, $dest)) {
                    $path = mysqli_real_escape_string($connect, 'uploads/venues/' . $filename);
                    mysqli_query($connect, "INSERT INTO venue_images (venue_id, image_path, is_thumbnail, sort_order) VALUES ($venue_id, '$path', 0, $i)");
                }
            }
        }

        $success = "Venue added successfully.";
    } else {
        $error = "Failed to add venue.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $images = mysqli_query($connect, "SELECT image_path FROM venue_images WHERE venue_id=$id");
    while ($img = mysqli_fetch_assoc($images)) {
        $file = '../' . $img['image_path'];
        if (file_exists($file)) unlink($file);
    }
    mysqli_query($connect, "DELETE FROM venues WHERE id=$id AND manager_id=$manager_id");
    $success = "Venue deleted.";
}

$venues = mysqli_query($connect, "
    SELECT v.*, vi.image_path AS thumbnail
    FROM venues v
    LEFT JOIN venue_images vi ON v.id=vi.venue_id AND vi.is_thumbnail=1
    WHERE v.manager_id=$manager_id
    ORDER BY v.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Venues — Eventix</title>
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
        <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <h1>My Venues</h1>
                <p>Manage your listed spaces</p>
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ Add Venue</button>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Price/Day</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($venues)): ?>
                        <tr>
                            <td>
                                <?php if ($row['thumbnail']): ?>
                                    <img src="/eventix/<?= htmlspecialchars($row['thumbnail']) ?>" style="width:60px;height:45px;object-fit:cover;border-radius:6px">
                                <?php else: ?>
                                    <div style="width:60px;height:45px;background:var(--pink-light);border-radius:6px;display:flex;align-items:center;justify-content:center">🏛️</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= $row['capacity'] ?> pax</td>
                            <td>RM<?= number_format($row['price_per_day'], 2) ?></td>
                            <td><span class="badge badge-<?= $row['status']==='active'?'success':'warning' ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td style="display:flex;gap:8px">
                                <a href="edit_venue.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" onsubmit="return confirm('Delete venue?')">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Venue Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal" style="width:560px;max-height:90vh;overflow-y:auto">
        <h2>Add New Venue</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Venue Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Capacity (pax)</label>
                    <input type="number" name="capacity" required>
                </div>
                <div class="form-group">
                    <label>Price per Day (RM)</label>
                    <input type="number" name="price_per_day" step="0.01" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Thumbnail Picture</label>
                <input type="file" name="thumbnail" accept="image/*" style="padding:8px">
                <small style="color:var(--text-muted);font-size:12px">This appears as the card preview image</small>
            </div>
            <div class="form-group">
                <label>Gallery Pictures</label>
                <input type="file" name="gallery[]" accept="image/*" multiple style="padding:8px">
                <small style="color:var(--text-muted);font-size:12px">Select multiple — shown in venue detail page</small>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Venue</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
