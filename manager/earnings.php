<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('manager');

$manager_id = $_SESSION['user_id'];

$total = mysqli_fetch_row(mysqli_query($connect, "
    SELECT COALESCE(SUM(p.amount),0) FROM payments p
    JOIN bookings b ON p.booking_id=b.id
    JOIN venues v ON b.venue_id=v.id
    WHERE v.manager_id=$manager_id AND p.status='paid'
"))[0];

$avg_rating = mysqli_fetch_row(mysqli_query($connect, "
    SELECT COALESCE(AVG(r.rating),0) FROM ratings r
    JOIN venues v ON r.venue_id=v.id
    WHERE v.manager_id=$manager_id
"))[0];

$payments = mysqli_query($connect, "
    SELECT p.*, u.full_name, v.name AS venue_name
    FROM payments p
    JOIN bookings b ON p.booking_id=b.id
    JOIN users u ON b.user_id=u.id
    JOIN venues v ON b.venue_id=v.id
    WHERE v.manager_id=$manager_id
    ORDER BY p.paid_at DESC
");

$ratings = mysqli_query($connect, "
    SELECT r.*, u.full_name, v.name AS venue_name
    FROM ratings r
    JOIN users u ON r.user_id=u.id
    JOIN venues v ON r.venue_id=v.id
    WHERE v.manager_id=$manager_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Earnings — Eventix</title>
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
            <li><a href="venues.php">🏛️ My Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="earnings.php" class="active">💰 Earnings</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Earnings & Ratings</h1>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Earnings</div>
                <div class="stat-value">RM<?= number_format($total, 0) ?></div>
                <div class="stat-sub">Confirmed payments</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Avg Rating</div>
                <div class="stat-value"><?= number_format($avg_rating, 1) ?></div>
                <div class="stat-sub">⭐ across all venues</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:28px">
            <h2 class="section-title">Payment History</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Customer</th><th>Venue</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($payments)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td>RM<?= number_format($row['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['method']) ?></td>
                            <td><span class="badge badge-<?= $row['status']==='paid'?'success':'warning' ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($row['paid_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Customer Reviews</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Customer</th><th>Venue</th><th>Rating</th><th>Review</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($ratings)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td>⭐ <?= $row['rating'] ?>/5</td>
                            <td><?= htmlspecialchars($row['review'] ?? '—') ?></td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
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
