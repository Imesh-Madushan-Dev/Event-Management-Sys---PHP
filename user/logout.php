<?php
// Start the session if not already started
if (!function_exists('session_status') || session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions file for redirection
require_once '../includes/functions.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Set success message in a temporary cookie since session is destroyed
setcookie('logout_message', 'You have been successfully logged out!', time() + 5, '/');

// Redirect to home page
redirect('../index.php');
exit();
?>