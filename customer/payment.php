<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$booking_id = (int)($_GET['booking_id'] ?? 0);
$user_id    = $_SESSION['user_id'];

$booking = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT b.*, v.name AS venue_name, v.price_per_day, v.location
    FROM bookings b
    JOIN venues v ON b.venue_id=v.id
    WHERE b.id=$booking_id AND b.user_id=$user_id
"));

if (!$booking) { header("Location: my_bookings.php"); exit(); }

$addons = mysqli_query($connect, "
    SELECT a.name, a.icon, ba.price FROM booking_addons ba
    JOIN addons a ON ba.addon_id=a.id
    WHERE ba.booking_id=$booking_id
");
$addon_rows   = [];
$addons_total = 0;
while ($a = mysqli_fetch_assoc($addons)) {
    $addon_rows[]  = $a;
    $addons_total += $a['price'];
}

$days = round((strtotime($booking['end_date']) - strtotime($booking['start_date'])) / 86400) + 1;
$grand_total = ($booking['price_per_day'] * $days) + $addons_total;
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = mysqli_real_escape_string($connect, $_POST['method']);
    $sql = "INSERT INTO payments (booking_id, amount, method, status, paid_at)
            VALUES ($booking_id, $grand_total, '$method', 'paid', NOW())";
    if (mysqli_query($connect, $sql)) {
        mysqli_query($connect, "UPDATE bookings SET status='confirmed' WHERE id=$booking_id");
        $success = "Payment successful! Your booking is confirmed.";
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
    <style>
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 7px 0;
            border-bottom: 1px solid var(--pink-light);
            color: var(--text-muted);
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 18px;
            color: var(--pink-main);
            border-bottom: none;
            padding-top: 12px;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper" style="max-width:600px">
    <div class="page-header">
        <h1>Make Payment</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $success ?> 
            <br><br>
            <a href="my_bookings.php" class="btn btn-primary btn-sm" style="display: inline-block;">View My Bookings →</a>
        </div>
    <?php else: ?>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <div class="card" style="margin-bottom:20px">
        <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:20px">Booking Summary</h2>

        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= htmlspecialchars($booking['venue_name']) ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
            📍 <?= htmlspecialchars($booking['location']) ?> &nbsp;·&nbsp;
            📅 <?= date('d M Y', strtotime($booking['start_date'])) ?> to <?= date('d M Y', strtotime($booking['end_date'])) ?> &nbsp;·&nbsp;
            👥 <?= $booking['guest_count'] ?> guests
        </div>

        <div class="summary-row">
            <span>Venue (<?= $days ?> day<?= $days > 1 ? 's' : '' ?>)</span>
            <span>RM<?= number_format($booking['price_per_day'] * $days, 2) ?></span>
        </div>

        <?php foreach ($addon_rows as $a): ?>
        <div class="summary-row">
            <span><?= $a['icon'] ?> <?= htmlspecialchars($a['name']) ?></span>
            <span>+RM<?= number_format($a['price'], 2) ?></span>
        </div>
        <?php endforeach; ?>

        <div class="summary-row total">
            <span>Total</span>
            <span>RM<?= number_format($grand_total, 2) ?></span>
        </div>
    </div>

    <div class="card">
        <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:24px">Payment Method</h2>
        <form method="POST">
            <div class="form-group">
                <label>Select Method</label>
                <select name="method" required>
                    <option value="Online Banking">Online Banking</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="eWallet">eWallet (Touch 'n Go / GrabPay)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">
                Pay RM<?= number_format($grand_total, 2) ?> →
            </button>
        </form>
    </div>

    <?php endif; ?>
</div>

</body>
</html>
