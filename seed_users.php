<?php
/**
 * Seed script: 40 fake customers with bookings, payments & reviews
 * Password for all: "password"
 * Run once via: php seed_users.php
 */

require_once __DIR__ . '/includes/db.php';

$hash = password_hash('password', PASSWORD_DEFAULT);

// ── 40 Generic customer names ──────────────────────────────────────────
$customers = [
    ['James Lee',        'james@gmail.com',        '+60121110001'],
    ['Sarah Tan',        'sarah@gmail.com',        '+60121110002'],
    ['David Wong',       'david@gmail.com',        '+60121110003'],
    ['Emily Chen',       'emily@gmail.com',        '+60121110004'],
    ['Michael Lim',      'michael@gmail.com',      '+60121110005'],
    ['Jessica Ng',       'jessica@gmail.com',      '+60121110006'],
    ['Daniel Kumar',     'daniel@gmail.com',       '+60121110007'],
    ['Rachel Ahmad',     'rachel@gmail.com',       '+60121110008'],
    ['Ryan Chong',       'ryan@gmail.com',         '+60121110009'],
    ['Sophia Abdullah',  'sophia@gmail.com',       '+60121110010'],
    ['Kevin Yap',        'kevin@gmail.com',        '+60121110011'],
    ['Amanda Soh',       'amanda@gmail.com',       '+60121110012'],
    ['Justin Ooi',       'justin@gmail.com',       '+60121110013'],
    ['Natalie Goh',      'natalie@gmail.com',      '+60121110014'],
    ['Brandon Teo',      'brandon@gmail.com',      '+60121110015'],
    ['Melissa Raj',      'melissa@gmail.com',      '+60121110016'],
    ['Andrew Sim',       'andrew@gmail.com',       '+60121110017'],
    ['Chloe Foo',        'chloe@gmail.com',        '+60121110018'],
    ['Marcus Koh',       'marcus@gmail.com',       '+60121110019'],
    ['Hannah Yeoh',      'hannah@gmail.com',       '+60121110020'],
    ['Timothy Ong',      'timothy@gmail.com',      '+60121110021'],
    ['Grace Lau',        'grace@gmail.com',        '+60121110022'],
    ['Patrick Chin',     'patrick@gmail.com',      '+60121110023'],
    ['Victoria Phua',    'victoria@gmail.com',     '+60121110024'],
    ['Samuel Ho',        'samuel@gmail.com',       '+60121110025'],
    ['Olivia Nair',      'olivia@gmail.com',       '+60121110026'],
    ['Jason Yong',       'jason@gmail.com',        '+60121110027'],
    ['Ashley Gan',       'ashley@gmail.com',       '+60121110028'],
    ['Christopher Loh',  'christopher@gmail.com',  '+60121110029'],
    ['Stephanie Wee',    'stephanie@gmail.com',    '+60121110030'],
    ['Nicholas Tan',     'nicholas@gmail.com',     '+60121110031'],
    ['Vanessa Khoo',     'vanessa@gmail.com',      '+60121110032'],
    ['Alexander Chia',   'alexander@gmail.com',    '+60121110033'],
    ['Isabelle Low',     'isabelle@gmail.com',     '+60121110034'],
    ['Benjamin Heng',    'benjamin@gmail.com',     '+60121110035'],
    ['Audrey Teh',       'audrey@gmail.com',       '+60121110036'],
    ['Jonathan Liew',    'jonathan@gmail.com',     '+60121110037'],
    ['Megan Soo',        'megan@gmail.com',        '+60121110038'],
    ['Dylan Chua',       'dylan@gmail.com',        '+60121110039'],
    ['Fiona Leong',      'fiona@gmail.com',        '+60121110040'],
];

// ── Venue IDs from database ────────────────────────────────────────────
$venue_ids = [4, 5, 6, 7, 8, 9, 11, 13, 16, 17, 18, 19];

// ── Venue prices (for payments) ─────────────────────────────────────────
$venue_prices = [
    4  => 1000.00,
    5  => 2000.00,
    6  => 900.00,
    7  => 700.00,
    8  => 1500.00,
    9  => 2000.00,
    11 => 3000.00,
    13 => 3000.00,
    16 => 1500.00,
    17 => 2000.00,
    18 => 3500.00,
    19 => 3000.00,
];

