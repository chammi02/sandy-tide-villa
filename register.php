<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) redirect('my_bookings.php');
$errors = []; $old = ['name' => '', 'email' => '', 'contact' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = ['name' => trim($_POST['name'] ?? ''), 'email' => strtolower(trim($_POST['email'] ?? '')), 'contact' => trim($_POST['contact'] ?? '')];
    $pass = $_POST['password'] ?? ''; $pass2 = $_POST['password2'] ?? '';

    // Server-side validation (client-side validation is only a convenience)
    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) $errors[] = 'Please enter your full name (2-100 characters).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))            $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^\+?[0-9 ]{9,15}$/', $old['contact']))          $errors[] = 'Please enter a valid contact number (9-15 digits).';
    if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/[0-9]/', $pass)) $errors[] = 'Password must be at least 8 characters with letters and numbers.';
    if ($pass !== $pass2)                                             $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $chk = db()->prepare('SELECT 1 FROM users WHERE email = ?'); $chk->execute([$old['email']]);
        if ($chk->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            db()->prepare("INSERT INTO users (name,email,contact_number,password,role) VALUES (?,?,?,?, 'guest')")
                ->execute([$old['name'], $old['email'], $old['contact'], password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', 'Registration successful! Please log in.');
            redirect('login.php');
        }
    }
}
$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Create your guest account</h1></div></section>
<div class="container section narrow">
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="card form-card" id="registerForm" novalidate>
    <?= csrf_field() ?>
    <label>Full name<input type="text" name="name" value="<?= e($old['name']) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= e($old['email']) ?>" required></label>
    <label>Contact number<input type="tel" name="contact" value="<?= e($old['contact']) ?>" placeholder="e.g. 0771234567" required></label>
    <label>Password<input type="password" name="password" minlength="8" required></label>
    <label>Confirm password<input type="password" name="password2" required></label>
    <div id="formMessages" class="js-messages"></div>
    <button class="btn btn-primary" type="submit">Register</button>
    <p class="note">Already registered? <a href="<?= e(url('login.php')) ?>">Log in</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
