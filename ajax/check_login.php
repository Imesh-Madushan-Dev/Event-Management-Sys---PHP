<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions file
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
$response = [
    'loggedIn' => isLoggedIn(),
    'isAdmin' => isAdminLoggedIn(),
    'userName' => isLoggedIn() ? $_SESSION['user_name'] : null
];

// Return response as JSON
echo json_encode($response);
?> 