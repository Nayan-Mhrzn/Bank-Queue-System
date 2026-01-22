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
?>
<section class="card">
    <div class="card-header">
        <h2>Counter Dashboard</h2>
        <span class="pill">Hello, <?php echo htmlspecialchars($user_name); ?></span>
    </div>

    <form method="GET">
        <label for="counter_id">Select Your Counter</label>
        <select name="counter_id" id="counter_id" onchange="this.form.submit()">
            <option value="">Choose counter…</option>
            <?php
            $res = db_query("SELECT c.id, c.name, s.name AS service_name FROM counters c JOIN services s ON c.service_id = s.id WHERE c.is_active = 1");
            while ($row = $res->fetch_assoc()):
                $sel = ($selectedCounter === (int)$row['id']) ? 'selected' : '';
            ?>
                <option value="<?php echo $row['id']; ?>" <?php echo $sel; ?>>
                    <?php echo htmlspecialchars($row['name']).' ('.htmlspecialchars($row['service_name']).')'; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($selectedCounter):
        $cRes = db_query("SELECT c.*, s.name AS service_name FROM counters c JOIN services s ON c.service_id = s.id WHERE c.id = ?", [$selectedCounter], 'i');
        $counter = $cRes->fetch_assoc();
    ?>
        <div class="spacer"></div>
        <div class="token-display">
            <div class="token-chip">
                <div class="token-chip-label">Counter</div>
                <div class="token-chip-small"><?php echo htmlspecialchars($counter['name']); ?></div>
            </div>
            <div class="token-chip">
                <div class="token-chip-label">Service</div>
                <div class="token-chip-small"><?php echo htmlspecialchars($counter['service_name']); ?></div>
            </div>
        </div>

        <?php
        $curRes = db_query("SELECT * FROM tokens WHERE counter_id = ? AND status IN ('CALLING','SERVING') ORDER BY called_at DESC LIMIT 1", [$selectedCounter], 'i');
        if ($curRes->num_rows > 0):
            $cur = $curRes->fetch_assoc();
        ?>
            <div class="spacer"></div>
            <h3>Current Token</h3>
            <div class="token-display">
                <div class="token-chip">
                    <div class="token-chip-label">Token</div>
                    <div class="token-chip-value"><?php echo htmlspecialchars($cur['token_code']); ?></div>
                </div>
                <div class="token-chip">
                    <div class="token-chip-label">Status</div>
                    <div class="token-chip-small"><?php echo htmlspecialchars($cur['status']); ?></div>
                </div>
            </div>
            <div class="spacer"></div>
            <form method="POST" action="update_token.php">
                <input type="hidden" name="token_id" value="<?php echo $cur['id']; ?>">
                <input type="hidden" name="counter_id" value="<?php echo $selectedCounter; ?>">
                <button name="action" value="SERVING">Mark as Serving</button>
                <button name="action" value="COMPLETED">Mark as Completed</button>
                <button name="action" value="CANCELLED">Cancel / No-show</button>
            </form>
        <?php else: ?>
            <div class="spacer"></div>
            <p>No token currently assigned.</p>
        <?php endif; ?>

        <div class="spacer"></div>
        <h3>Call Next Token</h3>
        <form method="POST" action="call_next.php">
            <input type="hidden" name="counter_id" value="<?php echo $selectedCounter; ?>">
            <button type="submit">Call Next</button>
        </form>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
