<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$id    = (int)($_GET['id'] ?? 0);
$venue = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM venues WHERE id=$id AND status='active'"));
if (!$venue) { header("Location: venues.php"); exit(); }

// Parse addons from URL
$addon_ids  = [];
$addon_rows = [];
$addons_total = 0;

if (!empty($_GET['addons'])) {
    $raw = array_filter(array_map('intval', explode(',', $_GET['addons'])));
    if (!empty($raw)) {
        $in = implode(',', $raw);
        $res = mysqli_query($connect, "SELECT * FROM addons WHERE id IN ($in)");
        while ($a = mysqli_fetch_assoc($res)) {
            $addon_rows[] = $a;
            $addon_ids[]  = $a['id'];
            $addons_total += $a['price'];
        }
    }
}

$grand_total = $venue['price_per_day'] + $addons_total;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $start_date = mysqli_real_escape_string($connect, $_POST['start_date']);
    $end_date   = mysqli_real_escape_string($connect, $_POST['end_date']);
    $guests     = (int)$_POST['guest_count'];
    $notes      = mysqli_real_escape_string($connect, $_POST['notes'] ?? '');
    $post_addons = array_filter(array_map('intval', explode(',', $_POST['addon_ids'] ?? '')));

    if (strtotime($end_date) < strtotime($start_date)) {
        $error = "End date cannot be before start date.";
    } elseif ($guests > $venue['capacity']) {
        $error = "Guest count exceeds venue capacity of {$venue['capacity']}.";
    } else {
        $sql = "INSERT INTO bookings (user_id, venue_id, start_date, end_date, guest_count, notes, status)
                VALUES ($user_id, $id, '$start_date', '$end_date', $guests, '$notes', 'pending')";
        if (mysqli_query($connect, $sql)) {
            $booking_id = mysqli_insert_id($connect);

            // Save addons
            foreach ($post_addons as $aid) {
                $aprice = mysqli_fetch_row(mysqli_query($connect, "SELECT price FROM addons WHERE id=$aid"))[0] ?? 0;
                mysqli_query($connect, "INSERT INTO booking_addons (booking_id, addon_id, price) VALUES ($booking_id, $aid, $aprice)");
            }

            header("Location: payment.php?booking_id=$booking_id");
            exit();
        } else {
            $error = "Booking failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Venue — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
    <style>
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 7px 0;
            border-bottom: 1px solid var(--pink-light);
        }

        .summary-row:last-child { border-bottom: none; }
        .summary-row.total { font-weight: 700; font-size: 16px; color: var(--pink-main); padding-top: 12px; }

        .addon-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-light);
            color: var(--pink-dark);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            margin: 4px 4px 4px 0;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper" style="max-width:860px">
    <div class="page-header">
        <h1>Complete Your Booking</h1>
        <div style="margin-bottom: 24px;">
            <a href="venue_detail.php?id=<?= $id ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>←</span> Back to Venue
            </a>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start">

        <!-- Booking Form -->
        <div class="card">
            <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:24px">Your Details</h2>
            <form method="POST">
                <input type="hidden" name="addon_ids" value="<?= implode(',', $addon_ids) ?>">
                <div style="display: flex; gap: 16px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Number of Guests</label>
                    <input type="number" name="guest_count" min="1" max="<?= $venue['capacity'] ?>" placeholder="Max <?= $venue['capacity'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Additional Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any special requirements..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Proceed to Payment →</button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="card" style="position:sticky;top:84px">
            <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:20px">Order Summary</h2>

            <div style="margin-bottom:16px">
                <div style="font-weight:600;font-size:15px;color:var(--text);margin-bottom:4px"><?= htmlspecialchars($venue['name']) ?></div>
                <div style="font-size:13px;color:var(--text-muted)">📍 <?= htmlspecialchars($venue['location']) ?></div>
            </div>

            <div class="summary-row">
                <span id="venue-price-label">Venue (1 day)</span>
                <span id="venue-price-display">RM<?= number_format($venue['price_per_day'], 2) ?></span>
            </div>

            <?php foreach ($addon_rows as $a): ?>
            <div class="summary-row">
                <span><?= $a['icon'] ?> <?= htmlspecialchars($a['name']) ?></span>
                <span>+RM<?= number_format($a['price'], 2) ?></span>
            </div>
            <?php endforeach; ?>

            <div class="summary-row total">
                <span>Total</span>
                <span id="grand-total-display">RM<?= number_format($grand_total, 2) ?></span>
            </div>

            <?php if (!empty($addon_rows)): ?>
            <div style="margin-top:16px">
                <p style="font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px">Selected Add-ons</p>
                <?php foreach ($addon_rows as $a): ?>
                <span class="addon-pill"><?= $a['icon'] ?> <?= htmlspecialchars($a['name']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const basePrice = <?= $venue['price_per_day'] ?>;
const addonsTotal = <?= $addons_total ?>;
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');
const venueLabel = document.getElementById('venue-price-label');
const venueDisplay = document.getElementById('venue-price-display');
const grandDisplay = document.getElementById('grand-total-display');

function calculateTotal() {
    if (startInput.value) {
        endInput.min = startInput.value;
    }
    
    if (startInput.value && endInput.value) {
        const start = new Date(startInput.value);
        const end = new Date(endInput.value);
        let days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        if (days < 1) days = 1;
        
        const venueTotal = basePrice * days;
        const grandTotal = venueTotal + addonsTotal;
        
        venueLabel.innerText = `Venue (${days} day${days > 1 ? 's' : ''})`;
        venueDisplay.innerText = 'RM' + venueTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        grandDisplay.innerText = 'RM' + grandTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
}

startInput.addEventListener('change', calculateTotal);
endInput.addEventListener('change', calculateTotal);
</script>
</body>
</html>
