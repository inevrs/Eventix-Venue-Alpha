<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('manager');

$manager_id = $_SESSION['user_id'];

$total_venues   = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM venues WHERE manager_id=$manager_id"))[0];
$total_bookings = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM bookings b JOIN venues v ON b.venue_id=v.id WHERE v.manager_id=$manager_id"))[0];
$total_earnings = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN venues v ON b.venue_id=v.id WHERE v.manager_id=$manager_id AND p.status='paid'"))[0];
$avg_rating     = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(AVG(r.rating),0) FROM ratings r JOIN venues v ON r.venue_id=v.id WHERE v.manager_id=$manager_id"))[0];

// Fetch data for Chart (Bookings per Venue)
$chart_query = mysqli_query($connect, "SELECT v.name, COUNT(b.id) as count FROM venues v LEFT JOIN bookings b ON v.id=b.venue_id WHERE v.manager_id=$manager_id GROUP BY v.id");
$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_query)) {
    // Truncate long venue names for chart
    $name = strlen($row['name']) > 15 ? substr($row['name'], 0, 15) . '...' : $row['name'];
    $chart_labels[] = $name;
    $chart_data[] = $row['count'];
}

$recent = mysqli_query($connect, "
    SELECT b.*, u.full_name, v.name AS venue_name
    FROM bookings b
    JOIN users u ON b.user_id=u.id
    JOIN venues v ON b.venue_id=v.id
    WHERE v.manager_id=$manager_id
    ORDER BY b.created_at DESC LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard — Eventix</title>
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
        <p class="sidebar-section">My Business</p>
        <ul class="sidebar-menu">
            <li><a href="venues.php">🏛️ My Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="earnings.php">💰 Earnings</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            <p>Here's how your venues are performing</p>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">My Venues</div>
                <div class="stat-value"><?= $total_venues ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value"><?= $total_bookings ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Earnings</div>
                <div class="stat-value">RM<?= number_format($total_earnings, 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Avg Rating</div>
                <div class="stat-value"><?= number_format($avg_rating, 1) ?></div>
                <div class="stat-sub">⭐ across all venues</div>
            </div>
        </div>

        <!-- Chart Section for STA116 Integration -->
        <div class="card" style="margin-bottom: 24px;">
            <h2 class="section-title">Bookings per Venue (STA116 Integration)</h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px;">This bar chart represents the frequency of bookings across your different venues.</p>
            <div style="height: 300px;">
                <canvas id="venueChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Recent Bookings</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Event Date</th>
                            <th>Guests</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td><?= date('d M Y', strtotime($row['start_date'])) ?> to <?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <td><?= $row['guest_count'] ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status']==='confirmed'?'success':($row['status']==='pending'?'warning':'danger') ?>">
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
const ctx = document.getElementById('venueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Total Bookings',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: 'rgba(233, 30, 99, 0.7)',
            borderColor: 'rgba(233, 30, 99, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

</body>
</html>
