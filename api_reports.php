<?php
require 'config.php';
$sql = "SELECT r.*,a.name area,u.name author,TIMESTAMPDIFF(MINUTE,r.created_at,NOW()) mins,(SELECT COUNT(*) FROM confirmations c WHERE c.report_id=r.id) conf FROM reports r JOIN areas a ON a.id=r.area_id JOIN users u ON u.id=r.user_id WHERE r.status<>'HIDDEN'";
$t = '';
$p = [];
if (!empty($_GET['area'])) {
  $sql .= " AND r.area_id=?";
  $t .= 'i';
  $p[] = (int)$_GET['area'];
}
if (in_array($_GET['rain'] ?? '', ['LIGHT', 'MODERATE', 'HEAVY'])) {
  $sql .= " AND r.rain=?";
  $t .= 's';
  $p[] = $_GET['rain'];
}
if (($_GET['flood'] ?? '') === 'YES') $sql .= " AND r.flood<>'NONE'";
elseif (in_array($_GET['flood'] ?? '', ['ANKLE', 'KNEE', 'WAIST'])) {
  $sql .= " AND r.flood=?";
  $t .= 's';
  $p[] = $_GET['flood'];
}
if (!empty($_GET['date'])) {
  $sql .= " AND DATE(r.created_at)>=?";
  $t .= 's';
  $p[] = $_GET['date'];
}
$st = $db->prepare($sql . " ORDER BY r.created_at DESC LIMIT 60");
if ($p) $st->bind_param($t, ...$p);
$st->execute();
$res = $st->get_result();
if (!$res->num_rows) {
  echo '<p>No reports yet. Be the first to post one.</p>';
  exit;
}
function ago($m)
{
  return $m < 60 ? "$m min ago" : ($m < 1440 ? floor($m / 60) . ' hr ago' : floor($m / 1440) . ' day(s) ago');
}
while ($r = $res->fetch_assoc()) {
  $bg = $r['photo'] ? "style=\"background-image:url('uploads/" . e($r['photo']) . "')\"" : '';
  $fl = $r['flood'] !== 'NONE' ? "<span class='badge {$r['flood']}'>{$r['flood']} FLOOD</span>" : '';
  echo "<div class='card'><div class='ph' $bg>" . ($r['photo'] ? '' : 'No photo') . "</div><div class='bd'>
  <div><span class='badge {$r['rain']}'>{$r['rain']} RAIN</span>$fl<span class='badge {$r['status']}'>{$r['status']}</span></div>
  <h3>" . e($r['area']) . "</h3><p style='margin:0'>" . e($r['note']) . "</p>
  <small>by " . e($r['author']) . " &middot; " . ago($r['mins']) . " &middot; {$r['conf']} confirmed</small>
  <button onclick='confirmReport({$r['id']})'>Confirm</button></div></div>";
}
