<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('manager');

$manager_id = $_SESSION['user_id'];

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['booking_id'])) {
    $id     = (int)$_POST['booking_id'];
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    mysqli_query($connect, "UPDATE bookings SET status='$status' WHERE id=$id");
    $success = 'Booking status updated successfully!';
}

$bookings = mysqli_query($connect, "
    SELECT b.*, u.full_name, u.email, v.name AS venue_name, p.payment_proof, p.status AS payment_status
    FROM bookings b
    JOIN users u ON b.user_id=u.id
    JOIN venues v ON b.venue_id=v.id
    LEFT JOIN payments p ON b.id=p.booking_id
    WHERE v.manager_id=$manager_id
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings — Eventix</title>
    <?php include '../includes/header_scripts.php'; ?>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="flex min-h-screen pt-24">
    <aside class="w-64 bg-white border-r border-gray-100 shrink-0 py-8 shadow-sm  z-10">
        <p class="text-[10px] tracking-widest text-text-muted font-bold uppercase mb-3 px-8">Overview</p>
        <ul class="list-none p-0 m-0 mb-8 flex flex-col gap-1 px-4">
            <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all text-text-muted hover:bg-gray-50 hover:text-text">Dashboard</a></li>
        </ul>
        <p class="text-[10px] tracking-widest text-text-muted font-bold uppercase mb-3 px-8">My Business</p>
        <ul class="list-none p-0 m-0 mb-8 flex flex-col gap-1 px-4">
            <li><a href="venues.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all text-text-muted hover:bg-gray-50 hover:text-text">My Venues</a></li>
            <li><a href="bookings.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all bg-pink-main/10 text-pink-main font-semibold">Bookings</a></li>
            <li><a href="earnings.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all text-text-muted hover:bg-gray-50 hover:text-text">Earnings</a></li>
        </ul>
    </aside>

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="mb-10" data-aos="fade-down">
            <h1 class="font-[Playfair_Display] text-4xl text-pink-dark mb-2">Bookings</h1>
            <p>Manage bookings for your venues</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6"><?= $success ?></div>
        <?php endif; ?>

        <div class="bg-white border border-gray-100 rounded-2xl  p-8 shadow-soft mb-8">
            <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm">
                <table class="w-full text-sm text-left border-collapse min-w-[800px]">
                    <thead class="bg-gray-50 text-pink-dark text-xs uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-6 py-4 border-b border-gray-100">Customer</th>
                            <th class="px-6 py-4 border-b border-gray-100">Email</th>
                            <th class="px-6 py-4 border-b border-gray-100">Venue</th>
                            <th class="px-6 py-4 border-b border-gray-100">Event Date</th>
                            <th class="px-6 py-4 border-b border-gray-100">Guests</th>
                            <th class="px-6 py-4 border-b border-gray-100">Payment Proof</th>
                            <th class="px-6 py-4 border-b border-gray-100">Status</th>
                            <th class="px-6 py-4 border-b border-gray-100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rows = [];
                        while ($row = mysqli_fetch_assoc($bookings)) $rows[] = $row;
                        if (empty($rows)): ?>
                        <tr><td colspan="8" class="px-6 py-12 text-center text-text-muted">
                            <p style="font-size:32px;margin-bottom:8px">📅</p>
                            <p class="font-semibold mb-1">No bookings yet</p>
                            <p class="text-sm">Bookings from customers will appear here.</p>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 border-b border-gray-100 text-text"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text"><?= htmlspecialchars($row['venue_name']) ?></td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text"><?= date('d M Y', strtotime($row['start_date'])) ?> to <?= date('d M Y', strtotime($row['end_date'])) ?></td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text"><?= $row['guest_count'] ?></td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text">
                                <?php if (!empty($row['payment_proof'])): ?>
                                    <a href="/eventix/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-pink-main font-semibold hover:underline">
                                        📄 View Proof
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-text-muted">Unpaid / No Proof</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text">
                                <span class="<?= $row['status']==='confirmed' ? 'bg-green-100 text-green-700' : ($row['status']==='pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?> px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 border-b border-gray-100 text-text">
                                <form method="POST" style="display:flex;gap:6px" onsubmit="return confirm('Are you sure you want to change this booking status?')">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <select name="status" style="padding:6px 10px;border:1.5px solid var(--pink-light);border-radius:8px;font-size:13px;">
                                        <option value="pending"   <?= $row['status']==='pending'   ?'selected':'' ?>>Pending</option>
                                        <option value="confirmed" <?= $row['status']==='confirmed' ?'selected':'' ?>>Confirmed</option>
                                        <option value="cancelled" <?= $row['status']==='cancelled' ?'selected':'' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="bg-pink-main text-white px-4 py-2 rounded-full font-semibold text-xs hover:bg-pink-dark transition-colors inline-block">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer_scripts.php'; ?>
</body>
</html>
