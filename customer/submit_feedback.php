<?php
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token_id = (int)($_POST['token_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comments = trim($_POST['comments'] ?? '');

if ($token_id <= 0 || $rating < 1 || $rating > 5) {
    die("Invalid input.");
}

// Verify token exists
$res = db_query("SELECT id FROM tokens WHERE id = ?", [$token_id], 'i');
if ($res->num_rows === 0) {
    die("Token not found.");
}

// Insert feedback
$stmt = $conn->prepare("INSERT INTO feedback (token_id, rating, comments, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $token_id, $rating, $comments);

if ($stmt->execute()) {
    header("Location: token_status.php?id=$token_id&feedback=success");
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
exit;
