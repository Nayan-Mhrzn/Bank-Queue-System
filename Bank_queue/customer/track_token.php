<?php
$pageTitle = 'Track Token';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <h2>Track Existing Token</h2>
        <span class="pill">Live Status</span>
    </div>
    <form method="GET">
        <label for="code">Token Code</label>
        <input type="text" name="code" id="code" placeholder="e.g. D7" required>
        <button type="submit">Check Status</button>
    </form>
    <div class="spacer"></div>
    <?php
    if (!empty($_GET['code'])) {
        $code = trim($_GET['code']);
        $res = db_query(
            "SELECT t.*, s.name AS service_name, s.avg_service_time
             FROM tokens t
             JOIN services s ON t.service_id = s.id
             WHERE t.token_code = ?",
            [$code]
        );
        if ($res->num_rows === 0) {
            echo '<p>No token found with that code.</p>';
        } else {
            $token = $res->fetch_assoc();
            $service_id = (int)$token['service_id'];
            $created_at = $token['created_at'];

            $stmt = $conn->prepare("SELECT COUNT(*) AS ahead FROM tokens WHERE service_id = ? AND status = 'WAITING' AND created_at < ?");
            $stmt->bind_param("is", $service_id, $created_at);
            $stmt->execute();
            $aheadRes = $stmt->get_result();
            $aheadRow = $aheadRes->fetch_assoc();
            $ahead = (int)$aheadRow['ahead'];
            $eta = $ahead * (int)$token['avg_service_time'];

            echo '<div class="spacer"></div>';
            echo '<div class="token-display">';
            echo '  <div class="token-chip"><div class="token-chip-label">Token</div><div class="token-chip-value">'.htmlspecialchars($token['token_code']).'</div></div>';
            echo '  <div class="token-chip"><div class="token-chip-label">Service</div><div class="token-chip-small">'.htmlspecialchars($token['service_name']).'</div></div>';
            echo '  <div class="token-chip"><div class="token-chip-label">Status</div><div class="token-chip-small">'.htmlspecialchars($token['status']).'</div></div>';
            echo '  <div class="token-chip"><div class="token-chip-label">People Ahead</div><div class="token-chip-small">'.$ahead.'</div></div>';
            echo '  <div class="token-chip"><div class="token-chip-label">Estimated Wait</div><div class="token-chip-small">'.$eta.' minutes</div></div>';
            echo '</div>';
        }
    }
    ?>
    <div class="spacer"></div>
    <p><a class="muted-link" href="index.php">← Back to token generation</a></p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
