<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($connect, "DELETE FROM venues WHERE id = $id");
}

$venues = mysqli_query($connect, "
    SELECT v.*, u.full_name AS manager_name
    FROM venues v
    LEFT JOIN users u ON v.manager_id = u.id
    ORDER BY v.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Venues — Eventix</title>
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
        <p class="sidebar-section">Manage</p>
        <ul class="sidebar-menu">
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="venues.php" class="active">🏛️ Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="payments.php">💳 Payments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>All Venues</h1>
            <p>Venues listed across the platform</p>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Venue Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Price/Day</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($venues)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= $row['capacity'] ?> pax</td>
                            <td>RM<?= number_format($row['price_per_day'], 2) ?></td>
                            <td><?= htmlspecialchars($row['manager_name'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] === 'active' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this venue?')">
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

</body>
</html>
