<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$search = mysqli_real_escape_string($connect, $_GET['search'] ?? '');

$where = "WHERE v.status='active'";
if ($search) $where .= " AND (v.name LIKE '%$search%' OR v.location LIKE '%$search%')";

$best_picks = mysqli_query($connect, "
    SELECT v.*, 
           COALESCE(AVG(r.rating),0) AS avg_rating, 
           COUNT(DISTINCT r.id) AS review_count,
           vi.image_path AS thumbnail
    FROM venues v
    LEFT JOIN ratings r ON v.id=r.venue_id
    LEFT JOIN venue_images vi ON v.id=vi.venue_id AND vi.is_thumbnail=1
    $where
    GROUP BY v.id
    ORDER BY avg_rating DESC LIMIT 4
");

$total_venues   = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM venues WHERE status='active'"))[0];
$total_reviews  = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM ratings"))[0];
$overall_rating = mysqli_fetch_row(mysqli_query($connect, "SELECT COALESCE(AVG(rating),0) FROM ratings"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div style="background:var(--pink-light);padding:10px 40px;font-size:14px;color:var(--pink-dark)">
    Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! 👋
</div>

<div class="page-wrapper">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:48px;margin-top:20px">
        <div>
            <p style="color:var(--pink-main);font-size:12px;letter-spacing:3px;text-transform:uppercase;margin-bottom:16px">— Discover & Book</p>
            <h1 style="font-family:'Playfair Display',serif;font-size:64px;line-height:1;color:var(--pink-dark);margin-bottom:16px">
                Event<span style="color:var(--pink-main)">ix</span><br>Venues
            </h1>
            <p style="color:var(--text-muted);font-size:16px;max-width:400px;line-height:1.7;margin-bottom:32px">
                Extraordinary spaces for every occasion — from intimate gatherings to grand celebrations.
            </p>
            <form method="GET" action="venues.php" class="search-bar" style="max-width:500px">
                <input type="text" name="search" placeholder="Search by name or location..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div style="text-align:right;padding-top:40px">
            <div style="margin-bottom:24px">
                <div style="font-family:'Playfair Display',serif;font-size:40px;color:var(--pink-dark)"><?= $total_venues ?>+</div>
                <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted)">Venues</div>
            </div>
            <div style="margin-bottom:24px">
                <div style="font-family:'Playfair Display',serif;font-size:40px;color:var(--pink-dark)"><?= $total_reviews ?></div>
                <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted)">Reviews</div>
            </div>
            <div>
                <div style="font-family:'Playfair Display',serif;font-size:40px;color:var(--pink-dark)"><?= number_format($overall_rating,1) ?></div>
                <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted)">Avg Rating</div>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <div>
            <h2 class="section-title" style="margin-bottom:4px">Our Best Picks</h2>
            <p style="color:var(--text-muted);font-size:14px">Handpicked for your next unforgettable event</p>
        </div>
        <a href="venues.php" class="btn btn-outline btn-sm">View All</a>
    </div>

    <div class="venue-grid" style="margin-top:20px">
        <?php while ($row = mysqli_fetch_assoc($best_picks)): ?>
        <div class="venue-card">
            <span class="badge-new">NEW</span>
            <?php if ($row['thumbnail']): ?>
                <img src="/eventix/<?= htmlspecialchars($row['thumbnail']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <?php else: ?>
                <div class="venue-card-img-placeholder">🏛️</div>
            <?php endif; ?>
            <div class="venue-card-body">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>📍 <?= htmlspecialchars($row['location']) ?> &nbsp;·&nbsp; <?= $row['capacity'] ?> pax</p>
            </div>
            <div class="venue-card-footer">
                <span class="venue-price">RM<?= number_format($row['price_per_day'], 0) ?>/day</span>
                <span class="venue-rating">⭐ <span><?= number_format($row['avg_rating'],1) ?></span> (<?= $row['review_count'] ?>)</span>
            </div>
            <div style="padding:0 16px 16px">
                <a href="venue_detail.php?id=<?= $row['id'] ?>" class="btn btn-primary" style="width:100%;display:block">View Details</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
