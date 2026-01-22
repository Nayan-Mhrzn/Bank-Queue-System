<?php
$pageTitle = 'Customer - Generate Token';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <h2>Get a New Token</h2>
        <span class="pill">Customer Portal</span>
    </div>
    <form action="generate_token.php" method="POST">
        <div class="two-column">
            <div>
                <label for="service_id">Service</label>
                <select name="service_id" id="service_id" required>
                    <option value="">Select service…</option>
                    <?php
                    $res = db_query("SELECT id, name FROM services ORDER BY name");
                    while ($row = $res->fetch_assoc()):
                    ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit">Generate Token</button>
    </form>
    <div class="spacer"></div>
    <p><a class="muted-link" href="track_token.php">Already have a token? Track status →</a></p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
