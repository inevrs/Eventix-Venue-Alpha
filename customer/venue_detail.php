<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('customer');

$id = (int)($_GET['id'] ?? 0);

$venue = mysqli_fetch_assoc(mysqli_query($connect, "
    SELECT v.*, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(DISTINCT r.id) AS review_count
    FROM venues v
    LEFT JOIN ratings r ON v.id=r.venue_id
    WHERE v.id=$id AND v.status='active'
    GROUP BY v.id
"));

if (!$venue) { header("Location: venues.php"); exit(); }

$images  = mysqli_query($connect, "SELECT * FROM venue_images WHERE venue_id=$id ORDER BY is_thumbnail DESC, sort_order ASC");
$imgs    = [];
while ($img = mysqli_fetch_assoc($images)) $imgs[] = $img;

$reviews = mysqli_query($connect, "
    SELECT r.*, u.full_name FROM ratings r
    JOIN users u ON r.user_id=u.id
    WHERE r.venue_id=$id ORDER BY r.created_at DESC
");
$review_rows = [];
while ($r = mysqli_fetch_assoc($reviews)) $review_rows[] = $r;

$addons = mysqli_query($connect, "SELECT * FROM addons ORDER BY id");
$addon_rows = [];
while ($a = mysqli_fetch_assoc($addons)) $addon_rows[] = $a;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($venue['name']) ?> — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
    <style>
        .carousel {
            position: relative;
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--pink-light);
            aspect-ratio: 4/3;
        }

        .carousel-slides {
            display: flex;
            height: 100%;
            transition: transform 0.4s ease;
        }

        .carousel-slide { min-width: 100%; height: 100%; }
        .carousel-slide img { width: 100%; height: 100%; object-fit: cover; }

        .carousel-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            background: linear-gradient(135deg, var(--pink-light), var(--pink-mid));
        }

        .carousel-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
        }

        .carousel-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .carousel-dot.active { background: white; width: 22px; border-radius: 4px; }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.8);
            border: none;
            width: 36px; height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .carousel-btn:hover { background: white; }
        .carousel-btn.prev  { left: 12px; }
        .carousel-btn.next  { right: 12px; }

        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: start;
        }

        .info-row {
            display: flex;
            gap: 8px;
            align-items: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .review-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--pink-light);
        }

        .review-item:last-child { border-bottom: none; }

        /* ADDON CARDS */
        .addons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .addon-card {
            border: 2px solid var(--pink-light);
            border-radius: 12px;
            padding: 18px 16px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white);
            user-select: none;
            position: relative;
        }

        .addon-card:hover {
            border-color: var(--pink-mid);
            transform: translateY(-2px);
        }

        .addon-card.selected {
            border-color: var(--pink-main);
            background: #fff7f9;
        }

        .addon-card .check {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--pink-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: all 0.2s;
        }

        .addon-card.selected .check {
            background: var(--pink-main);
            color: white;
        }

        .addon-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .addon-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .addon-desc {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .addon-price {
            font-weight: 700;
            color: var(--pink-main);
            font-size: 15px;
        }

        .booking-total {
            background: var(--bg);
            border-radius: 10px;
            padding: 16px;
            margin: 16px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .total-row.grand {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            border-top: 1px solid var(--pink-light);
            padding-top: 10px;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="page-wrapper" style="max-width:1100px">
    <div style="margin-bottom: 24px;">
        <a href="venues.php" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
            <span>←</span> Back to Venues
        </a>
    </div>

    <div class="detail-layout">
        <!-- LEFT -->
        <div>
            <!-- Carousel -->
            <div class="carousel" id="carousel">
                <?php if (empty($imgs)): ?>
                    <div class="carousel-slides">
                        <div class="carousel-slide"><div class="carousel-placeholder">🏛️</div></div>
                    </div>
                <?php else: ?>
                    <div class="carousel-slides" id="slides">
                        <?php foreach ($imgs as $img): ?>
                        <div class="carousel-slide">
                            <img src="/eventix/<?= htmlspecialchars($img['image_path']) ?>" alt="venue">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($imgs) > 1): ?>
                    <button class="carousel-btn prev" onclick="changeSlide(-1)">‹</button>
                    <button class="carousel-btn next" onclick="changeSlide(1)">›</button>
                    <div class="carousel-dots" id="dots">
                        <?php foreach ($imgs as $i => $img): ?>
                        <button class="carousel-dot <?= $i===0?'active':'' ?>" onclick="goToSlide(<?= $i ?>)"></button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div style="margin-top:28px">
                <h1 style="font-family:'Playfair Display',serif;font-size:32px;color:var(--pink-dark);margin-bottom:12px">
                    <?= htmlspecialchars($venue['name']) ?>
                </h1>
                <div class="info-row">📍 <?= htmlspecialchars($venue['location']) ?></div>
                <div class="info-row">👥 Up to <?= $venue['capacity'] ?> guests</div>
                <div class="info-row">⭐ <?= number_format($venue['avg_rating'],1) ?> / 5 &nbsp;·&nbsp; <?= $venue['review_count'] ?> reviews</div>
                <p style="color:var(--text);font-size:15px;line-height:1.8;margin-top:16px">
                    <?= nl2br(htmlspecialchars($venue['description'] ?? '')) ?>
                </p>
            </div>

            <!-- Add-ons -->
            <div style="margin-top:36px">
                <h2 class="section-title">Enhance Your Event</h2>
                <p style="color:var(--text-muted);font-size:14px;margin-bottom:4px">Select any add-ons to include with your booking</p>

                <div class="addons-grid">
                    <?php foreach ($addon_rows as $addon): ?>
                    <div class="addon-card" id="addon-<?= $addon['id'] ?>" onclick="toggleAddon(<?= $addon['id'] ?>, <?= $addon['price'] ?>, '<?= htmlspecialchars($addon['name']) ?>')">
                        <div class="check">✓</div>
                        <div class="addon-icon"><?= $addon['icon'] ?></div>
                        <div class="addon-name"><?= htmlspecialchars($addon['name']) ?></div>
                        <div class="addon-desc"><?= htmlspecialchars($addon['description']) ?></div>
                        <div class="addon-price">+RM<?= number_format($addon['price'], 0) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reviews -->
            <?php if (!empty($review_rows)): ?>
            <div style="margin-top:36px">
                <h2 class="section-title">Customer Reviews</h2>
                <?php foreach ($review_rows as $r): ?>
                <div class="review-item">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <strong style="font-size:14px"><?= htmlspecialchars($r['full_name']) ?></strong>
                        <span style="color:#f4a261"><?= str_repeat('⭐', $r['rating']) ?></span>
                    </div>
                    <p style="font-size:14px;color:var(--text-muted)"><?= htmlspecialchars($r['review'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Sticky booking card -->
        <div class="card" style="position:sticky;top:84px">
            <div style="font-size:28px;font-weight:700;color:var(--pink-main);margin-bottom:4px">
                RM<?= number_format($venue['price_per_day'], 2) ?>
                <span style="font-size:14px;font-weight:400;color:var(--text-muted)">/ day</span>
            </div>
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">
                ⭐ <?= number_format($venue['avg_rating'],1) ?> &nbsp;·&nbsp; <?= $venue['review_count'] ?> reviews
            </p>

            <div class="booking-total">
                <div class="total-row">
                    <span>Venue</span>
                    <span>RM<?= number_format($venue['price_per_day'], 2) ?></span>
                </div>
                <div id="addon-summary"></div>
                <div class="total-row grand">
                    <span>Total</span>
                    <span id="grand-total">RM<?= number_format($venue['price_per_day'], 2) ?></span>
                </div>
            </div>

            <form method="GET" action="book.php">
                <input type="hidden" name="id" value="<?= $venue['id'] ?>">
                <input type="hidden" name="addons" id="selected-addons-input" value="">
                <button type="submit" class="btn btn-primary" style="width:100%">Book Now →</button>
            </form>
            <p style="text-align:center;font-size:12px;color:var(--text-muted);margin-top:12px">You won't be charged yet</p>
        </div>
    </div>
</div>

<script>
let current = 0;
const total  = <?= max(count($imgs), 1) ?>;
const venuePrice = <?= $venue['price_per_day'] ?>;
let selectedAddons = {};

function updateCarousel() {
    if (!document.getElementById('slides')) return;
    document.getElementById('slides').style.transform = `translateX(-${current * 100}%)`;
    document.querySelectorAll('.carousel-dot').forEach((d, i) => d.classList.toggle('active', i === current));
}

function changeSlide(dir) { current = (current + dir + total) % total; updateCarousel(); }
function goToSlide(i)     { current = i; updateCarousel(); }

function toggleAddon(id, price, name) {
    const card = document.getElementById('addon-' + id);
    if (selectedAddons[id]) {
        delete selectedAddons[id];
        card.classList.remove('selected');
    } else {
        selectedAddons[id] = { price, name };
        card.classList.add('selected');
    }
    updateTotal();
}

function updateTotal() {
    let addonsTotal = 0;
    let summaryHtml = '';
    let addonIds    = [];

    for (const [id, addon] of Object.entries(selectedAddons)) {
        addonsTotal += addon.price;
        addonIds.push(id);
        summaryHtml += `<div class="total-row"><span>${addon.name}</span><span>+RM${addon.price.toFixed(2)}</span></div>`;
    }

    document.getElementById('addon-summary').innerHTML = summaryHtml;
    document.getElementById('grand-total').textContent = 'RM' + (venuePrice + addonsTotal).toFixed(2);
    document.getElementById('selected-addons-input').value = addonIds.join(',');
}
</script>

</body>
</html>
