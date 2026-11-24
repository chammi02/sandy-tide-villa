<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$room = $id ? get_room($id, false) : null;
if ($id && !$room) { http_response_code(404); exit('Room not found.'); }

$images = array_map('basename', glob(__DIR__ . '/../assets/images/room-*.jpg'));
$types = ['Standard', 'Deluxe', 'Family', 'Suite'];
$f = $room ?: ['room_name' => '', 'room_type' => 'Standard', 'description' => '', 'facilities' => '', 'price_per_night' => '', 'max_guests' => 2, 'image' => $images[0] ?? 'room-garden-breeze.jpg', 'room_status' => 'active'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = [
      'room_name' => trim($_POST['room_name'] ?? ''), 'room_type' => $_POST['room_type'] ?? '', 'description' => trim($_POST['description'] ?? ''),
      'facilities' => trim($_POST['facilities'] ?? ''), 'price_per_night' => $_POST['price_per_night'] ?? '', 'max_guests' => (int)($_POST['max_guests'] ?? 0),
      'image' => $_POST['image'] ?? '', 'room_status' => $_POST['room_status'] ?? 'active',
    ];
    if (mb_strlen($f['room_name']) < 3 || mb_strlen($f['room_name']) > 100) $errors[] = 'Room name must be 3-100 characters.';
    if (!in_array($f['room_type'], $types, true))      $errors[] = 'Invalid room type.';
    if ($f['description'] === '')                      $errors[] = 'Description is required.';
    if ($f['facilities'] === '')                       $errors[] = 'Please list at least one facility (comma separated).';
    if (!is_numeric($f['price_per_night']) || $f['price_per_night'] <= 0) $errors[] = 'Price must be a positive number.';
    if ($f['max_guests'] < 1 || $f['max_guests'] > 10) $errors[] = 'Maximum guests must be 1-10.';
    if (!in_array($f['image'], $images, true))         $errors[] = 'Invalid image.';
    if (!in_array($f['room_status'], ['active', 'inactive'], true)) $errors[] = 'Invalid status.';

    if (!$errors) {
        $vals = [$f['room_name'], $f['room_type'], $f['description'], $f['facilities'], $f['price_per_night'], $f['max_guests'], $f['image'], $f['room_status']];
        if ($room) {
            db()->prepare('UPDATE rooms SET room_name=?,room_type=?,description=?,facilities=?,price_per_night=?,max_guests=?,image=?,room_status=? WHERE room_id=?')->execute([...$vals, $id]);
            flash('success', 'Room updated.');
        } else {
            db()->prepare('INSERT INTO rooms (room_name,room_type,description,facilities,price_per_night,max_guests,image,room_status) VALUES (?,?,?,?,?,?,?,?)')->execute($vals);
            flash('success', 'Room added.');
        }
        redirect('admin/rooms.php');
    }
}
$pageTitle = $room ? 'Edit Room' : 'Add Room';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container section narrow">
  <h1><?= $room ? 'Edit room' : 'Add new room' ?></h1>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="card form-card" id="roomForm">
    <?= csrf_field() ?>
    <label>Room name<input type="text" name="room_name" value="<?= e($f['room_name']) ?>" required></label>
    <div class="two-col">
      <label>Room type<select name="room_type"><?php foreach ($types as $t): ?><option <?= $f['room_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
      <label>Status<select name="room_status"><option value="active" <?= $f['room_status'] === 'active' ? 'selected' : '' ?>>active</option><option value="inactive" <?= $f['room_status'] === 'inactive' ? 'selected' : '' ?>>inactive</option></select></label>
    </div>
    <label>Description<textarea name="description" rows="3" required><?= e($f['description']) ?></textarea></label>
    <label>Facilities (comma separated)<input type="text" name="facilities" value="<?= e($f['facilities']) ?>" required></label>
    <div class="two-col">
      <label>Price per night (LKR)<input type="number" name="price_per_night" min="1" step="0.01" value="<?= e($f['price_per_night']) ?>" required></label>
      <label>Max guests<input type="number" name="max_guests" min="1" max="10" value="<?= (int)$f['max_guests'] ?>" required></label>
    </div>
    <label>Image<select name="image"><?php foreach ($images as $im): ?><option <?= $f['image'] === $im ? 'selected' : '' ?>><?= e($im) ?></option><?php endforeach; ?></select></label>
    <button class="btn btn-primary">Save room</button>
    <a class="btn btn-outline" href="<?= e(url('admin/rooms.php')) ?>">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
