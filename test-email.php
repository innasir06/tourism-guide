<?php
require __DIR__ . '/includes/mailer.php';

$sent = send_password_reset_email(
    'admin@example.com',  // send to yourself
    'Test User',
    'http://localhost/tourism_guide/reset-password.php?token=TEST123'
);

echo $sent ? '✅ Email sent successfully' : '❌ Email failed';
