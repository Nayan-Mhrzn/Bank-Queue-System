<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Customer Feedback';
include __DIR__ . '/../includes/header.php';

// Fetch feedback
$sql = "SELECT f.*, t.token_code, s.name as service_name, c.name as counter_name
        FROM feedback f
        JOIN tokens t ON f.token_id = t.id
        JOIN services s ON t.service_id = s.id
        LEFT JOIN counters c ON t.counter_id = c.id
        ORDER BY f.created_at DESC";
$res = db_query($sql);
?>

<section class="card">
    <div class="card-header">
        <h2>Customer Feedback</h2>
        <span class="pill">Admin</span>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Token</th>
                <th>Service</th>
                <th>Counter</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['token_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['counter_name'] ?? '-'); ?></td>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-<?php echo ($i <= $row['rating']) ? 'solid' : 'regular'; ?> fa-star" 
                                   style="color: <?php echo ($i <= $row['rating']) ? 'gold' : '#ccc'; ?>;"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['comments'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">No feedback received yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="spacer"></div>
    <p><a class="muted-link" href="dashboard.php"><- Back to Dashboard</a></p>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
