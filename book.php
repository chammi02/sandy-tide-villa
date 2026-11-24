<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$rooms = db()->query("SELECT * FROM rooms WHERE room_status='active' ORDER BY price_per_night")->fetchAll();
$byId = array_column($rooms, null, 'room_id');

$f = [
  'room_id'  => (int)($_GET['room_id'] ?? 0),
  'check_in' => $_GET['check_in'] ?? '', 'check_out' => $_GET['check_out'] ?? '',
  'guests'   => (int)($_GET['guests'] ?? 2),
  'name' => $user['name'], 'email' => $user['email'], 'phone' => $user['contact'], 'requests' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = [
      'room_id' => (int)($_POST['room_id'] ?? 0), 'check_in' => $_POST['check_in'] ?? '', 'check_out' => $_POST['check_out'] ?? '',
      'guests' => (int)($_POST['guests'] ?? 0), 'name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''), 'requests' => trim($_POST['requests'] ?? ''),
    ];
    $room = $byId[$f['room_id']] ?? null;

    // ---- Server-side validation ----
    if (!$room) $errors[] = 'Please select a valid room.';
    if ($err = date_range_error($f['check_in'], $f['check_out'])) $errors[] = $err;
    if ($room && ($f['guests'] < 1 || $f['guests'] > (int)$room['max_guests'])) $errors[] = 'This room allows 1 to ' . (int)$room['max_guests'] . ' guests.';
    if (mb_strlen($f['name']) < 2)                         $errors[] = 'Please enter the guest name.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))   $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^\+?[0-9 ]{9,15}$/', $f['phone']))   $errors[] = 'Please enter a valid contact number.';
    if (mb_strlen($f['requests']) > 300)                   $errors[] = 'Special requests must be 300 characters or less.';

    if (!$errors && !is_room_available($room['room_id'], $f['check_in'], $f['check_out'])) {
        $errors[] = 'Sorry, this room is already confirmed for some of those dates. Please choose other dates or another room.';
    }
    if (!$errors) {
        $total = calc_total((float)$room['price_per_night'], $f['check_in'], $f['check_out']);   // price is NEVER taken from the browser
        db()->prepare("INSERT INTO bookings (user_id,room_id,guest_name,guest_email,guest_phone,guests,check_in,check_out,total_amount,special_requests,booking_status)
                       VALUES (?,?,?,?,?,?,?,?,?,?, 'Pending')")
            ->execute([$user['user_id'], $room['room_id'], $f['name'], $f['email'], $f['phone'], $f['guests'], $f['check_in'], $f['check_out'], $total, $f['requests'] ?: null]);
        flash('success', 'Booking request #' . db()->lastInsertId() . ' submitted! Total ' . money($total) . '. The villa will confirm it shortly.');
        redirect('my_bookings.php');
    }
}
$pageTitle = 'Book a Room';
require __DIR__ . '/includes/header.php';
$ratesJson = json_encode(array_map(fn($s) => ['s' => $s['start_date'], 'e' => $s['end_date'], 'm' => (float)$s['multiplier']], seasonal_rates()));
?>
<section class="page-head"><div class="container"><h1>Book Your Stay</h1><p>Submit a booking request — the villa will confirm it.</p></div></section>
<div class="container section narrow">
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="card form-card" id="bookingForm" novalidate
        data-rates="<?= e($ratesJson) ?>" data-api="<?= e(url('api/check_availability.php')) ?>">
    <?= csrf_field() ?>
    <label>Room
      <select name="room_id" id="room_id" required>
        <option value="">-- Select a room --</option>
        <?php foreach ($rooms as $r): ?>
          <option value="<?= (int)$r['room_id'] ?>" data-price="<?= e($r['price_per_night']) ?>" data-max="<?= (int)$r['max_guests'] ?>" <?= $f['room_id'] === (int)$r['room_id'] ? 'selected' : '' ?>>
            <?= e($r['room_name']) ?> — <?= money($r['price_per_night']) ?>/night (max <?= (int)$r['max_guests'] ?>)</option>
        <?php endforeach; ?>
      </select></label>
    <div class="two-col">
      <label>Check-in<input type="date" name="check_in" id="check_in" value="<?= e($f['check_in']) ?>" required></label>
      <label>Check-out<input type="date" name="check_out" id="check_out" value="<?= e($f['check_out']) ?>" required></label>
    </div>
    <label>Number of guests<input type="number" name="guests" id="guests" min="1" max="10" value="<?= (int)$f['guests'] ?>" required></label>
    <label>Guest name<input type="text" name="name" value="<?= e($f['name']) ?>" required></label>
    <div class="two-col">
      <label>Email<input type="email" name="email" value="<?= e($f['email']) ?>" required></label>
      <label>Contact number<input type="tel" name="phone" value="<?= e($f['phone']) ?>" required></label>
    </div>
    <label>Special requests (optional)<textarea name="requests" rows="2" maxlength="300"><?= e($f['requests']) ?></textarea></label>

    <div class="summary" id="summary" aria-live="polite">Select a room and dates to see your total.</div>
    <div id="formMessages" class="js-messages"></div>
    <button class="btn btn-primary" type="submit">Submit booking request</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
