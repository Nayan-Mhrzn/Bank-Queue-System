<?php
session_start();
$pageTitle = 'Admin Login';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Login from users table with ADMIN role
    $res = db_query(
        "SELECT * FROM users WHERE email = ? AND role = 'ADMIN' AND is_active = 1 LIMIT 1",
        [$email],
        "s"
    );

    if ($res && $res->num_rows > 0) {
        $admin = $res->fetch_assoc();

        if (password_verify($password, $admin['password_hash'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['name'];
            $_SESSION['role'] = 'ADMIN';

            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Invalid admin credentials";
}
?>

<section class="card">
    <div class="card-header">
        <h2>Admin Login</h2>
        <span class="pill">Management</span>
    </div>

    <?php if ($error): ?>
        <p style="color:#f97373; margin-bottom:8px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="two-column">
            <div>
                <label><i class="fa-solid fa-envelope"></i> Email</label>
                <input type="email" name="email" required placeholder="admin@bank.com">
            </div>
            <div>
                <label><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
        </div>
        <button type="submit">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
