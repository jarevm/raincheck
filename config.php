<?php
session_start();
define('ADMIN_CODE', 'asd');
define('LAT', 14.2117);
define('LON', 121.1653);
define('PLACE', 'Calamba, Laguna');
$db = new mysqli('localhost', 'root', '149315', 'raincheck');
$db->set_charset('utf8mb4');
function e($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES);
}
function user()
{
  return $_SESSION['u'] ?? null;
}
function need($admin = false)
{
  $u = user();
  if (!$u || ($admin && $u['role'] !== 'ADMIN')) {
    header('Location: login.php');
    exit;
  }
}
function head($title)
{
  $u = user(); ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | RainCheck Campus</title>
    <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <header><a class="logo" href="index.php">Rain<span>Check</span></a>
      <nav><a href="index.php">Forecast &amp; Reports</a><a href="report.php">Post Report</a>
        <?php if ($u && $u['role'] === 'ADMIN'): ?><a href="admin.php">Admin</a><?php endif; ?>
        <?php if ($u): ?><a href="logout.php">Logout (<?= e($u['name']) ?>)</a><?php else: ?><a class="btn" href="login.php">Login</a><?php endif; ?></nav>
    </header>
    <main><?php }
        function foot()
        { ?></main>
    <footer>RainCheck Campus &middot; About &middot; Contact &middot; Guard / Admin Office</footer>
  </body>

  </html><?php }
