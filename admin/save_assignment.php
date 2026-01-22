<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    die("Unauthorized");
}

$user_id = (int)($_POST['user_id'] ?? 0);
$counter_id = (int)($_POST['counter_id'] ?? 0);

if (!$user_id || !$counter_id) {
    die("Invalid request");
}

// Ensure staff user
$uRes = db_query("SELECT id FROM users WHERE id = ? AND role = 'STAFF' AND is_active = 1", [$user_id], "i");
if ($uRes->num_rows === 0) die("Staff not found");

// Ensure counter exists
$cRes = db_query("SELECT id FROM counters WHERE id = ? AND is_active = 1", [$counter_id], "i");
if ($cRes->num_rows === 0) die("Counter not found");

$stmt = $conn->prepare("UPDATE users SET counter_id = ? WHERE id = ?");
$stmt->bind_param("ii", $counter_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php");
exit;
