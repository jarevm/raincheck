<?php
require 'config.php';
header('Content-Type: application/json');
$u = user();
if (!$u) {
    http_response_code(401);
    echo json_encode(['msg' => 'Login required']);
    exit;
}
$id = (int)($_POST['id'] ?? 0);
$s = $db->prepare("SELECT user_id FROM reports WHERE id=?");
$s->bind_param('i', $id);
$s->execute();
$r = $s->get_result()->fetch_assoc();
if (!$r) {
    echo json_encode(['msg' => 'Report not found']);
    exit;
}
if ($r['user_id'] == $u['id']) {
    echo json_encode(['msg' => 'You cannot confirm your own report.']);
    exit;
}
$s = $db->prepare("INSERT IGNORE INTO confirmations(report_id,user_id) VALUES(?,?)");
$s->bind_param('ii', $id, $u['id']);
$s->execute();
if (!$s->affected_rows) {
    echo json_encode(['msg' => 'You already confirmed this report.']);
    exit;
}
$db->query("UPDATE reports SET status='VERIFIED' WHERE id=$id AND status='PENDING' AND (SELECT COUNT(*) FROM confirmations WHERE report_id=$id)>=3");
echo json_encode(['msg' => 'Thanks! Your confirmation was recorded.']);
