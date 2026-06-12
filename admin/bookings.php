<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['booking_id'])) {
    $id     = (int)$_POST['booking_id'];
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    mysqli_query($connect, "UPDATE bookings SET status='$status' WHERE id=$id");
}

$bookings = mysqli_query($connect, "
    SELECT b.*, u.full_name, v.name AS venue_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN venues v ON b.venue_id = v.id
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings — Eventix</title>
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
            <li><a href="venues.php">🏛️ Venues</a></li>
            <li><a href="bookings.php" class="active">📅 Bookings</a></li>
            <li><a href="payments.php">💳 Payments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>All Bookings</h1>
            <p>Review and manage all venue bookings</p>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Event Date</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($bookings)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td><?= date('d M Y', strtotime($row['start_date'])) ?> to <?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <td><?= $row['guest_count'] ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] === 'confirmed' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:flex;gap:6px">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <select name="status" style="padding:6px 10px;border:1.5px solid var(--pink-light);border-radius:8px;font-size:13px;">
                                        <option value="pending"   <?= $row['status']==='pending'   ? 'selected':'' ?>>Pending</option>
                                        <option value="confirmed" <?= $row['status']==='confirmed' ? 'selected':'' ?>>Confirmed</option>
                                        <option value="cancelled" <?= $row['status']==='cancelled' ? 'selected':'' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
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
