<?php
header('Content-Type: application/json');
require __DIR__ . '/../config/db.php';

$current = [];
$cRes = db_query("SELECT c.id, c.name AS counter_name, s.name AS service_name FROM counters c JOIN services s ON c.service_id = s.id WHERE c.is_active = 1");
while ($c = $cRes->fetch_assoc()) {
    $cid = (int)$c['id'];
    $tRes = db_query(
        "SELECT token_code, status FROM tokens WHERE counter_id = ? AND status IN ('CALLING','SERVING') ORDER BY called_at DESC LIMIT 1",
        [$cid],
        'i'
    );
    $token_code = null;
    $status = null;
    if ($tRes->num_rows > 0) {
        $t = $tRes->fetch_assoc();
        $token_code = $t['token_code'];
        $status = $t['status'];
    }
    $current[] = [
        'counter_name' => $c['counter_name'],
        'service_name' => $c['service_name'],
        'token_code' => $token_code,
        'status' => $status
    ];
}

$waiting = [];
$wRes = db_query(
    "SELECT t.token_code, t.status, s.name AS service_name
     FROM tokens t
     JOIN services s ON t.service_id = s.id
     WHERE t.status = 'WAITING'
     ORDER BY t.created_at ASC
     LIMIT 50"
);
while ($w = $wRes->fetch_assoc()) {
    $waiting[] = [
        'service_name' => $w['service_name'],
        'token_code' => $w['token_code'],
        'status' => $w['status']
    ];
}

echo json_encode([
    'current' => $current,
    'waiting' => $waiting
]);
