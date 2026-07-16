<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/lang.php';
requireLogin('customer');

$lang = getCurrentLanguage();

$user_id = $_SESSION['user_id'];

$reviewsResult = mysqli_query($connect, "
    SELECT r.*, v.name AS venue_name, v.location
    FROM ratings r
    JOIN venues v ON r.venue_id=v.id
    WHERE r.user_id=$user_id
    ORDER BY r.created_at DESC
");

$reviews = [];
while ($row = mysqli_fetch_assoc($reviewsResult)) {
    $reviews[] = $row;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    $action = $_POST['action'];
    $review_id = (int)$_POST['review_id'];

    if ($action === 'delete') {
        $stmt = $connect->prepare('DELETE FROM ratings WHERE id=? AND user_id=?');
        $stmt->bind_param('ii', $review_id, $user_id);
        if ($stmt->execute()) {
            header('Location: my_reviews.php?deleted=1');
            exit();
        }
        $error = 'Unable to delete review. Please try again.';
    }
    elseif ($action === 'edit' && isset($_POST['rating'], $_POST['review_text'])) {
        $rating = max(1, min(5, (int)$_POST['rating']));
        $review_text = trim($_POST['review_text']);
        $stmt = $connect->prepare('UPDATE ratings SET rating=?, review=?, created_at=NOW() WHERE id=? AND user_id=?');
        $stmt->bind_param('isii', $rating, $review_text, $review_id, $user_id);
        if ($stmt->execute()) {
            header('Location: my_reviews.php?updated=1');
            exit();
        }
        $error = 'Unable to update review. Please try again.';
    }
}

if (isset($_GET['deleted'])) {
    $success = 'Review deleted successfully.';
} elseif (isset($_GET['updated'])) {
    $success = 'Review updated successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews — Eventix</title>
    <?php include '../includes/header_scripts.php'; ?>
</head>
<body class="text-text font-sans antialiased min-h-screen bg-[#fff8fb]">

<?php include '../includes/navbar.php'; ?>

<div class="max-w-7xl mx-auto px-6 py-10 mt-24">
    <div class="mb-10" data-aos="fade-down">
        <h1 class="font-[Playfair_Display] text-4xl text-pink-dark mb-3"><?= translate('my_reviews', $lang) ?></h1>
        <p class="text-text-muted text-sm"><?= translate('my_reviews_subtitle', $lang) ?></p>
    </div>

    <?php if ($error): ?><div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-6"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm mb-6"><?= $success ?></div><?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
        <div class="space-y-6">
            <?php if (empty($reviews)): ?>
                <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-soft text-center" data-aos="fade-up">
                    <div class="text-5xl mb-4"><?= getIcon('no_reviews') ?></div>
                    <p class="text-xl font-semibold text-pink-dark mb-3"><?= translate('no_reviews_yet', $lang) ?></p>
                    <p class="text-text-muted mb-6"><?= translate('leave_the_first_review', $lang) ?></p>
                    <a href="venues.php" class="inline-flex items-center justify-center rounded-full bg-pink-main px-6 py-3 text-sm font-semibold text-white hover:bg-pink-dark transition"><?= translate('write_first_review', $lang) ?></a>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft" data-aos="fade-up">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm text-text-muted"><?= date('j M Y', strtotime($review['created_at'])) ?></p>
                            <h2 class="font-semibold text-lg text-text mt-2"><?= htmlspecialchars($review['venue_name']) ?></h2>
                            <p class="text-xs text-text-muted"><?= htmlspecialchars($review['location']) ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-pink-light px-3 py-1 text-xs font-semibold text-pink-dark">⭐ <?= $review['rating'] ?>/5</span>
                            <form method="POST" class="inline">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="text-text mt-4 mb-4 whitespace-pre-line"><?= htmlspecialchars($review['review']) ?></p>

                    <details class="group rounded-3xl border border-gray-100 bg-gray-50 p-4">
                        <summary class="cursor-pointer font-semibold text-text">Edit review</summary>
                        <form method="POST" class="mt-4 space-y-4">
                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                            <input type="hidden" name="action" value="edit">
                            <div class="grid gap-4 sm:grid-cols-[120px_1fr] items-center">
                                <label class="text-sm font-semibold text-text-muted">Rating</label>
                                <select name="rating" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-text focus:border-pink-main focus:ring-2 focus:ring-pink-main/10 outline-none">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>" <?= $review['rating'] == $i ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-text-muted mb-2">Review</label>
                                <textarea name="review_text" rows="4" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-text focus:border-pink-main focus:ring-2 focus:ring-pink-main/10 outline-none"><?= htmlspecialchars($review['review']) ?></textarea>
                            </div>
                            <button type="submit" class="rounded-full bg-pink-main px-6 py-3 text-sm font-semibold text-white hover:bg-pink-dark transition">Save changes</button>
                        </form>
                    </details>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <aside class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft" data-aos="fade-up" data-aos-delay="100">
                <h2 class="font-[Playfair_Display] text-2xl text-pink-dark mb-3">Quick actions</h2>
                <div class="grid gap-3">
                    <a href="venues.php" class="block rounded-2xl border border-gray-200 bg-pink-light px-4 py-3 text-sm font-semibold text-pink-dark hover:bg-pink-light/90 transition">Book a venue</a>
                    <a href="my_bookings.php" class="block rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-text hover:bg-gray-50 transition">View saved / previous bookings</a>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-soft" data-aos="fade-up" data-aos-delay="200">
                <h2 class="font-[Playfair_Display] text-2xl text-pink-dark mb-3">Review tips</h2>
                <ul class="space-y-3 text-sm text-text-muted">
                    <li>• Be honest about the venue experience.</li>
                    <li>• Mention what worked well for your event.</li>
                    <li>• Keep feedback constructive and clear.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php include '../includes/footer_scripts.php'; ?>
</body>
</html>
