<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/db.php';

$token_id = (int)($_GET['id'] ?? 0);
if (!$token_id) {
    echo json_encode(['error' => 'no id']);
    exit;
}

$res = db_query(
    "SELECT t.*, s.avg_service_time, c.name AS counter_name
     FROM tokens t
     JOIN services s ON t.service_id = s.id
     LEFT JOIN counters c ON t.counter_id = c.id
     WHERE t.id = ?",
    [$token_id],
    'i'
);
if ($res->num_rows === 0) {
    echo json_encode(['error' => 'not found']);
    exit;
}
$token = $res->fetch_assoc();
$service_id = (int)$token['service_id'];
$created_at = $token['created_at'];

$stmt = $conn->prepare("SELECT COUNT(*) AS ahead FROM tokens WHERE service_id = ? AND status = 'WAITING' AND created_at < ?");
$stmt->bind_param("is", $service_id, $created_at);
$stmt->execute();
$aheadRes = $stmt->get_result();
$aheadRow = $aheadRes->fetch_assoc();
$ahead = (int)$aheadRow['ahead'];
$eta = $ahead * (int)$token['avg_service_time'];

echo json_encode([
    'status' => $token['status'],
    'ahead' => $ahead,
    'eta_minutes' => $eta,
    'counter_name' => $token['counter_name']
]);
