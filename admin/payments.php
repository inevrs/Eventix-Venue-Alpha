<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('admin');

$payments = mysqli_query($connect, "
    SELECT p.*, u.full_name, v.name AS venue_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN venues v ON b.venue_id = v.id
    ORDER BY p.paid_at DESC
");

$total = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments — Eventix</title>
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
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="payments.php" class="active">💳 Payments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Payments</h1>
            <p>Total confirmed revenue: <strong style="color:var(--pink-main)">RM<?= number_format($total, 2) ?></strong></p>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($payments)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td>RM<?= number_format($row['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['method']) ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] === 'paid' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($row['paid_at'])) ?></td>
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
