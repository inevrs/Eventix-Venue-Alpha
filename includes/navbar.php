<?php
$current = basename($_SERVER['PHP_SELF']);
$role = userRole();
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/icons.php';
$lang = getCurrentLanguage();
$languages = getSupportedLanguages();

$brand_link = '/eventix/index.php';
if ($role === 'customer') $brand_link = '/eventix/index.php';
if ($role === 'manager')  $brand_link = '/eventix/manager/dashboard.php';
if ($role === 'admin')    $brand_link = '/eventix/admin/dashboard.php';
?>
<nav class="fixed top-5 left-1/2 -translate-x-1/2 w-[calc(100%-80px)] max-w-7xl h-18 bg-surface border border-surface px-8 py-3 flex items-center justify-between rounded-full shadow-soft z-50 transition-all duration-300" id="mainNavbar">
    <a href="<?= $brand_link ?>" class="flex flex-col leading-none no-underline">
        <span class="font-sans text-2xl font-bold text-accent tracking-tight">Eventix</span>
    </a>

    <ul class="flex items-center gap-2 list-none m-0 p-0 relative z-10" id="navMenu">
        <?php if (!$role): ?>
            <li><a href="/eventix/index.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'index.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('explore_venues', $lang) ?></a></li>
            <li><a href="/eventix/about.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'about.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>">About Us</a></li>
        <?php elseif ($role === 'customer'): ?>
            <li><a href="/eventix/customer/venues.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'venues.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('venues', $lang) ?></a></li>
            <li><a href="/eventix/customer/my_bookings.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'my_bookings.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('my_bookings', $lang) ?></a></li>
            <li><a href="/eventix/customer/my_reviews.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'my_reviews.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('my_reviews', $lang) ?></a></li>
        <?php elseif ($role === 'manager'): ?>
            <li><a href="/eventix/manager/dashboard.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'dashboard.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('dashboard', $lang) ?></a></li>
            <li><a href="/eventix/manager/venues.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'venues.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('my_venues', $lang) ?></a></li>
            <li><a href="/eventix/manager/bookings.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'bookings.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('bookings', $lang) ?></a></li>
            <li><a href="/eventix/manager/earnings.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'earnings.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('earnings', $lang) ?></a></li>
        <?php elseif ($role === 'admin'): ?>
            <li><a href="/eventix/admin/dashboard.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'dashboard.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('dashboard', $lang) ?></a></li>
            <li><a href="/eventix/admin/users.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'users.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('users', $lang) ?></a></li>
            <li><a href="/eventix/admin/venues.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'venues.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('venues', $lang) ?></a></li>
            <li><a href="/eventix/admin/bookings.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'bookings.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('bookings', $lang) ?></a></li>
            <li><a href="/eventix/admin/payments.php" class="text-sm font-medium px-4 py-2 rounded-full transition-colors <?= $current === 'payments.php' ? 'bg-accent text-white' : 'text-muted hover:text-text relative z-10' ?>"><?= translate('payments', $lang) ?></a></li>
        <?php endif; ?>
    </ul>

    <div class="flex items-center gap-3">
        <button type="button" data-theme-toggle onclick="toggleTheme()" aria-label="Toggle theme" class="icon-btn border border-surface bg-surface text-text hover:border-accent hover:text-accent transition">
            <span data-theme-icon aria-hidden="true"></span>
        </button>
        <select onchange="setLanguage(this.value)" class="rounded-full border border-surface bg-surface px-3 py-2 text-sm text-text outline-none focus:border-accent transition">
            <?php foreach ($languages as $code => $label): ?>
                <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!$role): ?>
            <button onclick="openAuthModal()" class="text-sm font-medium bg-transparent border-none cursor-pointer text-muted hover:text-accent transition-colors"><?= translate('login', $lang) ?></button>
            <a href="/eventix/register.php" class="inline-block px-5 py-2.5 rounded-full text-sm font-semibold bg-accent text-white hover:bg-[#c72d6d] transition-all transform hover:-translate-y-px active:scale-95 no-underline"><?= translate('sign_up', $lang) ?></a>
        <?php else: ?>
            <div class="flex items-center gap-4">
                <a href="/eventix/profile.php" class="flex items-center gap-3 py-1.5 pl-4 pr-1.5 border border-surface rounded-full bg-surface transition-all no-underline text-text">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-accent flex items-center justify-center text-white">
                        <?php if (!empty($_SESSION['profile_picture']) && file_exists(__DIR__ . '/../' . $_SESSION['profile_picture'])): ?>
                            <img src="/eventix/<?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-sm font-semibold"><?= htmlspecialchars(substr($_SESSION['name'] ?? 'U', 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></span>
                </a>
                <ul class="flex items-center list-none p-0 m-0">
                    <li><a href="/eventix/logout.php" class="text-sm font-medium text-text hover:text-accent transition-colors no-underline"><?= translate('logout', $lang) ?></a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>

<?php if (!$role) include 'auth_modal.php'; ?>

<script src="/eventix/js/navbar.js"></script>