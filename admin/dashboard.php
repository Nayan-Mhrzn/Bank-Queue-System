<?php
session_start();
require __DIR__ . '/../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: login.php");
    exit;
}

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';

// Load counters
$counters = [];
$cRes = db_query("SELECT c.id, c.name, s.name AS service_name
                 FROM counters c
                 JOIN services s ON c.service_id = s.id
                 WHERE c.is_active = 1
                 ORDER BY s.name, c.name");
while ($row = $cRes->fetch_assoc()) $counters[] = $row;

// Load staff users (exclude admins)
$staffRes = db_query("SELECT u.id, u.name, u.email, u.counter_id,
                             c.name AS counter_name, s.name AS service_name
                      FROM users u
                      LEFT JOIN counters c ON u.counter_id = c.id
                      LEFT JOIN services s ON c.service_id = s.id
                      WHERE u.role = 'STAFF' AND u.is_active = 1
                      ORDER BY u.name");
?>
<section class="card">
  <div class="card-header">
    <h2>Staff Counter Assignment</h2>
    <span class="pill">Admin</span>
  </div>

  <table>
    <thead>
      <tr>
        <th class="col-staff"><i class="fa-solid fa-user-tie"></i> Staff</th>
        <th class="col-email"><i class="fa-solid fa-envelope"></i> Email</th>
        <th class="col-counter"><i class="fa-solid fa-desktop"></i> Assigned Counter</th>
        <th class="col-action"><i class="fa-solid fa-pen-to-square"></i> Assign / Update</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($st = $staffRes->fetch_assoc()): ?>
        <tr>
          <td>
              <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($st['name']); ?></div>
          </td>
          <td style="font-family: monospace; font-size: 0.9em;"><?php echo htmlspecialchars($st['email']); ?></td>
          <td>
            <?php echo $st['counter_name']
              ? '<span class="status-pill status-current"><i class="fa-solid fa-check"></i> ' . htmlspecialchars($st['counter_name']) . '</span>'
              : '<span class="status-pill status-waiting"><i class="fa-solid fa-minus"></i> Not assigned</span>'; ?>
          </td>
          <td style="text-align: center;">
            <form method="POST" action="save_assignment.php" style="display: flex; gap: 8px; justify-content: center; align-items: center;">
              <input type="hidden" name="user_id" value="<?php echo (int)$st['id']; ?>">
              <select name="counter_id" required style="width: auto; padding: 0.5rem; border-radius: 6px; font-size: 0.85rem; border: 1px solid var(--border-color);">
                <option value="">Select counter</option>
                <?php foreach ($counters as $c): ?>
                  <option value="<?php echo (int)$c['id']; ?>"
                    <?php echo ((int)$st['counter_id'] === (int)$c['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" style="padding: 0.5rem 0.8rem; font-size: 0.8rem; border-radius: 6px;">
                <i class="fa-solid fa-floppy-disk"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div class="spacer"></div>
  <p class="muted-link">Tip: assign one staff to one counter to avoid queue mixing.</p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
