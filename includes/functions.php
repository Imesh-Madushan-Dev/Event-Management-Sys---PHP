<?php
// Helper functions

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to redirect
function redirect($url) {
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: $url");
    exit();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to generate a unique ticket code
function generateTicketCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
}

// Function to get event details by ID
function getEventById($conn, $eventId) {
    $sql = "SELECT * FROM Events WHERE event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $eventId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to get user details by ID
function getUserById($conn, $userId) {
    $sql = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to check if a user has liked an event
function hasUserLikedEvent($conn, $userId, $eventId) {
    $sql = "SELECT 1 FROM Event_Likes WHERE user_id = ? AND event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $eventId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Function to check if a user is attending an event
function isUserAttendingEvent($conn, $userId, $eventId) {
    $sql = "SELECT 1 FROM Event_Attendance WHERE user_id = ? AND event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $eventId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Function to get all NIBM branches
function getNIBMBranches() {
    return [
        "Colombo Main Campus",
        "Kandy",
        "Galle",
        "Kurunegala",
        "Matara",
        "Jaffna",
        "Batticaloa",
        "Gampaha"
    ];
}
?> 