<?php
$pageTitle = 'Check Availability';
require __DIR__ . '/includes/header.php';

$in = $_GET['check_in'] ?? ''; $out = $_GET['check_out'] ?? ''; $guests = (int)($_GET['guests'] ?? 2);
$searched = isset($_GET['check_in']);
$results = []; $error = null;
if ($searched) {
    $error = date_range_error($in, $out);
    if (!$error && ($guests < 1 || $guests > 10)) $error = 'Number of guests must be between 1 and 10.';
    if (!$error) {
        // Only rooms that fit the party AND have no overlapping CONFIRMED booking
        $st = db()->prepare("SELECT * FROM rooms r WHERE room_status='active' AND max_guests >= :g
            AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.room_id=r.room_id AND b.booking_status='Confirmed'
                            AND b.check_in < :out AND b.check_out > :in)
            ORDER BY price_per_night");
        $st->execute([':g' => $guests, ':in' => $in, ':out' => $out]);
        $results = $st->fetchAll();
    }
}
?>
<section class="page-head"><div class="container"><h1>Check Room Availability</h1><p>Choose your dates and number of guests.</p></div></section>
<div class="container section">
  <form class="filter-bar" method="get" data-date-form>
    <label>Check-in<input type="date" name="check_in" value="<?= e($in) ?>" required></label>
    <label>Check-out<input type="date" name="check_out" value="<?= e($out) ?>" required></label>
    <label>Guests<input type="number" name="guests" min="1" max="10" value="<?= (int)$guests ?>" required></label>
    <button class="btn btn-primary" type="submit">Search</button>
  </form>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div>
  <?php elseif ($searched): $n = nights_between($in, $out); ?>
    <p><strong><?= count($results) ?></strong> room(s) available for <strong><?= $n ?></strong> night(s), <?= e($in) ?> to <?= e($out) ?>.</p>
    <?php if (!$results): ?><div class="alert alert-info">Sorry, no rooms are available for these dates. Please try different dates.</div><?php endif; ?>
    <div class="grid grid-3">
    <?php foreach ($results as $r): $total = calc_total((float)$r['price_per_night'], $in, $out); include __DIR__ . '/includes/room_card.php'; endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
