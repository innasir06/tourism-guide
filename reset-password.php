<?php
require_once __DIR__ . '/includes/config.php';

// DEBUG MODE - Shows what's happening
$debug = true;

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$error = '';
$success = '';
$debugInfo = [];

// Add debug info
$debugInfo[] = "Token received: " . ($token ? "YES (length: " . strlen($token) . ")" : "NO");
$debugInfo[] = "Email received: " . ($email ? htmlspecialchars($email) : "NO");

// Check if both token and email are provided
if (!$token || !$email) {
    $error = "Invalid reset link. Both token and email are required.";
    $debugInfo[] = "ERROR: Missing token or email in URL";
} else {
    // ✅ Find user by email (ANY user type - admin or customer)
    $debugInfo[] = "Querying database for email: " . htmlspecialchars($email);
    
    $stmt = $conn->prepare("
        SELECT user_id, email, full_name, user_type, reset_token, reset_token_expires
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

    if (!$user) {
        $error = "No user found with this email.";
        $debugInfo[] = "ERROR: No user found with email: " . htmlspecialchars($email);
    } else {
        $debugInfo[] = "✓ User found: " . htmlspecialchars($user['full_name']) . " (ID: " . $user['user_id'] . ")";
        $debugInfo[] = "User type: " . htmlspecialchars($user['user_type']);
        $debugInfo[] = "Has reset_token: " . ($user['reset_token'] ? "YES" : "NO");
        $debugInfo[] = "Token expires: " . ($user['reset_token_expires'] ?? 'NULL');
        $debugInfo[] = "Current time: " . date('Y-m-d H:i:s');
        
        // Check if token exists
        if (!$user['reset_token']) {
            $error = "No reset token found for this user.";
            $debugInfo[] = "ERROR: reset_token is NULL in database";
        }
        // Check if token expired
        elseif (!$user['reset_token_expires'] || strtotime($user['reset_token_expires']) < time()) {
            $error = "Reset link has expired.";
            $debugInfo[] = "ERROR: Token expired at " . ($user['reset_token_expires'] ?? 'NULL');
            $debugInfo[] = "Time difference: " . (time() - strtotime($user['reset_token_expires'] ?? 'now')) . " seconds ago";
        }
        // Verify token
        else {
            $dbToken = $user['reset_token'];
            $tokenOk = ($dbToken === $token) || password_verify($token, $dbToken);
            
            $debugInfo[] = "Token verification: " . ($tokenOk ? "✓ MATCH" : "✗ NO MATCH");
            
            if (!$tokenOk) {
                $error = "Invalid reset token.";
                $debugInfo[] = "ERROR: Token from URL does not match database token";
                $debugInfo[] = "URL token (first 20 chars): " . substr($token, 0, 20) . "...";
                $debugInfo[] = "DB token (first 20 chars): " . substr($dbToken, 0, 20) . "...";
            }
        }
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && isset($user)) {

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash new password
        $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $updateStmt = $conn->prepare("
            UPDATE users
            SET password = ?,
                reset_token = NULL,
                reset_token_expires = NULL
            WHERE user_id = ?
        ");
        
        if (!$updateStmt) {
            die("Update failed: " . $conn->error);
        }

        $updateStmt->bind_param("si", $newPasswordHash, $user['user_id']);
        
        if ($updateStmt->execute()) {
            $success = true;
            $debugInfo[] = "✓ Password updated successfully!";
        } else {
            $error = "Failed to update password. Please try again.";
            $debugInfo[] = "ERROR: Update query failed";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - DEBUG MODE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .debug {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 13px;
        }
        .debug h3 {
            margin-top: 0;
            color: #856404;
        }
        .debug-line {
            padding: 3px 0;
            border-bottom: 1px solid #fff;
        }
        .user-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        button:hover {
            background: #218838;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔒 Reset Your Password (DEBUG MODE)</h2>

    <?php if ($debug && !empty($debugInfo)): ?>
        <div class="debug">
            <h3>🔍 Debug Information:</h3>
            <?php foreach ($debugInfo as $info): ?>
                <div class="debug-line"><?= htmlspecialchars($info) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
        <div class="login-link">
            <a href="admin-forgot-password.php">← Request a new reset link</a>
        </div>
    <?php elseif ($success): ?>
        <div class="success">
            <h3 style="margin-top:0;">✅ Password Reset Successful!</h3>
            <p>Your password has been updated successfully.</p>
            <p>You can now login with your new password.</p>
            <a href="login.php" style="display: inline-block; margin-top: 15px; padding: 10px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                Go to Login Page →
            </a>
        </div>
    <?php elseif (isset($user)): ?>
        <div class="user-info">
            <strong>Resetting password for:</strong><br>
            <?= htmlspecialchars($user['full_name'] ?? 'User') ?> (<?= htmlspecialchars($user['user_type']) ?>)<br>
            <small><?= htmlspecialchars($email) ?></small>
        </div>

        <form method="post" id="resetForm">
            <label for="password">New Password</label>
            <input type="password" 
                   name="password" 
                   id="password"
                   required 
                   minlength="6" 
                   placeholder="Enter new password (min 6 characters)">
            
            <label for="confirm_password">Confirm Password</label>
            <input type="password" 
                   name="confirm_password" 
                   id="confirm_password"
                   required 
                   minlength="6" 
                   placeholder="Re-enter new password">
            
            <div id="match-message" style="margin-top: 10px; font-size: 14px;"></div>
            
            <button type="submit">Reset Password</button>
        </form>

        <div class="login-link">
            <a href="login.php">← Back to Login</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Password matching checker
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const matchMessage = document.getElementById('match-message');

function checkPasswords() {
    const password = passwordInput ? passwordInput.value : '';
    const confirm = confirmInput ? confirmInput.value : '';
    
    if (!matchMessage) return;
    
    if (confirm === '') {
        matchMessage.textContent = '';
        matchMessage.style.color = '';
    } else if (password === confirm) {
        matchMessage.textContent = '✓ Passwords match';
        matchMessage.style.color = '#28a745';
    } else {
        matchMessage.textContent = '✗ Passwords do not match';
        matchMessage.style.color = '#dc3545';
    }
}

if (confirmInput) confirmInput.addEventListener('input', checkPasswords);
if (passwordInput) passwordInput.addEventListener('input', checkPasswords);

// Form validation
const form = document.getElementById('resetForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const password = passwordInput ? passwordInput.value : '';
        const confirm = confirmInput ? confirmInput.value : '';
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters!');
            return false;
        }
    });
}
</script>

</body>
</html>