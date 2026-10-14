<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

// ✅ Your personal email (where reset link will be SENT)
define('ADMIN_PERSONAL_EMAIL', 'admin@example.com');

$message = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the admin user from database
    $sql = "SELECT user_id, email, full_name FROM users WHERE user_type='admin' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && ($admin = $result->fetch_assoc())) {

        // Generate token
        $tokenRaw  = bin2hex(random_bytes(32));
        $tokenHash = password_hash($tokenRaw, PASSWORD_DEFAULT);
        $expires   = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save HASH in DB
        $update = $conn->prepare("
            UPDATE users
            SET reset_token = ?, reset_token_expires = ?
            WHERE user_id = ?
        ");
        
        if (!$update) {
            die("Prepare failed: " . $conn->error);
        }

        $update->bind_param("ssi", $tokenHash, $expires, $admin['user_id']);
        $update->execute();

        // ✅✅✅ CRITICAL FIX: Use admin's DATABASE email, not personal email!
        $resetLink = "http://localhost/tourism_guide/reset-password.php"
                   . "?token=" . urlencode($tokenRaw)
                   . "&email=" . urlencode($admin['email']);  // ← ADMIN'S DB EMAIL!

        // Send email to YOUR personal email (but link uses DB email)
        $emailSent = send_password_reset_email(
            ADMIN_PERSONAL_EMAIL,  // Send TO your personal email
            $admin['full_name'] ?? 'Admin',
            $resetLink  // But link contains admin@test.com
        );

        if ($emailSent) {
            $message = "✅ Password reset link sent to " . ADMIN_PERSONAL_EMAIL;
        } else {
            $message = "⚠️ Email sending failed. Use the link below:";
        }
    } else {
        $message = "❌ No admin user found in database!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Password Reset - One-Time Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border: 3px solid #dc3545;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #dc3545;
            margin-top: 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .reset-link {
            background: #d1ecf1;
            border: 2px solid #0c5460;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            word-break: break-all;
        }
        .reset-link a {
            color: #004085;
            font-weight: bold;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #c82333;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .info ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info li {
            margin: 8px 0;
        }
        .email-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .email-info strong {
            color: #495057;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #e83e8c;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔐 Admin Password Reset (One-Time)</h2>

    <div class="warning">
        <strong>⚠️ How This Works:</strong><br>
        <ol style="margin: 10px 0; padding-left: 20px;">
            <li>Reset link will be SENT to: <code><?php echo ADMIN_PERSONAL_EMAIL; ?></code></li>
            <li>But the link will reset password for: <code>admin@test.com</code> (admin in database)</li>
            <li>After reset, login with <code>admin@test.com</code> and your new password</li>
        </ol>
    </div>

    <?php if ($message): ?>
        <div class="<?php echo strpos($message, '✅') !== false ? 'success' : 'warning'; ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($resetLink): ?>
        <div class="reset-link">
            <strong>🔗 YOUR RESET LINK:</strong><br><br>
            <a href="<?= htmlspecialchars($resetLink) ?>" target="_blank">
                <?= htmlspecialchars($resetLink) ?>
            </a>
            <br><br>
            <small style="color: #666;">
                ⏰ This link expires in 1 hour<br>
                📧 Email in link: admin@test.com (from database)
            </small>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <a href="<?= htmlspecialchars($resetLink) ?>" 
               style="display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                ➜ Click Here to Reset Password
            </a>
        </div>
    <?php endif; ?>

    <?php if (!$message): ?>
        <p>Click the button below to send a password reset link to your email.</p>
        <form method="post">
            <button type="submit">📧 Send Reset Link to My Email</button>
        </form>
    <?php else: ?>
        <form method="post">
            <button type="submit">🔄 Send Another Reset Link</button>
        </form>
    <?php endif; ?>

    <div class="info">
        <strong>ℹ️ Step-by-Step Instructions:</strong>
        <ol>
            <li>Click "Send Reset Link to My Email" button above</li>
            <li>Click the blue "Click Here to Reset Password" button (or the link shown)</li>
            <li>You'll see a form with "admin@test.com" displayed</li>
            <li>Enter your NEW password (twice)</li>
            <li>Click "Reset Password"</li>
            <li>Go to login page</li>
            <li>Login with:
                <div class="email-info">
                    <strong>Email:</strong> admin@test.com<br>
                    <strong>Password:</strong> (your new password)
                </div>
            </li>
        </ol>
        
        <p><strong>⚠️ IMPORTANT:</strong> Even though the reset email goes to <code><?php echo ADMIN_PERSONAL_EMAIL; ?></code>, you must login with <code>admin@test.com</code> because that's the admin email in your database!</p>
    </div>
</div>

</body>
</html>