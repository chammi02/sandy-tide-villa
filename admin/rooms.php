<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['room_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle') {
        $pdo->prepare("UPDATE rooms SET room_status = IF(room_status='active','inactive','active') WHERE room_id=?")->execute([$id]);
        flash('success', 'Room status updated.');
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare('DELETE FROM rooms WHERE room_id=?')->execute([$id]);
            flash('success', 'Room deleted.');
        } catch (PDOException $ex) {   // foreign key RESTRICT: room has bookings
            flash('error', 'This room has booking records and cannot be deleted. Deactivate it instead.');
        }
    }
    redirect('admin/rooms.php');
}
$rooms = $pdo->query('SELECT * FROM rooms ORDER BY room_id')->fetchAll();
$pageTitle = 'Manage Rooms';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container section">
  <div class="flex-between"><h1>Room Management</h1><a class="btn btn-primary" href="<?= e(url('admin/room_form.php')) ?>">+ Add room</a></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>ID</th><th>Room</th><th>Type</th><th>Price / night</th><th>Max</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($rooms as $r): ?>
    <tr>
      <td><?= (int)$r['room_id'] ?></td><td><?= e($r['room_name']) ?></td><td><?= e($r['room_type']) ?></td>
      <td><?= money($r['price_per_night']) ?></td><td><?= (int)$r['max_guests'] ?></td>
      <td><span class="badge badge-<?= $r['room_status'] === 'active' ? 'confirmed' : 'cancelled' ?>"><?= e($r['room_status']) ?></span></td>
      <td class="actions-cell">
        <a class="btn btn-outline btn-sm" href="<?= e(url('admin/room_form.php?id=' . (int)$r['room_id'])) ?>">Edit</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>"><input type="hidden" name="action" value="toggle">
          <button class="btn btn-outline btn-sm"><?= $r['room_status'] === 'active' ? 'Deactivate' : 'Activate' ?></button></form>
        <form method="post" onsubmit="return confirm('Permanently delete this room?');"><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="btn btn-danger btn-sm">Delete</button></form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
