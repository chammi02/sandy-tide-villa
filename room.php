<?php
$pageTitle = 'Room details';
require_once __DIR__ . '/includes/functions.php';
$room = get_room((int)($_GET['id'] ?? 0));
if (!$room) { http_response_code(404); require __DIR__ . '/includes/header.php'; echo '<div class="container section"><div class="alert alert-error">Room not found.</div></div>'; require __DIR__ . '/includes/footer.php'; exit; }

// Guests can post a review (logged-in users only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    require_login(); csrf_check();
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1 || $rating > 5 || $comment === '' || mb_strlen($comment) > 500) {
        flash('error', 'Please give a rating (1-5) and a comment of up to 500 characters.');
    } else {
        db()->prepare('INSERT INTO reviews (user_id,room_id,rating,comment) VALUES (?,?,?,?)')
            ->execute([current_user()['user_id'], $room['room_id'], $rating, $comment]);
        flash('success', 'Thank you for your review!');
    }
    redirect('room.php?id=' . $room['room_id']);
}
require __DIR__ . '/includes/header.php';
$rv = db()->prepare('SELECT r.*, u.name FROM reviews r JOIN users u ON u.user_id=r.user_id WHERE room_id=? ORDER BY created_at DESC');
$rv->execute([$room['room_id']]); $reviews = $rv->fetchAll();
$avg = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
?>
<section class="page-head"><div class="container"><h1><?= e($room['room_name']) ?></h1><p><?= e($room['room_type']) ?> room · Hikkaduwa</p></div></section>
<div class="container section detail-grid">
  <div><img class="detail-img" src="<?= e(room_image($room['image'])) ?>" alt="<?= e($room['room_name']) ?>"></div>
  <div>
    <p class="lead"><?= e($room['description']) ?></p>
    <p><strong>Maximum guests:</strong> <?= (int)$room['max_guests'] ?></p>
    <h3>Facilities</h3>
    <ul class="chips"><?php foreach (explode(',', $room['facilities']) as $f): ?><li><?= e(trim($f)) ?></li><?php endforeach; ?></ul>
    <p class="big-price"><?= money($room['price_per_night']) ?> <small>per night</small></p>
    <?php if (seasonal_rates()): ?><p class="note">Seasonal rates apply during peak periods (e.g. mid-December to early January).</p><?php endif; ?>
    <a class="btn btn-primary" href="<?= e(url('book.php?room_id=' . (int)$room['room_id'])) ?>">Book this room</a>
    <a class="btn btn-outline" href="<?= e(url('rooms.php')) ?>">Back to rooms</a>
  </div>
</div>
<section class="section alt"><div class="container">
  <h2 class="section-title">Guest reviews <?= $reviews ? '(' . number_format($avg, 1) . ' ★ from ' . count($reviews) . ')' : '' ?></h2>
  <?php foreach ($reviews as $rv): ?>
    <div class="card review"><strong><?= e($rv['name']) ?></strong> <span class="stars"><?= str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']) ?></span>
    <p><?= e($rv['comment']) ?></p></div>
  <?php endforeach; if (!$reviews): ?><p>No reviews yet.</p><?php endif; ?>
  <?php if (current_user()): ?>
  <form method="post" class="card form-card" style="margin-top:1rem">
    <h3>Leave a review</h3><?= csrf_field() ?><input type="hidden" name="review" value="1">
    <label>Rating<select name="rating"><?php for ($i = 5; $i >= 1; $i--) echo "<option value=\"$i\">$i ★</option>"; ?></select></label>
    <label>Comment<textarea name="comment" maxlength="500" rows="3" required></textarea></label>
    <button class="btn btn-primary">Submit review</button>
  </form>
  <?php else: ?><p><a href="<?= e(url('login.php?next=room.php%3Fid%3D' . (int)$room['room_id'])) ?>">Log in</a> to leave a review.</p><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
