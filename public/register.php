<?php
$projectRoot = dirname(__DIR__);
set_include_path($projectRoot . PATH_SEPARATOR . get_include_path());

require_once 'includes/auth.php';
startSecureSession();
require_once 'includes/db.php';
require_once 'includes/validation.php';

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

$error   = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nameResult = validate_name($_POST['full_name'] ?? '');
    $emailResult = validate_email($_POST['email'] ?? '');
    $phoneResult = validate_phone($_POST['phone'] ?? '');
    $passwordResult = validate_password($_POST['password'] ?? '');
    $role = sanitize_input($_POST['role'] ?? 'customer');

    if (!$nameResult['valid']) {
        $error = $nameResult['message'];
    } elseif (!$emailResult['valid']) {
        $error = $emailResult['message'];
    } elseif (!$phoneResult['valid']) {
        $error = $phoneResult['message'];
    } elseif (!$passwordResult['valid']) {
        $error = $passwordResult['message'];
    } else {
        $name = $nameResult['value'];
        $email = $emailResult['value'];
        $phone = $phoneResult['value'];
        $password = $passwordResult['value'];

        $checkStmt = $connect->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $connect->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param('sssss', $name, $email, $phone, $hashedPassword, $role);

            if ($insertStmt->execute()) {
                $success = "Account created! <a href='/eventix/login.php' class='underline font-bold'>Sign in here</a>.";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Eventix</title>
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
    <div class="w-full lg:w-7/12 bg-surface flex flex-col justify-center items-center p-6 overflow-y-auto" data-aos="fade-left" data-aos-duration="1000">
        <div class="w-full max-w-[440px] auth-panel rounded-3xl p-6">
            <h1 class="font-[Playfair_Display] text-4xl text-pink-dark leading-[1.1] mb-2">Create Account</h1>
            <p class="text-text-muted text-sm mb-6">Fill in your details to get started.</p>

            <?php if ($error): ?>
                <div class="bg-red-500/10 text-red-400 px-4 py-3 rounded-xl text-sm mb-4"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-emerald-500/10 text-emerald-400 px-4 py-3 rounded-xl text-sm mb-4"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateRegister()">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-1.5">Full Name</label>
                        <input type="text" name="full_name" placeholder="Your full name" required class="form-input w-full px-4 py-2.5 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-1.5">Phone Number</label>
                        <input type="text" name="phone" placeholder="+60 12-345 6789" class="form-input w-full px-4 py-2.5 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-1.5">Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required class="form-input w-full px-4 py-2.5 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                </div>
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-1.5">Password</label>
                    <input type="password" name="password" placeholder="Min. 8 characters" required class="form-input w-full px-4 py-2.5 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10">
                </div>
                <div class="mb-6">
                    <label class="block text-[11px] font-semibold tracking-wider uppercase field-label mb-1.5">Register As</label>
                    <select name="role" class="form-select w-full px-4 py-2.5 rounded-xl text-sm outline-none focus:border-pink-main focus:ring-2 focus:ring-pink-main/10 cursor-pointer pr-10">
                        <option value="customer">Customer</option>
                        <option value="manager">Venue Manager</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-pink-main text-white py-3.5 rounded-full font-semibold text-sm hover:bg-pink-dark transition-all transform hover:-translate-y-px active:scale-95 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <span>Create Account</span>
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M13 5l7 7-7 7M20 12H4"/></svg>
                </button>
            </form>

            <p class="text-center mt-8 text-sm text-text-muted">
                Already have an account? <a href="/eventix/login.php" class="text-pink-main font-semibold hover:underline">Sign in</a>
            </p>
            <div class="text-center mt-4">
                <a href="/eventix/index.php" class="inline-flex items-center gap-2 text-sm text-text-muted hover:text-pink-main font-medium transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Continue as Guest
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function validateRegister() {
    const name = document.querySelector('input[name="full_name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const phone = document.querySelector('input[name="phone"]').value.trim();
    const pass = document.querySelector('input[name="password"]').value;
    let errors = [];

    if (name.length < 3) errors.push('Full name must be at least 3 characters.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Please enter a valid email address.');
    if (phone && !/^[\d\s\-\+]{8,15}$/.test(phone)) errors.push('Please enter a valid phone number.');
    if (pass.length < 8) errors.push('Password must be at least 8 characters.');

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
<?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
            </button>
        </form>

        <p class="text-center mt-8 text-sm text-text-muted">
            Already have an account? <a href="/eventix/login.php" class="text-pink-main font-semibold hover:underline">Sign in</a>
        </p>
    </div>
</div>