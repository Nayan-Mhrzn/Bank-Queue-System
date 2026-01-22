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
                <label for="service_id"><i class="fa-solid fa-list-ul"></i> Select Service</label>
                <select name="service_id" id="service_id" required>
                    <option value="">Choose a service...</option>
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
        <div class="spacer"></div>
        <button type="submit">
            <i class="fa-solid fa-ticket"></i> Generate Token
        </button>
    </form>
    <div class="spacer"></div>
    <p><a class="muted-link" href="track_token.php">Already have a token? Track status -></a></p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
