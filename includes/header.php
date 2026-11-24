<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
$page = basename($_SERVER['SCRIPT_NAME']);
function nav_link(string $file, string $label, string $page): string {
    return '<a href="' . e(url($file)) . '"' . ($page === $file ? ' class="active"' : '') . '>' . e($label) . '</a>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Home') ?> | <?= e(VILLA_NAME) ?>, Hikkaduwa</title>
<link rel="icon" href="<?= e(url('assets/images/favicon.svg')) ?>">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= e(url('index.php')) ?>">🌴 <?= e(VILLA_NAME) ?> <small>Hikkaduwa</small></a>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">☰</button>
    <nav id="mainNav" class="main-nav">
      <?= nav_link('index.php', 'Home', $page) ?>
      <?= nav_link('rooms.php', 'Rooms', $page) ?>
      <?= nav_link('search.php', 'Check Availability', $page) ?>
      <?php if ($user): ?>
        <?= nav_link('my_bookings.php', 'My Bookings', $page) ?>
        <?php if (is_admin()): ?><a href="<?= e(url('admin/index.php')) ?>">Admin</a><?php endif; ?>
        <a href="<?= e(url('logout.php')) ?>">Logout (<?= e(explode(' ', $user['name'])[0]) ?>)</a>
      <?php else: ?>
        <?= nav_link('login.php', 'Login', $page) ?>
        <?= nav_link('register.php', 'Register', $page) ?>
      <?php endif; ?>
      <a class="btn btn-primary btn-sm" href="<?= e(url('book.php')) ?>">Book Now</a>
    </nav>
  </div>
</header>
<main>
<div class="container flash-wrap">
<?php foreach (flashes() as $f): ?>
  <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
</div>
