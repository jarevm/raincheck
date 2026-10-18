<?php require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = $db->prepare("SELECT * FROM users WHERE email=?");
  $s->bind_param('s', $_POST['email']);
  $s->execute();
  $u = $s->get_result()->fetch_assoc();
  if ($u && password_verify($_POST['password'], $u['password'])) {
    session_regenerate_id(true);
    $_SESSION['u'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']];
    header('Location: ' . ($u['role'] === 'ADMIN' ? 'admin.php' : 'index.php'));
    exit;
  }
  $err = 'Invalid email or password.';
}
head('Login'); ?>
<div class="panel">
  <h2>Login</h2><?php if (isset($_GET['registered'])): ?><div class="ok">Account created. Please log in.</div><?php endif; ?>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" required><br><br>
    <button>Login</button> <a href="register.php">Create account</a>
  </form>
</div><?php foot(); ?>