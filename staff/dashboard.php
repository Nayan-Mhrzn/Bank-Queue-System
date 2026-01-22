<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Staff Dashboard';
include __DIR__ . '/../includes/header.php';

$user_name = $_SESSION['user_name'];
$selectedCounter = (int)($_GET['counter_id'] ?? 0);

// Auto-use assigned counter if staff has one
$meRes = db_query("SELECT counter_id FROM users WHERE id = ?", [$_SESSION['user_id']], "i");
$me = $meRes->fetch_assoc();
$assigned = (int)($me['counter_id'] ?? 0);

if ($assigned > 0) {
    $selectedCounter = $assigned; // force assigned counter
}
?>
<section class="card">
    <div class="card-header">
        <h2>Counter Dashboard</h2>
        <span class="pill"><i class="fa-solid fa-user-check"></i> Hello, <?php echo htmlspecialchars($user_name); ?></span>
    </div>

    <!-- Strict Mode: Staff cannot select counter. Must be assigned by Admin. -->
    <?php if ($assigned > 0): 
        $selectedCounter = $assigned;
        $cRes = db_query(
            "SELECT c.*, s.name AS service_name
             FROM counters c
             JOIN services s ON c.service_id = s.id
             WHERE c.id = ?",
            [$selectedCounter],
            'i'
        );
        $counter = $cRes->fetch_assoc();
    ?>
        <div class="spacer"></div>
        <div class="token-display">
            <div class="token-chip">
                <div class="token-chip-label">Counter</div>
                <div class="token-chip-small" style="color: var(--primary-light); font-weight: 700;"><?php echo htmlspecialchars($counter['name']); ?></div>
            </div>
            <div class="token-chip">
                <div class="token-chip-label">Service</div>
                <div class="token-chip-small"><?php echo htmlspecialchars($counter['service_name']); ?></div>
            </div>
        </div>

        <?php
        $curRes = db_query(
            "SELECT * FROM tokens
             WHERE counter_id = ? AND status IN ('CALLING','SERVING')
             ORDER BY called_at DESC
             LIMIT 1",
            [$selectedCounter],
            'i'
        );
        if ($curRes->num_rows > 0):
            $cur = $curRes->fetch_assoc();
            $statusLower = strtolower($cur['status']);
        ?>
            <div class="spacer"></div>
            <h3><i class="fa-solid fa-clipboard-user"></i> Current Token</h3>
            <div class="token-display">
                <div class="token-chip">
                    <div class="token-chip-label">Token</div>
                    <div class="token-chip-value"><?php echo htmlspecialchars($cur['token_code']); ?></div>
                </div>
                <div class="token-chip">
                    <div class="token-chip-label">Status</div>
                    <div class="token-chip-small">
                        <span class="status-pill status-<?php echo $statusLower; ?>" style="font-size: 1rem;">
                            <?php echo htmlspecialchars($cur['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="spacer"></div>
            <form method="POST" action="update_token.php" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="hidden" name="token_id" value="<?php echo (int)$cur['id']; ?>">
                <input type="hidden" name="counter_id" value="<?php echo (int)$selectedCounter; ?>">
                
                <button name="action" value="SERVING" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                    <i class="fa-solid fa-play"></i> Mark as Serving
                </button>
                <button name="action" value="COMPLETED" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fa-solid fa-check"></i> Mark as Completed
                </button>
                <button name="action" value="CANCELLED" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
                    <i class="fa-solid fa-xmark"></i> Cancel / No-show
                </button>
            </form>
        <?php else: ?>
            <div class="spacer"></div>
            <p class="muted-link">No token currently assigned to this counter.</p>
        <?php endif; ?>

        <div class="spacer"></div>
        <h3><i class="fa-solid fa-bullhorn"></i> Call Next Token</h3>
        <form method="POST" action="call_next.php">
            <input type="hidden" name="counter_id" value="<?php echo (int)$selectedCounter; ?>">
            <button type="submit">
                <i class="fa-solid fa-users-viewfinder"></i> Call Next
            </button>
        </form>
    <?php else: ?>
        <div class="spacer"></div>
        <div style="text-align: center; padding: 40px; background: var(--bg-inset); border-radius: var(--radius);">
            <i class="fa-solid fa-lock" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-main); margin-bottom: 10px;">Access Restricted</h3>
            <p style="color: var(--text-muted);">You have not been assigned to a counter.</p>
            <p style="color: var(--text-muted);">Please contact an Administrator to assign you a workstation.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
