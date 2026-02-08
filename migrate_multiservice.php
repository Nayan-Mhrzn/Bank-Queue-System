<?php
require __DIR__ . '/config/db.php';

echo "Migration Started...\n";

// 1. Create counter_services table
$sql = "CREATE TABLE IF NOT EXISTS counter_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    counter_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (counter_id) REFERENCES counters(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    UNIQUE KEY (counter_id, service_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'counter_services' created/verified.\n";
} else {
    die("Error creating table: " . $conn->error);
}

// 2. Migrate existing 1:1 relationships
$res = $conn->query("SELECT id, service_id FROM counters WHERE service_id IS NOT NULL");
while ($row = $res->fetch_assoc()) {
    $cid = $row['id'];
    $sid = $row['service_id'];
    // Insert if not exists
    $check = $conn->query("SELECT id FROM counter_services WHERE counter_id=$cid AND service_id=$sid");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO counter_services (counter_id, service_id) VALUES ($cid, $sid)");
        echo "Migrated Counter $cid -> Service $sid\n";
    }
}

// 3. SPECIAL LOGIC: Link "Deposit" counters (Service 1) to "Withdrawal" (Service 2) and vice-versa
// Assuming Service ID 1 = Deposit, Service ID 2 = Withdrawal
// Find counters linked to 1 and add 2
$depCounters = $conn->query("SELECT counter_id FROM counter_services WHERE service_id = 1");
while ($row = $depCounters->fetch_assoc()) {
    $cid = $row['counter_id'];
    // Add withdrawal capability
    $conn->query("INSERT IGNORE INTO counter_services (counter_id, service_id) VALUES ($cid, 2)");
    echo "Upgraded Counter $cid to also handle Withdrawal (Service 2)\n";
}

// Find counters linked to 2 and add 1
$wdCounters = $conn->query("SELECT counter_id FROM counter_services WHERE service_id = 2");
while ($row = $wdCounters->fetch_assoc()) {
    $cid = $row['counter_id'];
    // Add deposit capability
    $conn->query("INSERT IGNORE INTO counter_services (counter_id, service_id) VALUES ($cid, 1)");
    echo "Upgraded Counter $cid to also handle Deposit (Service 1)\n";
}

echo "Migration Complete.\n";
