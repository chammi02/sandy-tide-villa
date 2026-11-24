<?php
// AJAX endpoint: returns availability, nights and total for a room and date range as JSON.
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$room = get_room((int)($_GET['room_id'] ?? 0));
$in = $_GET['check_in'] ?? ''; $out = $_GET['check_out'] ?? '';
if (!$room)                           { echo json_encode(['ok' => false, 'message' => 'Select a room.']); exit; }
if ($err = date_range_error($in, $out)) { echo json_encode(['ok' => false, 'message' => $err]); exit; }
$avail = is_room_available((int)$room['room_id'], $in, $out);
echo json_encode([
    'ok' => true, 'available' => $avail, 'nights' => nights_between($in, $out),
    'total' => calc_total((float)$room['price_per_night'], $in, $out),
    'message' => $avail ? 'Room is available for these dates.' : 'Sorry, this room is already confirmed for these dates.',
]);