// ── Payment methods ─────────────────────────────────────────────────────
$methods = ['Online Banking', 'Credit Card', 'Debit Card', 'eWallet'];

// ── Reviews pool (realistic, diverse) ────────────────────────────────────
$reviews_pool = [
    [5, "Absolutely stunning venue! The ambiance was perfect for our corporate dinner. Staff was incredibly helpful and responsive. Would definitely book again."],
    [5, "We hosted our wedding reception here and it was magical. The views were breathtaking and the space was beautifully maintained. Highly recommend!"],
    [4, "Great venue with excellent facilities. The lighting setup was impressive. Only minor issue was parking, but overall a fantastic experience."],
    [5, "Best event space in KL hands down. Our product launch was a massive success thanks to the amazing setup and professional staff."],
    [4, "Very spacious and well-maintained. The acoustics were great for our live band. Food catering options could be more diverse though."],
    [5, "Hosted a birthday party here and everyone was blown away. The panoramic views at night are simply gorgeous. Worth every ringgit."],
    [3, "Decent venue for the price. The space is nice but the air conditioning was a bit inconsistent during our event. Staff was friendly though."],
    [5, "This is our go-to venue for company events. Every time we book, the experience just gets better. Exceptional service and beautiful decor."],
    [4, "Really enjoyed the outdoor space. Perfect for our garden-themed engagement party. The greenery and natural lighting were beautiful."],
    [5, "Incredible venue! The setup was seamless and the event coordinator was super professional. Our guests couldn't stop complimenting the space."],
    [4, "Booked for our annual gala dinner. The ballroom was elegant and the audiovisual equipment was top-notch. Minor delay in setup but all went smoothly."],
    [5, "Absolutely loved this place. The rooftop setting with KLCC views made our anniversary dinner unforgettable. Staff went above and beyond."],
    [3, "Good space overall but felt a bit cramped for 200 guests. The venue looks much bigger in photos. That said, the food was excellent."],
    [4, "Professional team, beautiful venue, and great location. Our seminar went perfectly. Would appreciate more parking options nearby."],
    [5, "My daughter's 21st birthday party was held here and it was perfect! The DJ area was well set up and the dance floor was spacious."],
    [4, "Solid choice for mid-sized events. The decor options they offer are beautiful and reasonably priced. Booking process was smooth."],
    [5, "We've used this venue three times now for different company events and it never disappoints. The consistency in quality is remarkable."],
    [4, "Beautiful architecture and great photo opportunities throughout the venue. Our guests loved the aesthetic. Catering was delicious too."],
    [3, "Average experience. The venue itself is lovely but communication before the event was slow. During the event everything was fine though."],
    [5, "Hosted our charity gala here and raised record funds! The venue set the perfect tone. Elegant, spacious, and the staff was phenomenal."],
    [4, "Great for intimate gatherings. The cozy atmosphere made our family reunion special. Appreciated the flexible timing arrangements."],
    [5, "This venue exceeded all our expectations! From the initial site visit to the event day, everything was handled with utmost professionalism."],
    [4, "Very good venue for corporate events. Modern facilities, great WiFi, and the breakout rooms were perfect for our workshop sessions."],
    [5, "Our engagement ceremony was held here and it was dreamy. The natural light during golden hour was perfect for photos. Love this place!"],
    [3, "Nice venue but the pricing is on the higher side for what you get. The view does make up for it though. Staff could be more attentive."],
    [5, "Absolutely perfect for our company's year-end party. The team building activities in the garden area were fantastic. Best venue in Selangor!"],
    [4, "Hosted a product showcase here. The open layout was great for our exhibition booths. Sound system was crystal clear. Highly recommend."],
    [5, "From the moment we walked in, we knew this was the venue for our wedding. The garden ceremony was picture-perfect. Thank you for making our day special!"],
    [4, "Good venue with attentive staff. The event coordination team helped us plan everything down to the last detail. Very impressed."],
    [5, "A hidden gem! We discovered this venue through a friend's recommendation and weren't disappointed. The sunset views are unreal."],
    [4, "Booked for our quarterly town hall meeting. The presentation facilities were excellent and the refreshments were top quality."],
    [5, "Our family's Hari Raya open house was held here and it was the talk of the family. Everyone loved the spacious garden and the modern interior."],
    [3, "The venue is beautiful but navigating there via public transport is tricky. Grab was our best bet. Once you're there, it's lovely though."],
    [4, "Excellent for networking events. The layout encourages mingling and the cocktail area was perfectly set up. Great experience overall."],
    [5, "I cannot say enough good things about this venue. Our team retreat was productive and enjoyable. The serene environment helped everyone relax."],
    [4, "Celebrated our 10th wedding anniversary here. The intimate setting was just what we wanted. The chef prepared a special menu for us!"],
    [5, "Premier venue for KL events. We compared many options and this one stood out for its quality, service, and value. No regrets at all."],
    [4, "Smooth booking experience and even smoother event day. The team handled last-minute changes gracefully. Will definitely return."],
    [3, "It's a good venue but not exceptional for the price point. The space is nice and the views are great, just expected a bit more in terms of service."],
    [5, "Used this for our influencer meet and greet. The instagrammable spots throughout the venue were amazing. Content creators loved it!"],
    [4, "Very well-maintained venue with attention to detail. The flower arrangements and table settings were elegant. Perfect for formal events."],
    [5, "Our company just had its best annual dinner ever at this venue. The food, the atmosphere, the service — everything was 10/10."],
    [4, "Great venue for workshops and training sessions. The natural lighting reduces fatigue and the breakout spaces were very useful."],
    [5, "This venue made our graduation celebration truly special. The whole family was impressed by the sophistication and warmth of the place."],
    [3, "Adequate space for our needs. Could use some modernization in the restroom area. Otherwise, the main hall and garden are lovely."],
    [4, "Hosted a baby shower here and it was adorable. The event team helped with decoration and it turned out beautifully. Thank you!"],
    [5, "If you're looking for a venue that combines luxury with warmth, this is it. Our guests felt pampered and the entire evening was flawless."],
    [4, "Reliable venue for recurring corporate events. We book quarterly and the team always remembers our preferences. That's real service."],
    [5, "Dream venue alert! Our ROM ceremony here was straight out of a fairy tale. The garden at sunset was pure magic. Forever grateful."],
    [4, "Nicely located and easy to get to. The venue exceeded expectations for our friend's surprise party. The private room was perfect for our group size."],
];

