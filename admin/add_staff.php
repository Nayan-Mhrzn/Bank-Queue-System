<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    die("Unauthorized");
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    die("All fields are required");
}

// Check if email exists
$check = db_query("SELECT id FROM users WHERE email = ?", [$email], "s");
if ($check->num_rows > 0) {
    die("Email already exists");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'STAFF', 1)");
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    header("Location: dashboard.php?msg=Staff+added+successfully");
} else {
    die("Error adding staff: " . $stmt->error);
}
