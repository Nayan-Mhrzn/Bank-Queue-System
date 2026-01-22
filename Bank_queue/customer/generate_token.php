<?php
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$service_id = (int)($_POST['service_id'] ?? 0);
if (!$service_id) {
    die('Invalid service');
}

$svcRes = db_query("SELECT prefix FROM services WHERE id = ?", [$service_id], 'i');
if ($svcRes->num_rows === 0) {
    die('Service not found');
}
$svc = $svcRes->fetch_assoc();
$prefix = $svc['prefix'];

$lastRes = db_query("SELECT token_number FROM tokens WHERE service_id = ? ORDER BY token_number DESC LIMIT 1", [$service_id], 'i');
if ($lastRes->num_rows > 0) {
    $last = (int)$lastRes->fetch_assoc()['token_number'];
    $nextNumber = $last + 1;
} else {
    $nextNumber = 1;
}

$token_code = $prefix . $nextNumber;

$stmt = $conn->prepare("INSERT INTO tokens (service_id, token_number, token_code) VALUES (?,?,?)");
$stmt->bind_param("iis", $service_id, $nextNumber, $token_code);
$stmt->execute();
$token_id = $stmt->insert_id;
$stmt->close();

header("Location: token_status.php?id=" . $token_id);
exit;
