<?php
require_once __DIR__ . '/includes/functions.php';
$next = $_GET['next'] ?? $_POST['next'] ?? '';
if (current_user()) redirect(is_admin() ? 'admin/index.php' : 'my_bookings.php');
$error = null; $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = db()->prepare('SELECT * FROM users WHERE email = ?'); $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);               // prevents session fixation
        $_SESSION['user'] = ['user_id' => (int)$u['user_id'], 'name' => $u['name'], 'email' => $u['email'], 'contact' => $u['contact_number'], 'role' => $u['role']];
        flash('success', 'Welcome back, ' . $u['name'] . '!');
        redirect($u['role'] === 'admin' && $next === '' ? 'admin/index.php' : safe_next($next));
    }
    $error = 'Invalid email or password.';
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Guest &amp; Admin Login</h1></div></section>
<div class="container section narrow">
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="card form-card" id="loginForm" novalidate>
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Email<input type="email" name="email" value="<?= e($email) ?>" required autofocus></label>
    <label>Password<input type="password" name="password" required></label>
    <div id="formMessages" class="js-messages"></div>
    <button class="btn btn-primary" type="submit">Log in</button>
    <p class="note">New guest? <a href="<?= e(url('register.php')) ?>">Create an account</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
