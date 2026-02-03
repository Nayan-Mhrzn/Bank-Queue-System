<?php
session_start();
require __DIR__ . '/../config/db.php';
if (empty($_SESSION['user_id'])) {
    die('Not logged in');
}
$token_id = (int)($_POST['token_id'] ?? 0);
$counter_id = (int)($_POST['counter_id'] ?? 0);
$action = $_POST['action'] ?? '';
if (!$token_id || !$counter_id || !$action) {
    die('Invalid request');
}

if ($action === 'SERVING') {
    $stmt = $conn->prepare("UPDATE tokens SET status = 'SERVING', started_at = IFNULL(started_at, NOW()) WHERE id = ?");
    $stmt->bind_param("i", $token_id);
} elseif ($action === 'COMPLETED') {
    $stmt = $conn->prepare("UPDATE tokens SET status = 'COMPLETED', completed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $token_id);
} elseif ($action === 'CANCELLED') {
    $stmt = $conn->prepare("UPDATE tokens SET status = 'CANCELLED', cancelled_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $token_id);
} else {
    die('Unknown action');
}
$stmt->execute();
$stmt->close();

header('Location: dashboard.php?counter_id='.$counter_id);
exit;
