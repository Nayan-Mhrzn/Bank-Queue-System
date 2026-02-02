<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    die("Unauthorized");
}

$id = (int)($_POST['user_id'] ?? 0);

if (!$id) {
    die("Invalid request");
}

// Ensure the user is a STAFF member first, to prevent deleting admins
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'STAFF'");
$stmt->bind_param("i", $id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    die("Staff not found or cannot delete this user");
}
$stmt->close();

// Delete (Hard delete for now as requested, or soft delete is usage depends. 
// Given simple requirements, hard delete is okay, but `is_active=0` is safer. 
// However, the `add_staff.php` check looks for email existence, so hard delete allows reusing email. 
// Let's do hard delete to clean up fully.)
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard.php?msg=Staff+deleted+successfully");
} else {
    die("Error deleting staff: " . $stmt->error);
}
