<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Please sign in to delete your report.']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode(['message' => 'Invalid request token. Refresh the page and try again.']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid report.']);
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM reports
     WHERE id = ? AND user_id = ? AND status IN ('PENDING', 'VERIFIED')"
);
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows !== 1) {
    http_response_code(404);
    echo json_encode(['message' => 'Report not found or cannot be deleted.']);
    exit;
}

echo json_encode(['message' => 'Report deleted.']);
