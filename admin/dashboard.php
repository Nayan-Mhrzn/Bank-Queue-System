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
$staffRes = db_query("SELECT u.id, u.name, u.email, u.counter_id, u.status,
                             c.name AS counter_name, s.name AS service_name
                      FROM users u
                      LEFT JOIN counters c ON u.counter_id = c.id
                      LEFT JOIN services s ON c.service_id = s.id
                      WHERE u.role = 'STAFF' AND u.is_active = 1
                      ORDER BY u.name");
?>
<section class="card">
  <div class="card-header">
    <div style="display: flex; gap: 1rem; align-items: center;">
      <h2>Staff Management</h2>
      <button onclick="document.getElementById('addStaffModal').style.display='flex'" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
        <i class="fa-solid fa-plus"></i> Add Staff
      </button>
    </div>
    <span class="pill">Admin</span>
  </div>

  <table>
    <thead>
      <tr>
        <th class="col-staff"><i class="fa-solid fa-user-tie"></i> Staff</th>
        <th class="col-email"><i class="fa-solid fa-envelope"></i> Email</th>
        <th class="col-counter"><i class="fa-solid fa-desktop"></i> Assigned Counter</th>
        <th class="col-action"><i class="fa-solid fa-pen-to-square"></i> Assign / Update</th>
        <th style="width: 50px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php while ($st = $staffRes->fetch_assoc()): ?>
        <tr>
          <td>
              <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($st['name']); ?></div>
              <span class="status-pill status-<?php echo strtolower($st['status'] ?? 'ONLINE'); ?>" style="font-size: 0.75rem; padding: 2px 6px;">
                  <?php echo htmlspecialchars($st['status'] ?? 'ONLINE'); ?>
              </span>
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
              <button type="submit" style="padding: 0.5rem 0.8rem; font-size: 0.8rem; border-radius: 6px;" title="Save Assignment">
                <i class="fa-solid fa-floppy-disk"></i>
              </button>
            </form>
          </td>
          <td>
              <form method="POST" action="delete_staff.php" onsubmit="return confirm('Are you sure you want to delete this staff member?');" style="margin:0;">
                  <input type="hidden" name="user_id" value="<?php echo (int)$st['id']; ?>">
                  <button type="submit" style="background: var(--danger-color); color: white; border: none; padding: 0.5rem; border-radius: 6px; cursor: pointer;" title="Delete Staff">
                      <i class="fa-solid fa-trash"></i>
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

<!-- Add Staff Modal -->
<div id="addStaffModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: var(--bg-surface); padding: 2rem; border-radius: 12px; width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); border: 1px solid var(--border-color);">
        <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Add New Staff</h3>
        <form method="POST" action="add_staff.php">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Full Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 0.8rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 0.8rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 0.8rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-main);">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addStaffModal').style.display='none'" style="padding: 0.8rem 1.5rem; border-radius: 6px; background: transparent; border: 1px solid var(--border-color); color: var(--text-main); cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 0.8rem 1.5rem;">Add Staff</button>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
