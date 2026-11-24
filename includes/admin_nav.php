<?php $ap = basename($_SERVER['SCRIPT_NAME']); ?>
<div class="admin-nav"><div class="container">
  <a href="<?= e(url('admin/index.php')) ?>" class="<?= $ap === 'index.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="<?= e(url('admin/bookings.php')) ?>" class="<?= $ap === 'bookings.php' ? 'active' : '' ?>">Bookings</a>
  <a href="<?= e(url('admin/rooms.php')) ?>" class="<?= in_array($ap, ['rooms.php', 'room_form.php']) ? 'active' : '' ?>">Rooms</a>
</div></div>
