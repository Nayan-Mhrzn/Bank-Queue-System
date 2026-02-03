<?php
session_start();
require __DIR__ . '/../config/db.php';
if (empty($_SESSION['user_id'])) {
    die('Not logged in');
}

// Check status
$uRes = db_query("SELECT status FROM users WHERE id = ?", [$_SESSION['user_id']], "i");
$uRow = $uRes->fetch_assoc();
if (($uRow['status'] ?? 'ONLINE') !== 'ONLINE') {
    die('You are currently ' . htmlspecialchars($uRow['status'] ?? 'OFFLINE') . '. Change status to ONLINE to serve customers.');
}
$counter_id = (int)($_POST['counter_id'] ?? 0);
if (!$counter_id) {
    die('No counter');
}

$cRes = db_query("SELECT service_id FROM counters WHERE id = ?", [$counter_id], 'i');
if ($cRes->num_rows === 0) {
    die('Counter not found');
}
$service_id = (int)$cRes->fetch_assoc()['service_id'];

$nextRes = db_query(
    "SELECT id FROM tokens
     WHERE service_id = ? AND status = 'WAITING'
     ORDER BY created_at ASC
     LIMIT 1",
    [$service_id],
    'i'
);
if ($nextRes->num_rows === 0) {
    header('Location: dashboard.php?counter_id='.$counter_id.'&msg=nowaiting');
    exit;
}
$token_id = (int)$nextRes->fetch_assoc()['id'];

$stmt = $conn->prepare("UPDATE tokens SET status = 'CALLING', counter_id = ?, called_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $counter_id, $token_id);
$stmt->execute();
$stmt->close();

header('Location: dashboard.php?counter_id='.$counter_id);
exit;
