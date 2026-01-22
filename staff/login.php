<?php
session_start();
$pageTitle = 'Staff Login';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $res = db_query("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid credentials';
}
?>
<section class="card">
    <div class="card-header">
        <h2>Staff Login</h2>
        <span class="pill">Secure Access</span>
    </div>
    <?php if ($error): ?>
        <p style="color:#f97373; margin-bottom:8px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="two-column">
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
        </div>
        <button type="submit" class="btn-block btn-login">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
    </form>
    <div class="spacer"></div>
    <p><a class="muted-link" href="../customer/index.php">Back to customer portal</a></p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
