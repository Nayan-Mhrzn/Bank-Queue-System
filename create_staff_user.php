<?php
// create_staff_user.php
require __DIR__ . '/config/db.php';

$name  = 'Bank Staff';
$email = 'staff@bank.com';
$plainPassword = 'Bankstaff123';

// Hash the password securely
$passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

// Check if staff already exists
$check = db_query("SELECT id FROM users WHERE email = ?", [$email]);
if ($check->num_rows > 0) {
    die("Staff user already exists. No action taken.");
}

// Insert staff user
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password_hash, role, is_active)
     VALUES (?, ?, ?, 'STAFF', 1)"
);
$stmt->bind_param("sss", $name, $email, $passwordHash);

if ($stmt->execute()) {
    echo "Staff user created successfully!<br><br>";
    echo "Email: <b>$email</b><br>";
    echo "Password: <b>$plainPassword</b><br><br>";
    echo "Please DELETE this file now for security.";
} else {
    echo "Error creating staff user: " . $stmt->error;
}

$stmt->close();
