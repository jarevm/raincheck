<?php require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $n = trim($_POST['name']);
  $em = trim($_POST['email']);
  $pw = $_POST['password'];
  $role = ($_POST['role'] ?? '') === 'ADMIN' ? 'ADMIN' : 'USER';
  if (!$n || !filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) $err = 'Enter a name, valid email, and a password of 6+ characters.';
  elseif ($role === 'ADMIN' && ($_POST['code'] ?? '') !== ADMIN_CODE) $err = 'Invalid admin access code.';
  else {
    $s = $db->prepare("SELECT id FROM users WHERE email=?");
    $s->bind_param('s', $em);
    $s->execute();
    if ($s->get_result()->num_rows) $err = 'Email already registered.';
    else {
      $h = password_hash($pw, PASSWORD_DEFAULT);
      $s = $db->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
      $s->bind_param('ssss', $n, $em, $h, $role);
      $s->execute();
      header('Location: login.php?registered=1');
      exit;
    }
  }
}
head('Register'); ?>
<div class="panel">
  <h2>Create account</h2><?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" onsubmit="if(this.password.value!==this.confirm.value){alert('Passwords do not match');return false}">
    <label>Account type</label>
    <select name="role" id="role" onchange="document.getElementById('codebox').style.display=this.value==='ADMIN'?'block':'none';document.getElementById('code').required=this.value==='ADMIN'">
      <option value="USER" <?= ($_POST['role'] ?? '') === 'USER' ? 'selected' : '' ?>>Student / Faculty / Staff</option>
      <option value="ADMIN" <?= ($_POST['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Admin (Guard / Admin Office)</option>
    </select>
    <div id="codebox" style="display:<?= ($_POST['role'] ?? '') === 'ADMIN' ? 'block' : 'none' ?>"><label>Admin access code</label><input type="password" name="code" id="code"></div>
    <label>Full name</label><input name="name" required><label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" minlength="6" required><label>Confirm password</label><input type="password" name="confirm" required><br><br>
    <button>Register</button> <a href="login.php">Have an account?</a>
  </form>
</div><?php foot(); ?>