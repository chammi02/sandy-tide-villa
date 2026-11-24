<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();

// Only this guest's bookings are ever queried (access control)
$st = db()->prepare('SELECT b.*, r.room_name FROM bookings b JOIN rooms r ON r.room_id=b.room_id WHERE b.user_id = ? ORDER BY b.booking_date DESC');
$st->execute([$user['user_id']]);
$bookings = $st->fetchAll();
$pageTitle = 'My Bookings';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1>My Bookings</h1></div></section>
<div class="container section">
  <?php if (!$bookings): ?>
    <div class="alert alert-info">You have no bookings yet. <a href="<?= e(url('rooms.php')) ?>">Browse rooms</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>#</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Nights</th><th>Guests</th><th>Total</th><th>Status</th><th>Booked on</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= (int)$b['booking_id'] ?></td><td><?= e($b['room_name']) ?></td><td><?= e($b['check_in']) ?></td><td><?= e($b['check_out']) ?></td>
        <td><?= nights_between($b['check_in'], $b['check_out']) ?></td><td><?= (int)$b['guests'] ?></td>
        <td><?= money($b['total_amount']) ?></td><td><?= status_badge($b['booking_status']) ?></td><td><?= e(substr($b['booking_date'], 0, 10)) ?></td>
        <td><?php if ($b['booking_status'] === 'Pending'): ?>
          <form method="post" action="<?= e(url('cancel_booking.php')) ?>" onsubmit="return confirm('Cancel this booking request?');">
            <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
            <button class="btn btn-danger btn-sm">Cancel</button></form>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
