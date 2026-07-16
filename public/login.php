<?php
$projectRoot = dirname(__DIR__);
set_include_path($projectRoot . PATH_SEPARATOR . get_include_path());

require_once 'includes/auth.php';
startSecureSession();
require_once 'includes/db.php';
require_once 'includes/validation.php';

if (isLoggedIn()) {
    header("Location: /eventix/" . userRole() . "/dashboard.php");
    exit();
}

// Fetch random venue previews for slideshow
$preview_venues = [];
$pvQuery = mysqli_query($connect, "
    SELECT v.name, v.location, v.price_per_day,
           COALESCE(AVG(r.rating),0) AS avg_rating,
           COUNT(DISTINCT r.id) AS review_count,
           vi.image_path AS thumbnail
    FROM venues v
    LEFT JOIN ratings r ON v.id=r.venue_id
    LEFT JOIN venue_images vi ON v.id=vi.venue_id AND vi.is_thumbnail=1
    WHERE v.status='active' AND vi.image_path IS NOT NULL
    GROUP BY v.id
    ORDER BY RAND()
    LIMIT 5
");
while ($pv = mysqli_fetch_assoc($pvQuery)) {
    $preview_venues[] = $pv;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailResult = validate_email($_POST['email'] ?? '');
    $passwordResult = validate_required($_POST['password'] ?? '', 'Password');

    if (!$emailResult['valid']) {
        $error = $emailResult['message'];
    } elseif (!$passwordResult['valid']) {
        $error = $passwordResult['message'];
    } else {
        $email = $emailResult['value'];
        $password = $passwordResult['value'];

        $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['full_name'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;

            $redirect = $_GET['redirect'] ?? '';
            if ($redirect && strpos($redirect, 'venue.php') === 0) {
                if ($user['role'] === 'customer') {
                    $venue_id = explode('id=', $redirect)[1] ?? 0;
                    header("Location: /eventix/customer/book.php?id=" . (int)$venue_id);
                } else {
                    header("Location: /eventix/" . $redirect);
                }
            } else {
                if ($user['role'] === 'admin')   header("Location: /eventix/admin/dashboard.php");
                if ($user['role'] === 'manager') header("Location: /eventix/manager/dashboard.php");
                if ($user['role'] === 'customer') header("Location: /eventix/index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Eventix</title>
    <?php include 'includes/header_scripts.php'; ?>
</head>
<body class="text-text font-sans h-screen m-0 overflow-hidden">

<div class="flex h-screen">
    <!-- LEFT PANEL -->
    <div class="hidden lg:flex flex-col justify-between w-5/12 bg-pink-light p-14 relative overflow-hidden" data-aos="fade-right" data-aos-duration="1000">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.8),transparent)] pointer-events-none z-[1]"></div>
        <div class="relative z-10 flex flex-col items-start gap-3">
            <img src="/eventix/images/eventix_logo.jpg" alt="Eventix Logo" class="w-[120px] h-auto rounded-xl shadow-sm mb-2 mix-blend-multiply">
            <h2 class="font-sans text-3xl font-bold text-pink-main tracking-tight m-0">Eventix</h2>
            <span class="text-[10px] tracking-widest text-text-muted font-semibold uppercase">EVENT MANAGEMENT SYSTEM</span>
        </div>

        <!-- Venue Preview Slideshow -->
        <div class="relative z-10 flex-1 flex flex-col justify-center py-6">
            <p class="text-[10px] tracking-widest text-text-muted font-semibold uppercase mb-3">Featured Venues</p>
            <div id="venueSlideshow" class="relative w-full rounded-2xl overflow-hidden shadow-lg" style="aspect-ratio:16/10">
                <?php foreach ($preview_venues as $i => $pv): ?>
                <div class="venue-slide absolute inset-0 transition-opacity duration-700 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>" data-slide="<?= $i ?>">
                    <img src="/eventix/<?= htmlspecialchars($pv['thumbnail']) ?>" alt="<?= htmlspecialchars($pv['name']) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                        <h3 class="font-semibold text-base leading-tight mb-1 drop-shadow-sm"><?= htmlspecialchars($pv['name']) ?></h3>
                        <p class="text-xs opacity-90 mb-1.5"><?= htmlspecialchars($pv['location']) ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold">RM<?= number_format($pv['price_per_day'], 0) ?>/day</span>
                            <span class="text-xs"><span class="text-yellow-300">&#9733;</span> <?= number_format($pv['avg_rating'], 1) ?> (<?= $pv['review_count'] ?>)</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Slide Dots -->
        <div class="relative z-10 flex gap-2" id="slideDots">
            <?php foreach ($preview_venues as $i => $pv): ?>
            <span class="w-2 h-2 rounded-full transition-all duration-300 cursor-pointer <?= $i === 0 ? 'bg-pink-main w-6' : 'bg-pink-main/30' ?>" data-dot="<?= $i ?>" onclick="goToSlide(<?= $i ?>)"></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="w-full lg:w-7/12 bg-surface flex flex-col justify-center items-center p-8 overflow-y-auto" data-aos="fade-left" data-aos-duration="1000">
        <div class="w-full max-w-[400px] auth-panel rounded-3xl p-8">
            <h1 class="font-[Playfair_Display] text-5xl text-pink-dark leading-[1.1] mb-4">Welcome<br>Back</h1>
            <p class="text-text-muted text-sm mb-10">Sign in to your account to continue.</p>

            <?php if ($error): ?>
                <div class="bg-red-500/10 text-red-400 px-4 py-3 rounded-xl text-sm mb-6"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateLogin()">
                <div class="mb-5">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-2">Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required class="form-input w-full px-4 py-3 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                </div>
                <div class="mb-8">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="passInput" placeholder="••••••••" required class="form-input w-full px-4 py-3 pr-12 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                        <button type="button" data-password-toggle="passInput" onclick="togglePass('passInput')" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-pink-main transition">
                            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-pink-main text-white py-3.5 rounded-full font-semibold text-sm hover:bg-pink-dark transition-all transform hover:-translate-y-px active:scale-95 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M13 5l7 7-7 7M20 12H4"/></svg>
                </button>
            </form>

            <p class="text-center mt-8 text-sm text-text-muted">
                Don't have an account? <a href="/eventix/register.php" class="text-pink-main font-semibold hover:underline">Register here</a>
            </p>
            <div class="text-center mt-4">
                <a href="/eventix/index.php" class="inline-flex items-center gap-2 text-sm text-text-muted hover:text-pink-main font-medium transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Continue as Guest
                </a>
            </div>
            <div class="text-center mt-12">
                <a href="/eventix/login.php?role=admin" class="text-[10px] tracking-widest uppercase font-semibold text-text-muted hover:text-pink-main transition-colors">ADMIN PORTAL</a>
            </div>
        </div>
    </div>
</div>

<script>
function validateLogin() {
    const email = document.querySelector('input[name="email"]').value.trim();
    let errors = [];
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Please enter a valid email address.');
    }
    if (errors.length > 0) {
        let existing = document.getElementById('js-error');
        if (existing) existing.remove();
        let div = document.createElement('div');
        div.id = 'js-error';
        div.className = 'bg-red-500/10 text-red-400 px-4 py-3 rounded-xl text-sm mb-6';
        div.innerHTML = errors.join('<br>');
        document.querySelector('form').prepend(div);
        return false;
    }
    return true;
}
</script>
<script>
(function() {
    const slides = document.querySelectorAll('.venue-slide');
    const dots = document.querySelectorAll('#slideDots [data-dot]');
    if (slides.length === 0) return;
    let current = 0;
    let timer;

    function showSlide(idx) {
        slides.forEach((s, i) => {
            s.style.opacity = i === idx ? '1' : '0';
        });
        dots.forEach((d, i) => {
            d.className = i === idx
                ? 'w-6 h-2 rounded-full transition-all duration-300 cursor-pointer bg-pink-main'
                : 'w-2 h-2 rounded-full transition-all duration-300 cursor-pointer bg-pink-main/30';
        });
        current = idx;
    }

    window.goToSlide = function(idx) {
        clearInterval(timer);
        showSlide(idx);
        startTimer();
    };

    function startTimer() {
        timer = setInterval(() => {
            showSlide((current + 1) % slides.length);
        }, 4000);
    }
    startTimer();
})();
</script>
<script src="/eventix/js/auth.js"></script>
<?php include 'includes/footer_scripts.php'; ?>
</body>
</html>