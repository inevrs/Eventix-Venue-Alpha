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

// Check if already paid
$existing_payment = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM payments WHERE booking_id=$booking_id AND status='paid'"));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing_payment) {
    $method = mysqli_real_escape_string($connect, $_POST['method']);
    
    $dest = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $upload_dir = '../uploads/payments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . $booking_id . '_' . time() . '.' . $ext;
        $dest = 'uploads/payments/' . $filename;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], '../' . $dest);
    }

    if ($dest === '') {
        $error = "Please upload a valid payment proof picture.";
    } else {
        $sql = "INSERT INTO payments (booking_id, amount, method, status, payment_proof, paid_at)
                VALUES ($booking_id, $grand_total, '$method', 'paid', '$dest', NOW())";
        if (mysqli_query($connect, $sql)) {
            $success = "Payment successful! Your booking is paid and pending manager approval.";
            $existing_payment = true; // flag it so we don't show form again
        } else {
            $error = "Payment failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment — Eventix</title>
    <?php include '../includes/header_scripts.php'; ?>
    <style>
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 7px 0;
            border-bottom: 1px solid #f3f4f6;
            color: var(--text-muted);
        }
        .summary-row.total {
            font-weight: 700;
            font-size: 18px;
            color: var(--pink-main);
            border-bottom: none;
            padding-top: 12px;
        }
        /* Status Timeline */
        .status-timeline { display: flex; align-items: center; gap: 0; margin: 20px 0; }
        .status-step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
        .status-step .step-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; border: 2px solid #e5e7eb; background: #fff; color: #9ca3af; z-index: 1; }
        .status-step.active .step-dot { border-color: #e8437a; background: #e8437a; color: #fff; }
        .status-step.done .step-dot { border-color: #22c55e; background: #22c55e; color: #fff; }
        .status-step .step-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; color: #9ca3af; }
        .status-step.active .step-label, .status-step.done .step-label { color: #222; }
        .status-line { flex: 1; height: 2px; background: #e5e7eb; margin: 0 -8px; margin-top: -16px; }
        .status-line.done { background: #22c55e; }

        /* Print styles */
        @media print {
            nav, aside, .no-print, footer { display: none !important; }
            body { background: #fff !important; }
            .print-area { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="max-w-[860px] mx-auto px-6 py-10 mt-24" style="max-width:600px" data-aos="fade-up">
    <div class="mb-10" data-aos="fade-down">
        <h1 class="font-[Playfair_Display] text-4xl text-pink-dark mb-2">Payment</h1>
    </div>

    <!-- Status Timeline -->
    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-soft mb-6 print-area" data-aos="fade-up">
        <h3 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px">Booking Progress</h3>
        <?php
        $status = $booking['status'];
        $is_paid = $existing_payment ? true : false;
        ?>
        <div class="status-timeline">
            <!-- Step 1: Booked -->
            <div class="status-step done">
                <div class="step-dot">✓</div>
                <div class="step-label">Booked</div>
            </div>
            <!-- Line 1 to Step 2: Payment -->
            <div class="status-line <?= $is_paid ? 'done' : '' ?>"></div>
            <!-- Step 2: Payment -->
            <div class="status-step <?= !$is_paid ? 'active' : 'done' ?>">
                <div class="step-dot"><?= $is_paid ? '✓' : '2' ?></div>
                <div class="step-label">Payment</div>
            </div>
            <!-- Line 2 to Step 3: Approval -->
            <div class="status-line <?= $status === 'confirmed' ? 'done' : '' ?>"></div>
            <!-- Step 3: Approval -->
            <div class="status-step <?= ($is_paid && $status === 'pending') ? 'active' : ($status === 'confirmed' ? 'done' : '') ?>">
                <div class="step-dot"><?= $status === 'confirmed' ? '✓' : ($status === 'cancelled' ? '✗' : '3') ?></div>
                <div class="step-label"><?= $status === 'cancelled' ? 'Rejected' : 'Approval' ?></div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">
            <?= $success ?> 
            <br><br>
            <a href="my_bookings.php" class="bg-pink-main text-white px-6 py-2.5 rounded-full hover:bg-pink-dark transition-colors btn-sm no-print" style="display: inline-block;">View My Bookings →</a>
            <button onclick="window.print()" class="border-2 border-pink-main text-pink-main px-6 py-2.5 rounded-full font-semibold text-sm hover:bg-pink-50 transition-colors no-print" style="display: inline-block; margin-left: 8px;">🖨️ Print Receipt</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?><div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-6"><?= $error ?></div><?php endif; ?>

    <!-- Booking Summary (always shown, printable) -->
    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-soft print-area" style="margin-bottom:20px" data-aos="fade-up">
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

    <?php if ($status === 'cancelled'): ?>
        <div class="bg-red-50 text-red-600 px-6 py-8 rounded-2xl text-center">
            <p style="font-size:32px;margin-bottom:8px">❌</p>
            <h3 style="font-weight:700;margin-bottom:4px">Booking Cancelled</h3>
            <p class="text-sm">This booking has been cancelled by the venue manager.</p>
            <a href="venues.php" class="bg-pink-main text-white px-6 py-2.5 rounded-full hover:bg-pink-dark transition-colors inline-block mt-4 no-print">Browse Other Venues</a>
        </div>
    <?php elseif (!$existing_payment): ?>
        <!-- Payment form — shown if not yet paid -->
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-soft no-print" data-aos="fade-up">
            <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:24px">Payment Method</h2>
            <div class="bg-yellow-50 text-yellow-700 px-4 py-3 rounded-lg text-sm mb-6">
                ⏳ Please complete your payment to submit your booking for manager approval.
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-5">
                    <label>Select Method</label>
                    <select name="method" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-sans text-text focus:border-pink-main focus:ring-2 focus:ring-pink-main/10 outline-none transition-all cursor-pointer pr-10" required>
                        <option value="Online Banking">Online Banking</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="eWallet">eWallet (Touch 'n Go / GrabPay)</option>
                    </select>
                </div>
                <div class="mb-5">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase text-text-muted mb-2">Upload Payment Proof (Screenshot / Receipt)</label>
                    <input type="file" name="payment_proof" accept="image/*" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none" required>
                </div>
                <button type="submit" class="bg-pink-main text-white px-6 py-2.5 rounded-full hover:bg-pink-dark transition-all hover:-translate-y-px active:scale-95 shadow-md hover:shadow-lg" style="width:100%">
                    Pay RM<?= number_format($grand_total, 2) ?> →
                </button>
            </form>
        </div>
    <?php elseif ($existing_payment && !$success): ?>
        <!-- Already paid -->
        <div class="bg-green-50 text-green-700 px-6 py-8 rounded-2xl text-center" data-aos="fade-up">
            <p style="font-size:32px;margin-bottom:8px">✅</p>
            <h3 style="font-weight:700;margin-bottom:4px">Payment Submitted</h3>
            <p class="text-sm mb-4">
                <?php if ($status === 'pending'): ?>
                    Your payment of RM<?= number_format($grand_total, 2) ?> is complete. Your booking is now awaiting manager approval.
                <?php else: ?>
                    Your booking is confirmed and paid successfully.
                <?php endif; ?>
            </p>
            <a href="my_bookings.php" class="bg-pink-main text-white px-6 py-2.5 rounded-full hover:bg-pink-dark transition-colors inline-block no-print">View My Bookings →</a>
            <button onclick="window.print()" class="border-2 border-pink-main text-pink-main px-6 py-2.5 rounded-full font-semibold text-sm hover:bg-pink-50 transition-colors inline-block no-print" style="margin-left: 8px;">🖨️ Print Receipt</button>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer_scripts.php'; ?>
</body>
</html>
