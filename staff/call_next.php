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

// Fetches services assigned to this counter
$servicesRes = db_query("SELECT service_id FROM counter_services WHERE counter_id = ?", [$counter_id], 'i');
$serviceIds = [];
while ($row = $servicesRes->fetch_assoc()) {
    $serviceIds[] = (int)$row['service_id'];
}

// Fallback to old method if no services found (though migration should have fixed this)
if (empty($serviceIds)) {
    $cRes = db_query("SELECT service_id FROM counters WHERE id = ?", [$counter_id], 'i');
    if ($cRes->num_rows > 0) {
        $serviceIds[] = (int)$cRes->fetch_assoc()['service_id'];
    }
}

if (empty($serviceIds)) {
    die('Counter has no assigned services');
}

// Convert to comma-separated string for IN clause
$serviceIdsStr = implode(',', $serviceIds);

// Check if already serving
$activeRes = db_query(
    "SELECT id FROM tokens WHERE counter_id = ? AND status IN ('CALLING', 'SERVING') LIMIT 1",
    [$counter_id],
    'i'
);
if ($activeRes->num_rows > 0) {
    die('Cannot call next. You are currently serving a customer. Please complete the current token first.');
}

// Fetch next token from ANY of the assigned services
// Prioritizes by Created At (First Come First Serve across all services)
$nextRes = db_query(
    "SELECT * FROM tokens
     WHERE service_id IN ($serviceIdsStr) AND status = 'WAITING'
     ORDER BY created_at ASC
     LIMIT 1"
);

if ($nextRes->num_rows === 0) {
    header('Location: dashboard.php?counter_id='.$counter_id.'&msg=nowaiting');
    exit;
}
$tokenRow = $nextRes->fetch_assoc();
$token_id = (int)$tokenRow['id'];
$customer_email = $tokenRow['customer_email'] ?? null;
$token_code = $tokenRow['token_code'];

$stmt = $conn->prepare("UPDATE tokens SET status = 'CALLING', counter_id = ?, called_at = NOW() WHERE id = ?");
$stmt->bind_param("ii", $counter_id, $token_id);
$stmt->execute();
$stmt->close();

// Send Email Notification to the NEXT customer (if any)
// We check for the next person waiting in ANY of the services this counter handles
$nextWaitingRes = db_query(
    "SELECT t.token_number, t.token_code, t.customer_email 
     FROM tokens t
     WHERE t.service_id IN ($serviceIdsStr) AND t.status = 'WAITING'
     ORDER BY t.created_at ASC
     LIMIT 1"
);

if ($nextWaitingRes->num_rows > 0) {
    require_once __DIR__ . '/../includes/Mailer.php';
    
    // Get Counter Name
    $counterName = "Counter $counter_id";
    $cNameRes = db_query("SELECT name FROM counters WHERE id = ?", [$counter_id], 'i');
    if ($cNameRow = $cNameRes->fetch_assoc()) {
        $counterName = htmlspecialchars($cNameRow['name']);
    }

    $nextRow = $nextWaitingRes->fetch_assoc();
    $next_email = $nextRow['customer_email'];
    $next_code = $nextRow['token_code'];

    if ($next_email) {
        $subject = "Get Ready! You are next - Token $next_code";
        $body = "
            <h2>Hello!</h2>
            <p>Your token <strong>$next_code</strong> is next in line.</p>
            <p>Please get ready near <strong>$counterName</strong>.</p>
            <br>
            <p><small>Bank Queue System</small></p>
        ";
        
        // We don't want email failure to stop the workflow
        Mailer::send($next_email, $subject, $body); 
    }
}

header('Location: dashboard.php?counter_id='.$counter_id);
exit;
