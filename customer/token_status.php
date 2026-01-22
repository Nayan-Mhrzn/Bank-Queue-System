<?php
require __DIR__ . '/../config/db.php';
$token_id = (int)($_GET['id'] ?? 0);
if (!$token_id) {
    die('No token id');
}

$res = db_query(
    "SELECT t.*, s.name AS service_name, s.avg_service_time, c.name AS counter_name
     FROM tokens t
     JOIN services s ON t.service_id = s.id
     LEFT JOIN counters c ON t.counter_id = c.id
     WHERE t.id = ?",
    [$token_id],
    'i'
);
if ($res->num_rows === 0) {
    die('Token not found');
}
$token = $res->fetch_assoc();
$pageTitle = 'Token ' . $token['token_code'];
include __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <h2>Token Details</h2>
        <span class="pill"><?php echo htmlspecialchars($token['service_name']); ?></span>
    </div>

    <div class="token-display">
        <div class="token-chip">
            <div class="token-chip-label">Your Token</div>
            <div class="token-chip-value"><?php echo htmlspecialchars($token['token_code']); ?></div>
        </div>
        <div class="token-chip">
            <div class="token-chip-label">Status</div>
            <div class="token-chip-small">
                <span id="status" class="status-pill status-waiting" style="font-size: 1rem;"><?php echo htmlspecialchars($token['status']); ?></span>
            </div>
        </div>
        <div class="token-chip">
            <div class="token-chip-label">Assigned Counter</div>
            <div class="token-chip-small">
                <span id="counter" style="color: var(--primary-light); font-weight: 600;"><?php echo $token['counter_name'] ? htmlspecialchars($token['counter_name']) : 'Not assigned yet'; ?></span>
            </div>
        </div>
    </div>

    <div class="spacer"></div>

    <div class="token-display">
        <div class="token-chip">
            <div class="token-chip-label">People Ahead</div>
            <div class="token-chip-small"><span id="ahead">...</span></div>
        </div>
        <div class="token-chip">
            <div class="token-chip-label">Estimated Wait</div>
            <div class="token-chip-small"><span id="eta">...</span> minutes</div>
        </div>
    </div>

    <div class="spacer"></div>
    <p><a class="muted-link" href="index.php">Get another token</a></p>
</section>

<script>
    function refreshStatus() {
        fetch('token_status_api.php?id=<?php echo $token_id; ?>')
            .then(r => r.json())
            .then(d => {
                if (d.error) return;
                document.getElementById('status').innerText = d.status;
                document.getElementById('ahead').innerText = d.ahead;
                document.getElementById('eta').innerText = d.eta_minutes;
                document.getElementById('counter').innerText = d.counter_name || 'Not assigned yet';
            });
    }
    setInterval(refreshStatus, 5000);
    refreshStatus();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
