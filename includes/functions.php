<?php
/**
 * Shared helper functions: sessions, CSRF, flash messages, access control,
 * and booking logic (nights, seasonal pricing, availability).
 */
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

const VILLA_NAME = 'Sandy Tide Villa';
const CURRENCY   = 'LKR';

/** Base URL path of the project (works if installed in a sub-folder like /sandy-tide-villa). */
function base_url(): string {
    static $base = null;
    if ($base === null) {
        $root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $base = rtrim(str_ireplace($doc, '', $root), '/');
    }
    return $base;
}
function url(string $path = ''): string { return base_url() . '/' . ltrim($path, '/'); }

/** HTML-escape output (prevents XSS). */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($v): string { return CURRENCY . ' ' . number_format((float)$v, 2); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('Invalid or expired form token. Please go back and try again.'); }
}

/* ---------- Flash messages ---------- */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ---------- Auth / access control ---------- */
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function require_login(): void {
    if (!current_user()) {
        flash('warning', 'Please log in to continue.');
        $uri = $_SERVER['REQUEST_URI'];
        if (base_url() !== '' && str_starts_with($uri, base_url())) $uri = substr($uri, strlen(base_url()));
        redirect('login.php?next=' . urlencode(ltrim($uri, '/')));
    }
}
function require_admin(): void {
    require_login();
    if (!is_admin()) { http_response_code(403); exit('403 - Administrator access only.'); }
}

/* ---------- Booking logic ---------- */
function valid_date(string $d): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}
function nights_between(string $in, string $out): int {
    return (int) (new DateTime($in))->diff(new DateTime($out))->format('%r%a');
}
/** Validates a date range; returns an error message or null. */
function date_range_error(string $in, string $out): ?string {
    if (!valid_date($in) || !valid_date($out)) return 'Please choose valid check-in and check-out dates.';
    if ($in < date('Y-m-d'))                   return 'Check-in date cannot be in the past.';
    if ($out <= $in)                           return 'Check-out must be after check-in.';
    if (nights_between($in, $out) > 30)        return 'Maximum stay is 30 nights per booking.';
    return null;
}
function seasonal_rates(): array {
    static $rates = null;
    if ($rates === null) $rates = db()->query('SELECT season_name,start_date,end_date,multiplier FROM seasonal_rates')->fetchAll();
    return $rates;
}
/** Total cost for the stay, applying seasonal multipliers night by night (computed on the server). */
function calc_total(float $base, string $in, string $out): float {
    $total = 0.0;
    $d = new DateTimeImmutable($in);
    $end = new DateTimeImmutable($out);
    while ($d < $end) {
        $night = $d->format('Y-m-d');
        $mult = 1.0;
        foreach (seasonal_rates() as $s) {
            if ($night >= $s['start_date'] && $night <= $s['end_date']) { $mult = (float)$s['multiplier']; break; }
        }
        $total += $base * $mult;
        $d = $d->modify('+1 day');
    }
    return round($total, 2);
}
/** A room is free if no CONFIRMED booking overlaps [in, out). */
function is_room_available(int $roomId, string $in, string $out, int $excludeBookingId = 0): bool {
    $st = db()->prepare("SELECT COUNT(*) FROM bookings
        WHERE room_id = :r AND booking_status = 'Confirmed' AND booking_id <> :x
          AND check_in < :out AND check_out > :in");
    $st->execute([':r' => $roomId, ':x' => $excludeBookingId, ':in' => $in, ':out' => $out]);
    return (int)$st->fetchColumn() === 0;
}
function get_room(int $id, bool $activeOnly = true): ?array {
    $sql = 'SELECT * FROM rooms WHERE room_id = ?' . ($activeOnly ? " AND room_status='active'" : '');
    $st = db()->prepare($sql); $st->execute([$id]);
    return $st->fetch() ?: null;
}
function status_badge(string $s): string {
    return '<span class="badge badge-' . strtolower($s) . '">' . e($s) . '</span>';
}
function safe_next(?string $next): string {
    $next = ltrim((string)$next, '/');
    return (preg_match('#^[a-zA-Z0-9_\-/]+\.php(\?[a-zA-Z0-9_=&%\-\.]*)?$#', $next) && !str_contains($next, '..')) ? $next : 'my_bookings.php';
}

/** URL of a room image. Old database rows may still use old names (room-x.svg / room-x.jpg); map them to the current photos. */
function room_image(string $file): string {
    $dir = __DIR__ . '/../assets/images/';
    $legacy = [
        'room-garden.svg' => 'room-garden-breeze.jpg',    'room-garden.jpg' => 'room-garden-breeze.jpg',
        'room-deluxe.svg' => 'room-deluxe-sand.jpg',      'room-deluxe.jpg' => 'room-deluxe-sand.jpg',
        'room-oceanview.svg' => 'room-ocean-view.jpg',    'room-oceanview.jpg' => 'room-ocean-view.jpg',
        'room-family.svg' => 'room-family-coral.jpg',     'room-family.jpg' => 'room-family-coral.jpg',
        'room-suite.svg' => 'room-sunset-suite.jpg',      'room-suite.jpg' => 'room-sunset-suite.jpg',
    ];
    if (isset($legacy[$file])) $file = $legacy[$file];
    if (!is_file($dir . $file)) $file = 'room-garden-breeze.jpg';
    return url('assets/images/' . $file);
}
