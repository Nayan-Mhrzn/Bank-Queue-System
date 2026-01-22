<?php
// reset_staff_v2.php
require __DIR__ . '/config/db.php';

// 1. Delete all existing STAFF users
$conn->query("DELETE FROM users WHERE role = 'STAFF'");
echo "Existing staff users deleted.<br><hr>";

// 2. Fetch all counters (Sorted by Name)
$countersRes = $conn->query("SELECT c.id, c.name, s.name AS service_name FROM counters c JOIN services s ON c.service_id = s.id WHERE c.is_active = 1 ORDER BY c.name");
$counters = [];
while ($r = $countersRes->fetch_assoc()) $counters[] = $r;

// 3. Create new staff for each counter with SHORT credentials
$created = [];
$i = 1;

foreach ($counters as $c) {
    $counterId = (int)$c['id'];
    
    // Simple short credentials
    // Email: staff1@bank.com, staff2@bank.com
    // Password: pass1, pass2... (Or just '123456' for all?) 
    // User asked for "easy pw". Let's do 'pass123' for all to be super easy, or 'pass1' matching id.
    // Let's use 'pass123' for simplicity.
    
    $email = 'staff' . $i . '@bank.com';
    $plainPass = 'pass123'; 
    $name = 'Staff ' . $i; // Simple Name: Staff 1
    
    $hash = password_hash($plainPass, PASSWORD_BCRYPT);
    
    // Insert User
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, is_active, counter_id) VALUES (?, ?, ?, 'STAFF', 1, ?)");
    $stmt->bind_param("sssi", $name, $email, $hash, $counterId);
    
    if ($stmt->execute()) {
        $created[] = [
            'counter' => $c['name'],
            'name' => $name,
            'email' => $email,
            'password' => $plainPass
        ];
    }
    $i++;
}

// 4. Output Logic
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: white; padding: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #1e293b; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #334155; text-align: left; }
        th { background: #0d9488; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #334155; }
        .success { color: #4ade80; font-weight: bold; }
        .code { font-family: monospace; color: #facc15; font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Staff Credentials Reset (V2)</h1>
    <p class="success">Successfully created <?php echo count($created); ?> new staff accounts with SHORT credentials.</p>
    
    <table>
        <thead>
            <tr>
                <th>Assigned Counter</th>
                <th>Staff Name</th>
                <th>Short Email</th>
                <th>Easy Password</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($created as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['counter']); ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td class="code" style="color: #38bdf8;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td class="code"><?php echo htmlspecialchars($u['password']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 30px; color: #94a3b8;">
        IMPORTANT: Save these credentials now. This script file should be deleted after use.
    </p>
</body>
</html>
