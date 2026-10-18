<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Please login first']);
    exit;
}

$report_id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

// get the report to see who posted it
$stmt = $conn->prepare("SELECT user_id FROM reports WHERE id = ?");
$stmt->bind_param('i', $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    echo json_encode(['message' => 'Report not found']);
    exit;
}

if ($report['user_id'] == $user_id) {
    echo json_encode(['message' => 'You cannot confirm your own report']);
    exit;
}

// each user can only confirm a report once
$stmt = $conn->prepare("INSERT IGNORE INTO confirmations (report_id, user_id) VALUES (?, ?)");
$stmt->bind_param('ii', $report_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows == 0) {
    echo json_encode(['message' => 'You already confirmed this report']);
    exit;
}

// 3 or more confirmations makes a pending report verified
$stmt = $conn->prepare("UPDATE reports SET status = 'VERIFIED'
                        WHERE id = ? AND status = 'PENDING'
                        AND (SELECT COUNT(*) FROM confirmations WHERE report_id = ?) >= 3");
$stmt->bind_param('ii', $report_id, $report_id);
$stmt->execute();

echo json_encode(['message' => 'Thanks for confirming!']);
