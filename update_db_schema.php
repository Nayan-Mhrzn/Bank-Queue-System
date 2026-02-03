<?php
require __DIR__ . '/config/db.php';

// Check if column exists
$check = db_query("SHOW COLUMNS FROM users LIKE 'status'");
if ($check->num_rows == 0) {
    echo "Adding 'status' column to users table...\n";
    db_query("ALTER TABLE users ADD COLUMN status ENUM('ONLINE','BREAK','OFFLINE') DEFAULT 'OFFLINE'");
    echo "Column added successfully.\n";
} else {
    echo "Column 'status' already exists.\n";
}

// Update existing users to 'ONLINE' or 'OFFLINE' based on is_active?
// Let's set everyone to 'OFFLINE' initially or 'ONLINE' to avoid disruption?
// The prompt implies this is a new feature. Let's default to ONLINE for existing active users so they don't suddenly stop working.
db_query("UPDATE users SET status = 'ONLINE' WHERE is_active = 1 AND status = 'OFFLINE'"); // One-time migration for active staff

echo "Migration complete.";
