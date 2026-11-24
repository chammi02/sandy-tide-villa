<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Admin Dashboard';
$pdo = db();
$counts = array_fill_keys(['Pending', 'Confirmed', 'Cancelled'], 0);
foreach ($pdo->query('SELECT booking_status s, COUNT(*) c FROM bookings GROUP BY booking_status') as $row) $counts[$row['s']] = (int)$row['c'];
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE booking_status='Confirmed'")->fetchColumn();
$rooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE room_status='active'")->fetchColumn();
$guests = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='guest'")->fetchColumn();
$recent = $pdo->query('SELECT b.*, r.room_name FROM bookings b JOIN rooms r ON r.room_id=b.room_id ORDER BY b.booking_date DESC LIMIT 6')->fetchAll();
$popular = $pdo->query("SELECT r.room_name, COUNT(*) c FROM bookings b JOIN rooms r ON r.room_id=b.room_id WHERE b.booking_status<>'Cancelled' GROUP BY r.room_id ORDER BY c DESC LIMIT 3")->fetchAll();
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container section">
  <h1>Administrator Dashboard</h1>
  <div class="grid grid-4 stats">
    <div class="card stat"><span><?= $counts['Pending'] ?></span>Pending bookings</div>
    <div class="card stat"><span><?= $counts['Confirmed'] ?></span>Confirmed bookings</div>
    <div class="card stat"><span><?= $counts['Cancelled'] ?></span>Cancelled bookings</div>
    <div class="card stat"><span><?= $rooms ?></span>Active rooms</div>
    <div class="card stat wide"><span><?= money($revenue) ?></span>Confirmed revenue</div>
    <div class="card stat"><span><?= $guests ?></span>Registered guests</div>
  </div>
  <div class="two-col" style="margin-top:2rem;align-items:start">
    <div>
      <h2>Recent bookings</h2>
      <div class="table-wrap"><table class="table"><thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($recent as $b): ?><tr><td><?= (int)$b['booking_id'] ?></td><td><?= e($b['guest_name']) ?></td><td><?= e($b['room_name']) ?></td><td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td><td><?= status_badge($b['booking_status']) ?></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <p><a class="btn btn-outline btn-sm" href="<?= e(url('admin/bookings.php')) ?>">Manage all bookings</a></p>
    </div>
    <div>
      <h2>Most booked rooms</h2>
      <ul class="chips"><?php foreach ($popular as $p): ?><li><?= e($p['room_name']) ?> — <?= (int)$p['c'] ?> booking(s)</li><?php endforeach; ?></ul>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
