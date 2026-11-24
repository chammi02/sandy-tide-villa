<?php
$pageTitle = 'Rooms';
require __DIR__ . '/includes/header.php';

// Optional filters: room type, maximum price, facility keyword
$type = trim($_GET['type'] ?? '');
$max  = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$fac  = trim($_GET['facility'] ?? '');

$sql = "SELECT * FROM rooms WHERE room_status='active'";
$params = [];
if ($type !== '') { $sql .= ' AND room_type = :t'; $params[':t'] = $type; }
if ($max !== null) { $sql .= ' AND price_per_night <= :p'; $params[':p'] = $max; }
if ($fac !== '')  { $sql .= ' AND facilities LIKE :f'; $params[':f'] = '%' . $fac . '%'; }
$sql .= ' ORDER BY price_per_night';
$st = db()->prepare($sql); $st->execute($params);
$rooms = $st->fetchAll();
$types = db()->query("SELECT DISTINCT room_type FROM rooms WHERE room_status='active' ORDER BY room_type")->fetchAll(PDO::FETCH_COLUMN);
?>
<section class="page-head"><div class="container"><h1>Rooms &amp; Accommodation</h1><p>Comfortable rooms steps from Hikkaduwa beach.</p></div></section>
<div class="container section">
  <form class="filter-bar" method="get">
    <label>Room type
      <select name="type"><option value="">All types</option>
      <?php foreach ($types as $t): ?><option <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
      </select></label>
    <label>Max price / night (LKR)<input type="number" name="max_price" min="0" step="500" value="<?= e($_GET['max_price'] ?? '') ?>"></label>
    <label>Facility<input type="text" name="facility" placeholder="e.g. Sea view" value="<?= e($fac) ?>"></label>
    <button class="btn btn-primary" type="submit">Filter</button>
    <a class="btn btn-outline" href="<?= e(url('rooms.php')) ?>">Reset</a>
  </form>
  <?php if (!$rooms): ?><div class="alert alert-info">No rooms match your filters.</div><?php endif; ?>
  <div class="grid grid-3">
    <?php foreach ($rooms as $r): include __DIR__ . '/includes/room_card.php'; endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
