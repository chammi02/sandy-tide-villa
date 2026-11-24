<?php /* expects $r (room row) and optional $in, $out, $guests, $total */
$bookQuery = http_build_query(array_filter(['room_id' => $r['room_id'], 'check_in' => $in ?? null, 'check_out' => $out ?? null, 'guests' => $guests ?? null]));
?>
<article class="card room-card">
  <img src="<?= e(room_image($r['image'])) ?>" alt="<?= e($r['room_name']) ?>">
  <div class="card-body">
    <span class="tag"><?= e($r['room_type']) ?></span>
    <h3><?= e($r['room_name']) ?></h3>
    <p><?= e(mb_strimwidth($r['description'], 0, 110, '…')) ?></p>
    <p class="meta">👥 Up to <?= (int)$r['max_guests'] ?> guests</p>
    <p class="facilities"><?= e(implode(' • ', array_slice(explode(',', $r['facilities']), 0, 4))) ?></p>
    <div class="price-row">
      <div><strong><?= money($r['price_per_night']) ?></strong> <small>/ night</small>
      <?php if (isset($total)): ?><br><small>Total for stay: <strong><?= money($total) ?></strong></small><?php endif; ?></div>
    </div>
    <div class="actions">
      <a class="btn btn-outline btn-sm" href="<?= e(url('room.php?id=' . (int)$r['room_id'])) ?>">View Details</a>
      <a class="btn btn-primary btn-sm" href="<?= e(url('book.php?' . $bookQuery)) ?>">Book Now</a>
    </div>
  </div>
</article>
