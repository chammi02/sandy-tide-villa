<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT * FROM bookings WHERE booking_id = ? FOR UPDATE'); $st->execute([$id]);
        $b = $st->fetch();
        if (!$b) throw new RuntimeException('Booking not found.');
        if ($action === 'confirm') {
            if ($b['booking_status'] === 'Confirmed') throw new RuntimeException('Already confirmed.');
            // Prevent overlapping confirmed bookings for the same room
            if (!is_room_available((int)$b['room_id'], $b['check_in'], $b['check_out'], $id)) {
                throw new RuntimeException("Cannot confirm #$id: the room is already confirmed for overlapping dates.");
            }
            $pdo->prepare("UPDATE bookings SET booking_status='Confirmed' WHERE booking_id=?")->execute([$id]);
            flash('success', "Booking #$id confirmed.");
        } elseif ($action === 'cancel') {
            $pdo->prepare("UPDATE bookings SET booking_status='Cancelled' WHERE booking_id=?")->execute([$id]);
            flash('success', "Booking #$id cancelled.");
        } else { throw new RuntimeException('Unknown action.'); }
        $pdo->commit();
    } catch (RuntimeException $ex) {
        $pdo->rollBack(); flash('error', $ex->getMessage());
    }
    redirect('admin/bookings.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$status = $_GET['status'] ?? '';
$sql = 'SELECT b.*, r.room_name FROM bookings b JOIN rooms r ON r.room_id=b.room_id';
$params = [];
if (in_array($status, ['Pending', 'Confirmed', 'Cancelled'], true)) { $sql .= ' WHERE b.booking_status = ?'; $params[] = $status; }
$st = $pdo->prepare($sql . ' ORDER BY b.booking_date DESC'); $st->execute($params);
$bookings = $st->fetchAll();
$pageTitle = 'Manage Bookings';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container section">
  <h1>Booking Management</h1>
  <p class="filter-links">Filter:
    <a href="?">All</a> · <a href="?status=Pending">Pending</a> · <a href="?status=Confirmed">Confirmed</a> · <a href="?status=Cancelled">Cancelled</a></p>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>#</th><th>Guest</th><th>Contact</th><th>Room</th><th>Stay</th><th>Guests</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($bookings as $b): ?>
    <tr>
      <td><?= (int)$b['booking_id'] ?></td>
      <td><?= e($b['guest_name']) ?><br><small><?= e($b['guest_email']) ?></small></td>
      <td><?= e($b['guest_phone']) ?></td><td><?= e($b['room_name']) ?></td>
      <td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?><br><small><?= nights_between($b['check_in'], $b['check_out']) ?> night(s)</small></td>
      <td><?= (int)$b['guests'] ?></td><td><?= money($b['total_amount']) ?></td><td><?= status_badge($b['booking_status']) ?></td>
      <td class="actions-cell">
        <?php foreach (['confirm' => ['Confirmed', 'btn-primary', 'Confirm'], 'cancel' => ['Cancelled', 'btn-danger', 'Cancel']] as $act => [$skip, $cls, $label]):
              if ($b['booking_status'] === $skip) continue; ?>
          <form method="post" onsubmit="return confirm('<?= $label ?> booking #<?= (int)$b['booking_id'] ?>?');">
            <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>"><input type="hidden" name="action" value="<?= $act ?>">
            <button class="btn <?= $cls ?> btn-sm"><?= $label ?></button></form>
        <?php endforeach; ?>
      </td>
    </tr>
    <?php endforeach; if (!$bookings): ?><tr><td colspan="9">No bookings found.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
