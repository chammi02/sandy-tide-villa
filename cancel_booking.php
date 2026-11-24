<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('my_bookings.php');
csrf_check();
$id = (int)($_POST['booking_id'] ?? 0);
// user_id condition stops guests cancelling other people's bookings; only Pending can be cancelled
$st = db()->prepare("UPDATE bookings SET booking_status='Cancelled' WHERE booking_id = ? AND user_id = ? AND booking_status = 'Pending'");
$st->execute([$id, current_user()['user_id']]);
flash($st->rowCount() ? 'success' : 'error', $st->rowCount() ? "Booking #$id cancelled." : 'That booking could not be cancelled (only your own pending bookings can be cancelled).');
redirect('my_bookings.php');