// ── Booking notes pool ──────────────────────────────────────────────────
$notes_pool = [
    'Corporate annual dinner for 80 people with live band setup.',
    'Wedding reception - need fairy lights and floral arch.',
    'Birthday celebration with DJ and dance floor required.',
    'Product launch event with presentation area and cocktail setup.',
    'Family reunion gathering, buffet style catering.',
    'Engagement ceremony - garden setup preferred.',
    'Team building event with breakout sessions.',
    'Charity gala dinner with auction setup.',
    'Year-end company party, casual dress code.',
    'Graduation celebration for 50 guests.',
    'Baby shower with themed decorations.',
    'Anniversary dinner, intimate setting for 30 guests.',
    'Networking event with cocktail stations.',
    'Seminar and workshop for 100 attendees.',
    'Influencer meetup with photo booth areas.',
    'Hari Raya open house celebration.',
    'ROM ceremony followed by dinner reception.',
    'Company quarterly town hall meeting.',
    'Press conference and media event.',
    'Farewell party for departing colleagues.',
];

$inserted_users = 0;
$inserted_bookings = 0;
$inserted_payments = 0;
$inserted_reviews = 0;

echo "Starting seed process...\n\n";

// ── Insert Users ────────────────────────────────────────────────────────
$user_ids = [];
foreach ($customers as $c) {
    // Skip if email already exists
    $email_escaped = mysqli_real_escape_string($connect, $c[1]);
    $exists = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM users WHERE email='$email_escaped'"));
    if ($exists) {
        $user_ids[] = $exists['id'];
        echo "  Skipped (exists): {$c[0]}\n";
        continue;
    }

    $name = mysqli_real_escape_string($connect, $c[0]);
    $email = $email_escaped;
    $phone = mysqli_real_escape_string($connect, $c[2]);

    $sql = "INSERT INTO users (full_name, email, phone, password, role, created_at)
            VALUES ('$name', '$email', '$phone', '$hash', 'customer', NOW() - INTERVAL FLOOR(RAND()*90) DAY)";
    if (mysqli_query($connect, $sql)) {
        $user_ids[] = mysqli_insert_id($connect);
        $inserted_users++;
        echo "  Added user: {$c[0]}\n";
    } else {
        echo "  ERROR inserting {$c[0]}: " . mysqli_error($connect) . "\n";
    }
}

