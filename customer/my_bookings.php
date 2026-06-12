<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$user_id = $_SESSION['user_id'];

$bookings = mysqli_query($connect, "
    SELECT b.*, v.name AS venue_name, v.location, v.price_per_day,
           p.status AS payment_status, p.method,
           r.rating, r.review
    FROM bookings b
    JOIN venues v ON b.venue_id=v.id
    LEFT JOIN payments p ON p.booking_id=b.id
    LEFT JOIN ratings r ON r.venue_id=v.id AND r.user_id=$user_id
    WHERE b.user_id=$user_id
    ORDER BY b.created_at DESC
");

$success = $error = '';

// Submit rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['venue_id'], $_POST['rating'])) {
    $venue_id = (int)$_POST['venue_id'];
    $rating   = (int)$_POST['rating'];
    $review   = mysqli_real_escape_string($connect, $_POST['review'] ?? '');

    $check = mysqli_fetch_row(mysqli_query($connect, "SELECT id FROM ratings WHERE user_id=$user_id AND venue_id=$venue_id"));
    if ($check) {
        mysqli_query($connect, "UPDATE ratings SET rating=$rating, review='$review' WHERE user_id=$user_id AND venue_id=$venue_id");
    } else {
        mysqli_query($connect, "INSERT INTO ratings (user_id, venue_id, rating, review) VALUES ($user_id, $venue_id, $rating, '$review')");
    }
    $success = "Rating submitted!";
    header("Location: my_bookings.php?rated=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1>My Bookings</h1>
        <p>Track and manage your venue reservations</p>
    </div>

    <?php if (isset($_GET['rated'])): ?>
        <div class="alert alert-success">Rating submitted successfully!</div>
    <?php endif; ?>

    <?php
    $rows = [];
    while ($row = mysqli_fetch_assoc($bookings)) $rows[] = $row;

    if (empty($rows)): ?>
        <div class="card" style="text-align:center;padding:60px">
            <p style="font-size:40px;margin-bottom:16px">🏛️</p>
            <h2 style="font-family:'Playfair Display',serif;color:var(--pink-dark);margin-bottom:8px">No bookings yet</h2>
            <p style="color:var(--text-muted);margin-bottom:24px">Discover amazing venues and make your first booking.</p>
            <a href="venues.php" class="btn btn-primary">Browse Venues</a>
        </div>
    <?php else: ?>

    <div style="display:flex;flex-direction:column;gap:20px">
        <?php foreach ($rows as $row): ?>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
                <div>
                    <h3 style="font-family:'Playfair Display',serif;font-size:20px;color:var(--pink-dark);margin-bottom:6px">
                        <?= htmlspecialchars($row['venue_name']) ?>
                    </h3>
                    <p style="color:var(--text-muted);font-size:13px;margin-bottom:4px">📍 <?= htmlspecialchars($row['location']) ?></p>
                    <p style="color:var(--text-muted);font-size:13px;margin-bottom:4px">📅 <?= date('d M Y', strtotime($row['start_date'])) ?> to <?= date('d M Y', strtotime($row['end_date'])) ?> &nbsp;·&nbsp; 👥 <?= $row['guest_count'] ?> guests</p>
                    <p style="color:var(--text-muted);font-size:13px">💳 <?= htmlspecialchars($row['method'] ?? 'Not paid') ?> &nbsp;·&nbsp; RM<?= number_format($row['price_per_day'], 2) ?></p>
                </div>
                <div style="text-align:right">
                    <span class="badge badge-<?= $row['status']==='confirmed'?'success':($row['status']==='pending'?'warning':'danger') ?>" style="font-size:13px;padding:6px 16px">
                        <?= ucfirst($row['status']) ?>
                    </span>
                    <?php if ($row['payment_status'] === 'paid'): ?>
                        <br><span class="badge badge-success" style="margin-top:8px">Paid</span>
                    <?php elseif ($row['status'] === 'pending'): ?>
                        <br><a href="payment.php?booking_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:8px">Pay Now</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($row['status'] === 'confirmed'): ?>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--pink-light)">
                <p style="font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:12px">YOUR REVIEW</p>
                <form method="POST" style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
                    <input type="hidden" name="venue_id" value="<?= $row['venue_id'] ?>">
                    <select name="rating" style="padding:8px 12px;border:1.5px solid var(--pink-light);border-radius:8px;font-size:14px;">
                        <?php for ($i=5;$i>=1;$i--): ?>
                        <option value="<?= $i ?>" <?= $row['rating']==$i?'selected':'' ?>>⭐ <?= $i ?>/5</option>
                        <?php endfor; ?>
                    </select>
                    <input type="text" name="review" value="<?= htmlspecialchars($row['review'] ?? '') ?>" placeholder="Leave a comment..." style="flex:1;padding:8px 14px;border:1.5px solid var(--pink-light);border-radius:8px;font-size:14px;min-width:200px;">
                    <button type="submit" class="btn btn-outline btn-sm"><?= $row['rating'] ? 'Update' : 'Submit' ?> Review</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

</body>
</html>
