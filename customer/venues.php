<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$search = mysqli_real_escape_string($connect, $_GET['search'] ?? '');

$where = "WHERE v.status='active'";
if ($search) $where .= " AND (v.name LIKE '%$search%' OR v.location LIKE '%$search%')";

$venues = mysqli_query($connect, "
    SELECT v.*, 
           COALESCE(AVG(r.rating),0) AS avg_rating, 
           COUNT(DISTINCT r.id) AS review_count,
           vi.image_path AS thumbnail
    FROM venues v
    LEFT JOIN ratings r ON v.id=r.venue_id
    LEFT JOIN venue_images vi ON v.id=vi.venue_id AND vi.is_thumbnail=1
    $where
    GROUP BY v.id
    ORDER BY v.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venues — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h1>All Venues</h1>
        <p>Find the perfect space for your event</p>
    </div>

    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search by name or location..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <div class="venue-grid">
        <?php while ($row = mysqli_fetch_assoc($venues)): ?>
        <div class="venue-card">
            <?php if ($row['thumbnail']): ?>
                <img src="/eventix/<?= htmlspecialchars($row['thumbnail']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <?php else: ?>
                <div class="venue-card-img-placeholder">🏛️</div>
            <?php endif; ?>
            <div class="venue-card-body">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>📍 <?= htmlspecialchars($row['location']) ?> &nbsp;·&nbsp; <?= $row['capacity'] ?> pax</p>
                <p style="font-size:13px;color:var(--text);line-height:1.5"><?= htmlspecialchars(substr($row['description'] ?? '', 0, 80)) ?>...</p>
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
