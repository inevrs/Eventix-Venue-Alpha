<?php
$current = basename($_SERVER['PHP_SELF']);
$role = userRole();

$brand_link = '/eventix/';
if ($role === 'customer') $brand_link = '/eventix/customer/home.php';
if ($role === 'manager')  $brand_link = '/eventix/manager/dashboard.php';
if ($role === 'admin')    $brand_link = '/eventix/admin/dashboard.php';
?>
<nav class="navbar" id="mainNavbar">
    <a href="<?= $brand_link ?>" class="navbar-brand">
        <span class="brand-name">Eventix</span>
    </a>

    <ul class="navbar-links">
        <?php if (!$role): ?>
            <li><a href="/eventix/index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Explore Venues</a></li>
        <?php elseif ($role === 'customer'): ?>
            <li><a href="/eventix/customer/home.php" class="<?= $current === 'home.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="/eventix/customer/venues.php" class="<?= $current === 'venues.php' ? 'active' : '' ?>">Venues</a></li>
            <li><a href="/eventix/customer/my_bookings.php" class="<?= $current === 'my_bookings.php' ? 'active' : '' ?>">My Bookings</a></li>
        <?php elseif ($role === 'manager'): ?>
            <li><a href="/eventix/manager/dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="/eventix/manager/venues.php" class="<?= $current === 'venues.php' ? 'active' : '' ?>">My Venues</a></li>
            <li><a href="/eventix/manager/bookings.php" class="<?= $current === 'bookings.php' ? 'active' : '' ?>">Bookings</a></li>
            <li><a href="/eventix/manager/earnings.php" class="<?= $current === 'earnings.php' ? 'active' : '' ?>">Earnings</a></li>
        <?php elseif ($role === 'admin'): ?>
            <li><a href="/eventix/admin/dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="/eventix/admin/users.php" class="<?= $current === 'users.php' ? 'active' : '' ?>">Users</a></li>
            <li><a href="/eventix/admin/venues.php" class="<?= $current === 'venues.php' ? 'active' : '' ?>">Venues</a></li>
            <li><a href="/eventix/admin/bookings.php" class="<?= $current === 'bookings.php' ? 'active' : '' ?>">Bookings</a></li>
            <li><a href="/eventix/admin/payments.php" class="<?= $current === 'payments.php' ? 'active' : '' ?>">Payments</a></li>
        <?php endif; ?>
    </ul>

    <div class="navbar-right">
        <?php if (!$role): ?>
            <button onclick="openAuthModal()" class="btn-nav" style="background: transparent; border: none; cursor: pointer; font-size: 14px; font-weight: 500; font-family: inherit;">Log in</button>
            <a href="/eventix/register.php" class="btn btn-primary btn-sm" style="border-radius: 30px;">Sign up</a>
        <?php else: ?>
            <a href="/eventix/profile.php" class="user-menu-btn" title="View Profile">
                <span class="hamburger-icon">☰</span>
                <div class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($_SESSION['profile_picture']) && file_exists(__DIR__ . '/../' . $_SESSION['profile_picture'])): ?>
                        <img src="/eventix/<?= htmlspecialchars($_SESSION['profile_picture']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 16px;">👤</span>
                    <?php endif; ?>
                </div>
            </a>
            <ul class="navbar-links" style="margin-left: 16px;">
                <li><a href="/eventix/logout.php" style="color: var(--text); font-weight: 500;">Log out</a></li>
            </ul>
        <?php endif; ?>
    </div>
</nav>

<?php if (!$role) include 'auth_modal.php'; ?>

<script>
    // Dynamic Navbar Scroll Effect
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('mainNavbar');
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>
