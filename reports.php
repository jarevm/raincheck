<?php
// sends the reports as JSON, script.js loads this with fetch()
require 'config.php';
header('Content-Type: application/json');

$area = (int)($_GET['area'] ?? 0);
$rain = $_GET['rain'] ?? '';
$flood = $_GET['flood'] ?? '';
$date = $_GET['date'] ?? '';

// hidden reports are not shown to users
$sql = "SELECT r.id, a.name AS area, r.rain, r.flood, r.note, r.photo, r.status,
        u.name AS author,
        TIMESTAMPDIFF(MINUTE, r.created_at, NOW()) AS minutes,
        (SELECT COUNT(*) FROM confirmations c WHERE c.report_id = r.id) AS votes
        FROM reports r
        JOIN areas a ON a.id = r.area_id
        JOIN users u ON u.id = r.user_id
        WHERE r.status <> 'HIDDEN' ";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    $sql .= "AND r.status <> 'PENDING' ";
}

$types = '';
$params = [];

if ($area > 0) {
    $sql .= "AND r.area_id = ? ";
    $types .= 'i';
    $params[] = $area;
}

if (in_array($rain, ['LIGHT', 'MODERATE', 'HEAVY'])) {
    $sql .= "AND r.rain = ? ";
    $types .= 's';
    $params[] = $rain;
}

if ($flood == 'YES') {
    $sql .= "AND r.flood <> 'NONE' ";
} else if (in_array($flood, ['ANKLE', 'KNEE', 'WAIST'])) {
    $sql .= "AND r.flood = ? ";
    $types .= 's';
    $params[] = $flood;
}

if ($date != '') {
    $sql .= "AND DATE(r.created_at) >= ? ";
    $types .= 's';
    $params[] = $date;
}

$sql .= "ORDER BY r.created_at DESC LIMIT 60";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $row['area'] = htmlspecialchars($row['area']);
    $row['note'] = htmlspecialchars($row['note'] ?? '');
    $row['author'] = htmlspecialchars($row['author']);
    $row['photo'] = htmlspecialchars($row['photo'] ?? '');
    $reports[] = $row;
}

echo json_encode($reports);
