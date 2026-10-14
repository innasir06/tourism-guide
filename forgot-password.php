<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    // Always show same message (security)
    $message = "If the email exists, password reset instructions have been sent.";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit;
    }

    // ✅ FIXED: Find ANY user (customer OR admin)
    $stmt = $conn->prepare("
        SELECT user_id, full_name
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {

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
            die("Update failed: " . $conn->error);
        }

        $update->bind_param("ssi", $tokenHash, $expires, $user['user_id']);
        $update->execute();

        // ✅ FIXED: Reset link with BOTH token AND email
        $resetLink = "http://localhost/tourism_guide/reset-password.php"
                   . "?token=" . urlencode($tokenRaw)
                   . "&email=" . urlencode($email);

        // Send email
        $emailSent = send_password_reset_email(
            $email,
            $user['full_name'] ?? 'User',
            $resetLink
        );
        
        // For localhost testing - show the link if email fails
        if (!$emailSent) {
            echo '<div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0; border-radius: 5px;">';
            echo '<strong>Email sending failed (localhost).</strong><br>';
            echo 'Use this link to reset your password:<br>';
            echo '<a href="' . htmlspecialchars($resetLink) . '" style="color: #004085;">' . htmlspecialchars($resetLink) . '</a>';
            echo '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔑 Forgot Password</h2>

    <?php if ($message): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <p>Enter your email address and we'll send you a link to reset your password.</p>

    <form method="post">
        <input type="email" name="email" required placeholder="Enter your email address">
        <button type="submit">Send Reset Link</button>
    </form>
    
    <div class="back-link">
        <a href="login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>