echo "\nUsers inserted: $inserted_users\n";
echo "Total user IDs available: " . count($user_ids) . "\n\n";

// ── Insert Bookings + Payments ──────────────────────────────────────────
// Each user gets 1-3 bookings
$booking_ids = []; // store [booking_id => [user_id, venue_id]]

foreach ($user_ids as $uid) {
    $num_bookings = rand(1, 3);
    for ($b = 0; $b < $num_bookings; $b++) {
        $vid = $venue_ids[array_rand($venue_ids)];
        $price = $venue_prices[$vid];

        // Random dates spread across past 6 months to 2 months ahead
        $days_offset = rand(-180, 60);
        $start = date('Y-m-d', strtotime("+{$days_offset} days"));
        $duration = rand(1, 3);
        $end = date('Y-m-d', strtotime($start . " +{$duration} days"));
        $guests = rand(20, 300);
        $note = mysqli_real_escape_string($connect, $notes_pool[array_rand($notes_pool)]);

        // Mix of statuses: 60% confirmed, 25% pending, 15% cancelled
        $rand_status = rand(1, 100);
        if ($rand_status <= 60) $status = 'confirmed';
        elseif ($rand_status <= 85) $status = 'pending';
        else $status = 'cancelled';

        $created_at = date('Y-m-d H:i:s', strtotime($start . " -{$days_diff} days", strtotime("-" . rand(5, 30) . " days", strtotime($start))));

        $sql = "INSERT INTO bookings (user_id, venue_id, start_date, end_date, guest_count, notes, status, created_at)
                VALUES ($uid, $vid, '$start', '$end', $guests, '$note', '$status', '$created_at')";
        if (mysqli_query($connect, $sql)) {
            $bid = mysqli_insert_id($connect);
            $booking_ids[$bid] = ['user_id' => $uid, 'venue_id' => $vid, 'price' => $price * ($duration + 1), 'status' => $status];
            $inserted_bookings++;

            // If confirmed or pending → insert payment
            if ($status !== 'cancelled') {
                $total = $price * ($duration + 1);
                $method = $methods[array_rand($methods)];
                $pay_status = 'paid';
                $pay_sql = "INSERT INTO payments (booking_id, amount, method, status, paid_at)
                            VALUES ($bid, $total, '$method', '$pay_status', '$created_at')";
                if (mysqli_query($connect, $pay_sql)) {
                    $inserted_payments++;
                }
            }
        }
    }
}

echo "Bookings inserted: $inserted_bookings\n";
echo "Payments inserted: $inserted_payments\n\n";

// ── Insert Reviews ──────────────────────────────────────────────────────
// Only confirmed bookings get reviews (about 70% of them)
$review_index = 0;
foreach ($booking_ids as $bid => $info) {
    if ($info['status'] !== 'confirmed') continue;
    if (rand(1, 100) > 70) continue; // 70% chance of leaving a review

    $r = $reviews_pool[$review_index % count($reviews_pool)];
    $review_index++;

    $rating = $r[0];
    $review_text = mysqli_real_escape_string($connect, $r[1]);
    $uid = $info['user_id'];
    $vid = $info['venue_id'];

    // Check if user already reviewed this venue
    $exists = mysqli_fetch_assoc(mysqli_query($connect, "SELECT id FROM ratings WHERE user_id=$uid AND venue_id=$vid"));
    if ($exists) continue;

    $created = date('Y-m-d H:i:s', strtotime("-" . rand(1, 60) . " days"));
    $sql = "INSERT INTO ratings (user_id, venue_id, rating, review, created_at)
            VALUES ($uid, $vid, $rating, '$review_text', '$created')";
    if (mysqli_query($connect, $sql)) {
        $inserted_reviews++;
    }
}

echo "Reviews inserted: $inserted_reviews\n\n";
echo "=== SEED COMPLETE ===\n";
echo "Summary:\n";
echo "  Users:    $inserted_users\n";
echo "  Bookings: $inserted_bookings\n";
echo "  Payments: $inserted_payments\n";
echo "  Reviews:  $inserted_reviews\n";
echo "\nAll users can log in with their email and password: \"password\"\n";
?>
