<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('admin');

$total_users    = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM users WHERE role != 'admin'"))[0];
$total_venues   = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM venues"))[0];
$total_bookings = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM bookings"))[0];
$total_revenue  = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'"))[0];

// Fetch data for Chart (Booking Status Distribution)
$chart_query = mysqli_query($connect, "SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_query)) {
    $chart_labels[] = ucfirst($row['status']);
    $chart_data[] = $row['count'];
}

$recent_bookings = mysqli_query($connect, "
    SELECT b.*, u.full_name, v.name AS venue_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN venues v ON b.venue_id = v.id
    ORDER BY b.created_at DESC LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="layout-sidebar">
    <aside class="sidebar">
        <p class="sidebar-section">Overview</p>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
        </ul>
        <p class="sidebar-section">Manage</p>
        <ul class="sidebar-menu">
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="venues.php">🏛️ Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="payments.php">💳 Payments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Overview of the Eventix platform</p>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-sub">Customers & managers</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Venues</div>
                <div class="stat-value"><?= $total_venues ?></div>
                <div class="stat-sub">Listed on platform</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Bookings</div>
                <div class="stat-value"><?= $total_bookings ?></div>
                <div class="stat-sub">All time</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Revenue</div>
                <div class="stat-value">RM<?= number_format($total_revenue, 0) ?></div>
                <div class="stat-sub">Confirmed payments</div>
            </div>
        </div>

        <!-- Chart Section for STA116 Integration -->
        <div class="card" style="margin-bottom: 24px; max-width: 600px;">
            <h2 class="section-title">Booking Status Distribution (STA116 Integration)</h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px;">This chart visualizes the frequency distribution of booking statuses.</p>
            <canvas id="statusChart"></canvas>
        </div>

        <div class="card">
            <h2 class="section-title">Recent Bookings</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td><?= date('d M Y', strtotime($row['start_date'])) ?> to <?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] === 'confirmed' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('statusChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Number of Bookings',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: [
                'rgba(233, 30, 99, 0.7)',  // Pink (Confirmed)
                'rgba(255, 193, 7, 0.7)',  // Yellow (Pending)
                'rgba(158, 158, 158, 0.7)' // Grey (Cancelled)
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

</body>
</html>
