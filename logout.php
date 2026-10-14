<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';
require_once 'includes/cookie_manager.php';

// Clear remember me cookie
clear_remember_me();

// Logout user (destroys session)
logout_user();

// Set flash message
set_flash_message('success', 'You have been logged out successfully.');

// Redirect to home
header("Location: index.php");
exit;
?>