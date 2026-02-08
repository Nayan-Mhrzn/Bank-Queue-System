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
    
    <?php if ($token['status'] === 'COMPLETED'): ?>
        <?php
        // Check if feedback already submitted
        $fbRes = db_query("SELECT rating FROM feedback WHERE token_id = ?", [$token_id], 'i');
        if ($fbRes->num_rows > 0):
        ?>
            <div style="text-align: center; padding: 20px; background: var(--bg-inset); border-radius: var(--radius);">
                <i class="fa-solid fa-star" style="color: gold; font-size: 2rem;"></i>
                <h3>Thank You!</h3>
                <p>We appreciate your feedback.</p>
            </div>
        <?php else: ?>
            <div id="feedback-section" style="text-align: center; padding: 20px; border: 1px dashed var(--border-color); border-radius: var(--radius);">
                <h3><i class="fa-regular fa-star"></i> Rate Your Experience</h3>
                <form action="submit_feedback.php" method="POST">
                    <input type="hidden" name="token_id" value="<?php echo $token_id; ?>">
                    
                    <div class="rating-stars" style="font-size: 2rem; color: var(--text-muted); cursor: pointer; margin-bottom: 1rem;">
                        <i class="fa-regular fa-star" data-val="1" onclick="setRating(1)"></i>
                        <i class="fa-regular fa-star" data-val="2" onclick="setRating(2)"></i>
                        <i class="fa-regular fa-star" data-val="3" onclick="setRating(3)"></i>
                        <i class="fa-regular fa-star" data-val="4" onclick="setRating(4)"></i>
                        <i class="fa-regular fa-star" data-val="5" onclick="setRating(5)"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" required>
                    
                    <textarea name="comments" placeholder="Optional comments..." style="width: 100%; margin-bottom: 10px;"></textarea>
                    
                    <button type="submit" id="submit-btn" disabled style="opacity: 0.5;">Submit Feedback</button>
                </form>
            </div>
            <script>
            function setRating(n) {
                document.getElementById('rating-input').value = n;
                document.getElementById('submit-btn').disabled = false;
                document.getElementById('submit-btn').style.opacity = 1;
                
                const stars = document.querySelectorAll('.rating-stars i');
                stars.forEach(s => {
                    let val = parseInt(s.getAttribute('data-val'));
                    if (val <= n) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid');
                        s.style.color = 'gold';
                    } else {
                        s.classList.remove('fa-solid');
                        s.classList.add('fa-regular');
                        s.style.color = 'var(--text-muted)';
                    }
                });
            }
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <p style="margin-top: 20px;"><a class="muted-link" href="index.php">Get another token</a></p>
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

                // Reload if status becomes COMPLETED to show feedback form
                if (d.status === 'COMPLETED' && '<?php echo $token['status']; ?>' !== 'COMPLETED') {
                    location.reload();
                }
            });
    }
    setInterval(refreshStatus, 5000);
    refreshStatus();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
