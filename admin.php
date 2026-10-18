<?php require 'config.php';
need(true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)$_POST['id'];
  $a = $_POST['action'];
  if ($a === 'verify') $db->query("UPDATE reports SET status='VERIFIED' WHERE id=$id");
  if ($a === 'hide') $db->query("UPDATE reports SET status='HIDDEN' WHERE id=$id");
  if ($a === 'del_report') $db->query("DELETE FROM reports WHERE id=$id");
  if ($a === 'del_user' && $id != $_SESSION['u']['id']) $db->query("DELETE FROM users WHERE id=$id");
  header('Location: admin.php');
  exit;
}
function n($sql)
{
  global $db;
  return $db->query($sql)->fetch_assoc()['c'];
}
function act($a, $id, $l, $c = '')
{
  return "<form class='inline' method='post'><input type='hidden' name='id' value='$id'><input type='hidden' name='action' value='$a'><button class='$c'>$l</button></form> ";
}
head('Admin'); ?>
<h1>Admin Dashboard</h1>
<div class="stats">
  <div class="stat"><b><?= n("SELECT COUNT(*) c FROM reports") ?></b>Total reports</div>
  <div class="stat"><b><?= n("SELECT COUNT(*) c FROM reports WHERE status='PENDING'") ?></b>Pending</div>
  <div class="stat"><b><?= n("SELECT COUNT(*) c FROM reports WHERE status='VERIFIED'") ?></b>Verified</div>
  <div class="stat"><b><?= n("SELECT COUNT(*) c FROM reports WHERE flood<>'NONE' AND created_at>NOW()-INTERVAL 1 DAY") ?></b>Flood reports (24h)</div>
  <div class="stat"><b><?= n("SELECT COUNT(*) c FROM users") ?></b>Users</div>
</div>
<div class="panel wide scroll">
  <h2>Reports</h2>
  <table>
    <tr>
      <th>Area</th>
      <th>Rain</th>
      <th>Flood</th>
      <th>Note</th>
      <th>By</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php $r = $db->query("SELECT r.*,a.name area,u.name author FROM reports r JOIN areas a ON a.id=r.area_id JOIN users u ON u.id=r.user_id ORDER BY r.status='PENDING' DESC,r.created_at DESC");
    while ($x = $r->fetch_assoc()): ?><tr>
        <td><?= e($x['area']) ?></td>
        <td><?= $x['rain'] ?></td>
        <td><?= $x['flood'] ?></td>
        <td><?= e($x['note']) ?></td>
        <td><?= e($x['author']) ?></td>
        <td><span class="badge <?= $x['status'] ?>"><?= $x['status'] ?></span></td>
        <td><?php if ($x['status'] !== 'VERIFIED') echo act('verify', $x['id'], 'Verify');
            if ($x['status'] !== 'HIDDEN') echo act('hide', $x['id'], 'Hide', 'alt');
            echo act('del_report', $x['id'], 'Delete', 'bad'); ?></td>
      </tr><?php endwhile; ?>
  </table>
</div><br>
<div class="panel wide scroll">
  <h2>Users</h2>
  <table>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Role</th>
      <th></th>
    </tr>
    <?php $r = $db->query("SELECT * FROM users");
    while ($x = $r->fetch_assoc()): ?><tr>
        <td><?= e($x['name']) ?></td>
        <td><?= e($x['email']) ?></td>
        <td><?= $x['role'] ?></td>
        <td><?php if ($x['id'] != $_SESSION['u']['id']) echo act('del_user', $x['id'], 'Delete', 'bad'); ?></td>
      </tr><?php endwhile; ?>
  </table>
</div>
<?php foot(); ?>