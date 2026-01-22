<?php
require __DIR__ . '/../config/db.php';

$name = "System Admin";
$email = "admin@bank.com";
$password = "admin123";

$hash = password_hash($password, PASSWORD_BCRYPT);

// Check if already exists
$check = db_query("SELECT id FROM users WHERE email = ? AND role = 'ADMIN' LIMIT 1", [$email], "s");
if ($check && $check->num_rows > 0) {
    die("Admin already exists. Delete this file now.");
}

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password_hash, role, is_active)
     VALUES (?, ?, ?, 'ADMIN', 1)"
);
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    echo "Admin created!<br>";
    echo "Email: admin@bank.com<br>";
    echo "Password: admin123<br><br>";
    echo "Now DELETE create_admin_once.php for security.";
} else {
    echo "Error: " . $stmt->error;
}
?>
