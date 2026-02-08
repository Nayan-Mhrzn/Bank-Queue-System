<?php
require __DIR__ . '/includes/Mailer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $res = Mailer::send($to, 'Test Email from Bank Queue', '<h1>It works!</h1><p>This is a test email sent from the Bank Queue System tester.</p>');
        $message = $res['message'];
        $messageType = $res['success'] ? 'success' : 'error';
    } else {
        $message = 'Invalid email address';
        $messageType = 'error';
    }
}

// Check status
$isConfigured = false;
if (defined('SMTP_USER') && strpos(SMTP_USER, 'PUT_YOUR') === false) {
    $isConfigured = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Tester</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: var(--bg-color); }
        .tester-card { width: 100%; max-width: 500px; padding: 2rem; background: var(--bg-surface); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <div class="tester-card">
        <h2><i class="fa-solid fa-paper-plane"></i> Email Tester</h2>
        
        <?php if ($isConfigured): ?>
            <div style="padding: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 6px; margin-bottom: 20px;">
                <i class="fa-solid fa-check-circle"></i> SMTP appears to be configured.
            </div>
        <?php else: ?>
            <div style="padding: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 6px; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> SMTP is NOT configured. Emails will be logged to file.
                <br><small>Edit <code>config/email_config.php</code> to fix.</small>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div style="padding: 10px; border-radius: 6px; margin-bottom: 20px; background: <?php echo $messageType === 'success' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $messageType === 'success' ? '#3b82f6' : '#ef4444'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem;">Send Test Email To:</label>
                <input type="email" name="to" required placeholder="name@example.com" style="width: 100%; padding: 0.8rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-main);">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 0.8rem;">
                Send Test
            </button>
        </form>
        
        <div class="spacer"></div>
        <p style="text-align: center;"><a class="muted-link" href="display/index.php">Go to Display</a> | <a class="muted-link" href="staff/login.php">Staff Login</a></p>
    </div>
</body>
</html>
