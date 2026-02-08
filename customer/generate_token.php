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

$email = isset($_POST['email']) ? trim($_POST['email']) : null;

$lastRes = db_query("SELECT token_number FROM tokens WHERE service_id = ? ORDER BY token_number DESC LIMIT 1", [$service_id], 'i');
if ($lastRes->num_rows > 0) {
    $row = $lastRes->fetch_assoc();
    $nextNumber = (int)$row['token_number'] + 1;
} else {
    $nextNumber = 1;
}

$token_code = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

// Insert with email
$stmt = $conn->prepare("INSERT INTO tokens (service_id, token_number, token_code, customer_email, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $service_id, $nextNumber, $token_code, $email);
$stmt->execute();
$token_id = $stmt->insert_id;
$stmt->close();

header("Location: token_status.php?id=" . $token_id);
exit;
