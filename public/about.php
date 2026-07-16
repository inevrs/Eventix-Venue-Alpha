<?php
$projectRoot = dirname(__DIR__);
set_include_path($projectRoot . PATH_SEPARATOR . get_include_path());

require_once 'includes/auth.php';
startSecureSession();
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/icons.php';

$lang = getCurrentLanguage();

// Pull live stats
$total_venues  = (int)mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM venues WHERE status='active'"))[0];
$total_bookings = (int)mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM bookings"))[0];
$total_customers = (int)mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM users WHERE role='customer'"))[0];
$total_reviews = (int)mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) FROM ratings"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — Eventix</title>
    <meta name="description" content="Learn about Eventix — Malaysia's premier venue booking platform. Discover our story, mission, and the team behind seamless event planning.">
    <?php include 'includes/header_scripts.php'; ?>
    <style>
        .about-hero {
            background: linear-gradient(135deg, var(--pink-light) 0%, #fff 50%, var(--pink-light) 100%);
            position: relative;
            overflow: hidden;
        }
        .about-hero::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--pink-main) 0%, transparent 70%);
            opacity: 0.06;
            border-radius: 50%;
        }
        .about-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            opacity: 0.05;
            border-radius: 50%;
        }
        .timeline-line {
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--pink-main), var(--accent));
            transform: translateX(-50%);
        }
        @media (max-width: 768px) {
            .timeline-line {
                left: 24px;
            }
        }
        .value-card {
            transition: all 0.3s ease;
        }
        .value-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .stat-number {
            background: linear-gradient(135deg, var(--pink-main), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="text-text font-sans antialiased min-h-screen">

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<div class="about-hero pt-36 pb-20">
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <p class="text-accent text-xs tracking-widest uppercase mb-4 font-semibold">— About Eventix</p>
            <h1 class="font-[Playfair_Display] text-5xl md:text-6xl leading-tight text-accent mb-6">
                Making Every Event <span class="text-accent-dark">Unforgettable</span>
            </h1>
            <p class="text-text-muted text-lg leading-relaxed max-w-2xl mx-auto">
                Eventix is Malaysia's premier venue booking platform — connecting event planners with extraordinary spaces to create moments that matter.
            </p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-6">

    <!-- Our Story Section -->
    <section class="py-16" data-aos="fade-up">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-accent text-xs tracking-widest uppercase mb-3 font-semibold">— Our Story</p>
                <h2 class="font-[Playfair_Display] text-4xl text-accent mb-6">Born From a Simple Frustration</h2>
                <p class="text-text-muted leading-relaxed mb-4">
                    Eventix was born in 2026 out of a simple frustration: planning an event in Malaysia shouldn't be this hard. Our founders experienced firsthand the struggle of finding the right venue — endless phone calls, unclear pricing, no real photos, and zero transparency.
                </p>
                <p class="text-text-muted leading-relaxed mb-4">
                    What started as a university project quickly became something much bigger. We realized that venue owners struggled just as much — managing bookings on paper, juggling WhatsApp messages, and losing track of payments. Both sides needed a better system.
                </p>
                <p class="text-text-muted leading-relaxed">
                    So we built Eventix — a platform that bridges the gap between customers looking for the perfect venue and managers running those spaces. One platform. Zero hassle. Just beautiful events.
                </p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-soft" data-aos="fade-left" data-aos-delay="100">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center text-2xl flex-shrink-0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-text mb-1">Started as a Vision</h3>
                            <p class="text-sm text-text-muted">A university project idea to modernize how Malaysians discover and book event venues.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center text-2xl flex-shrink-0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-text mb-1">Built by Event Lovers</h3>
                            <p class="text-sm text-text-muted">Created by a team who understands the Malaysian event culture and its unique needs.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center text-2xl flex-shrink-0">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-text mb-1">Growing Every Day</h3>
                            <p class="text-sm text-text-muted">From KL to Selangor and beyond — we're expanding to connect more venues with more customers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Stats Section -->
    <section class="py-16" data-aos="fade-up">
        <div class="bg-white border border-gray-100 rounded-3xl p-10 shadow-soft">
            <div class="text-center mb-8">
                <p class="text-accent text-xs tracking-widest uppercase mb-3 font-semibold">— Eventix in Numbers</p>
                <h2 class="font-[Playfair_Display] text-3xl text-accent">Our Growing Community</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div data-aos="zoom-in" data-aos-delay="0">
                    <div class="stat-number font-[Playfair_Display] text-5xl font-bold"><?= $total_venues ?>+</div>
                    <div class="text-[11px] tracking-widest uppercase text-text-muted mt-2 font-semibold">Active Venues</div>
                </div>
                <div data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-number font-[Playfair_Display] text-5xl font-bold"><?= $total_customers ?>+</div>
                    <div class="text-[11px] tracking-widest uppercase text-text-muted mt-2 font-semibold">Happy Customers</div>
                </div>
                <div data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-number font-[Playfair_Display] text-5xl font-bold"><?= $total_bookings ?>+</div>
                    <div class="text-[11px] tracking-widest uppercase text-text-muted mt-2 font-semibold">Bookings Made</div>
                </div>
                <div data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-number font-[Playfair_Display] text-5xl font-bold"><?= $total_reviews ?>+</div>
                    <div class="text-[11px] tracking-widest uppercase text-text-muted mt-2 font-semibold">Reviews Written</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-16" data-aos="fade-up">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-soft value-card" data-aos="fade-right">
                <div class="w-14 h-14 rounded-2xl bg-pink-light flex items-center justify-center mb-6">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                </div>
                <h3 class="font-[Playfair_Display] text-2xl text-accent mb-4">Our Mission</h3>
                <p class="text-text-muted leading-relaxed">
                    To simplify event planning in Malaysia by providing a seamless, transparent, and delightful venue booking experience. We believe everyone deserves access to great venues — whether you're planning a wedding, a corporate retreat, or an intimate birthday celebration.
                </p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-soft value-card" data-aos="fade-left" data-aos-delay="100">
                <div class="w-14 h-14 rounded-2xl bg-pink-light flex items-center justify-center mb-6">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <h3 class="font-[Playfair_Display] text-2xl text-accent mb-4">Our Vision</h3>
                <p class="text-text-muted leading-relaxed">
                    To become Southeast Asia's most trusted venue marketplace — where every event planner finds their dream space in minutes, and every venue owner has the tools to thrive. We envision a world where booking a venue is as easy as booking a flight.
                </p>
            </div>
        </div>
    </section>

    <!-- Why Eventix -->
    <section class="py-16" data-aos="fade-up">
        <div class="text-center mb-12">
            <p class="text-accent text-xs tracking-widest uppercase mb-3 font-semibold">— Why Eventix?</p>
            <h2 class="font-[Playfair_Display] text-4xl text-accent mb-4">What Makes Us Different</h2>
            <p class="text-text-muted max-w-2xl mx-auto">We're not just another booking platform. Here's why thousands of Malaysians trust Eventix for their events.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="0">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Transparent Pricing</h3>
                <p class="text-sm text-text-muted leading-relaxed">No hidden fees, no surprises. Every venue displays clear pricing per day so you can plan your budget with confidence.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="50">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Real Reviews</h3>
                <p class="text-sm text-text-muted leading-relaxed">Every review comes from a verified booking. Read honest feedback from real customers who've experienced the venue firsthand.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Secure Payments</h3>
                <p class="text-sm text-text-muted leading-relaxed">Your payments are safe with our proof-based verification system. Upload proof, manager confirms — simple and secure.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="150">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Instant Booking</h3>
                <p class="text-sm text-text-muted leading-relaxed">Browse, compare, and book venues in minutes — not days. Our streamlined process saves you time and energy.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="200">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Manager Dashboard</h3>
                <p class="text-sm text-text-muted leading-relaxed">Venue owners get powerful tools — manage bookings, track earnings, respond to reviews, and grow their business effortlessly.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft value-card" data-aos="fade-up" data-aos-delay="250">
                <div class="w-12 h-12 rounded-2xl bg-pink-light flex items-center justify-center mb-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <h3 class="font-semibold text-text text-lg mb-2">Flexible Add-ons</h3>
                <p class="text-sm text-text-muted leading-relaxed">Customize your booking with premium add-ons like audiovisual gear, custom lighting, and event planning services.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16" data-aos="fade-up">
        <div class="text-center mb-12">
            <p class="text-accent text-xs tracking-widest uppercase mb-3 font-semibold">— How It Works</p>
            <h2 class="font-[Playfair_Display] text-4xl text-accent mb-4">Simple Steps to Your Perfect Event</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center" data-aos="fade-up" data-aos-delay="0">
                <div class="w-16 h-16 rounded-full bg-accent text-white flex items-center justify-center mx-auto mb-4 text-2xl font-[Playfair_Display] font-bold shadow-md">1</div>
                <h3 class="font-semibold text-text mb-2">Browse Venues</h3>
                <p class="text-sm text-text-muted">Explore our curated selection of venues across Malaysia with real photos and honest reviews.</p>
            </div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 rounded-full bg-accent text-white flex items-center justify-center mx-auto mb-4 text-2xl font-[Playfair_Display] font-bold shadow-md">2</div>
                <h3 class="font-semibold text-text mb-2">Book Your Date</h3>
                <p class="text-sm text-text-muted">Pick your dates, select add-ons, and submit your booking request in just a few clicks.</p>
            </div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 rounded-full bg-accent text-white flex items-center justify-center mx-auto mb-4 text-2xl font-[Playfair_Display] font-bold shadow-md">3</div>
                <h3 class="font-semibold text-text mb-2">Upload Payment</h3>
                <p class="text-sm text-text-muted">Pay via your preferred method and upload a screenshot as proof. Simple and transparent.</p>
            </div>
            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 rounded-full bg-accent text-white flex items-center justify-center mx-auto mb-4 text-2xl font-[Playfair_Display] font-bold shadow-md">4</div>
                <h3 class="font-semibold text-text mb-2">Get Confirmed</h3>
                <p class="text-sm text-text-muted">The venue manager reviews your booking and payment proof, then confirms your reservation.</p>
            </div>
        </div>
    </section>

    <!-- Contact / Get In Touch -->
    <section class="py-16" data-aos="fade-up">
        <div class="bg-white border border-gray-100 rounded-3xl p-10 shadow-soft">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="text-accent text-xs tracking-widest uppercase mb-3 font-semibold">— Get In Touch</p>
                    <h2 class="font-[Playfair_Display] text-3xl text-accent mb-4">We'd Love to Hear From You</h2>
                    <p class="text-text-muted leading-relaxed mb-6">
                        Whether you're a customer looking for the perfect venue or a venue owner wanting to join our platform — we're here to help.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-pink-light flex items-center justify-center flex-shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-text-muted uppercase tracking-wider font-semibold">Email</p>
                                <p class="text-text font-medium">hello@eventix.com</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-pink-light flex items-center justify-center flex-shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-text-muted uppercase tracking-wider font-semibold">Phone</p>
                                <p class="text-text font-medium">+60 3-1234 5678</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-pink-light flex items-center justify-center flex-shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--pink-main)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-text-muted uppercase tracking-wider font-semibold">Location</p>
                                <p class="text-text font-medium">Kuala Lumpur, Malaysia</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-pink-light to-white rounded-2xl p-8 text-center">
                    <div class="font-[Playfair_Display] text-6xl text-accent mb-4">Event<span class="text-accent-dark">ix</span></div>
                    <p class="text-text-muted text-sm mb-6">Your events. Our platform. Unforgettable experiences.</p>
                    <a href="/eventix/index.php" class="inline-block bg-accent text-white px-8 py-3 rounded-full font-semibold text-sm hover:bg-[#c72d6d] active:scale-95 transition-all shadow-md">
                        Start Exploring Venues
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
