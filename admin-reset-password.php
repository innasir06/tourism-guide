<?php
require_once __DIR__ . '/includes/config.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (!$token || !$email) {
    die("Invalid reset link.");
}

$stmt = $conn->prepare("
    SELECT user_id, reset_token
    FROM users
    WHERE email = ?
      AND reset_token_expires > NOW()
      AND user_type = 'customer'
    LIMIT 1
");
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    die("Invalid or expired reset link.");
}

// ✅ Works for BOTH: plain token OR hashed token
$dbToken = $user['reset_token'];
$tokenOk = ($dbToken === $token) || password_verify($token, $dbToken);

if (!$tokenOk) {
    die("Invalid or expired reset link.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (strlen($pass) < 6) die("Password must be at least 6 chars.");
    if ($pass !== $pass2) die("Passwords do not match.");

    $newPassword = password_hash($pass, PASSWORD_DEFAULT);

    $upd = $conn->prepare("
        UPDATE users
        SET password=?, reset_token=NULL, reset_token_expires=NULL
        WHERE user_id=?
    ");
    if (!$upd) die("Update failed: " . $conn->error);

    $upd->bind_param("si", $newPassword, $user['user_id']);
    $upd->execute();

    echo "✅ Password reset successful. You can now log in.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
<h2>Reset Password</h2>

<form method="post">
  <input type="password" name="password" required minlength="6" placeholder="New password">
  <input type="password" name="confirm_password" required minlength="6" placeholder="Confirm password">
  <button type="submit">Reset Password</button>
</form>

</body>
</html>
