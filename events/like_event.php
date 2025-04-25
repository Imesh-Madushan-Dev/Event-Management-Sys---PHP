<?php
// AJAX endpoint for liking events
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'liked' => false,
    'likes' => 0
];

// Check if user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'Please login to like events';
    echo json_encode($response);
    exit();
}

// Check if event_id is set
if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
    $response['message'] = 'Invalid event ID';
    echo json_encode($response);
    exit();
}

$event_id = (int)$_POST['event_id'];
$user_id = $_SESSION['user_id'];

// Check if the event exists
$event_check_sql = "SELECT 1 FROM Events WHERE event_id = ?";
$event_check_stmt = mysqli_prepare($conn, $event_check_sql);
mysqli_stmt_bind_param($event_check_stmt, "i", $event_id);
mysqli_stmt_execute($event_check_stmt);
mysqli_stmt_store_result($event_check_stmt);

if (mysqli_stmt_num_rows($event_check_stmt) == 0) {
    $response['message'] = 'Event not found';
    echo json_encode($response);
    exit();
}

// Check if user already liked the event
$has_liked = hasUserLikedEvent($conn, $user_id, $event_id);

if ($has_liked) {
    // Unlike the event
    $unlike_sql = "DELETE FROM Event_Likes WHERE user_id = ? AND event_id = ?";
    $unlike_stmt = mysqli_prepare($conn, $unlike_sql);
    mysqli_stmt_bind_param($unlike_stmt, "ii", $user_id, $event_id);
    
    if (mysqli_stmt_execute($unlike_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Event unliked successfully';
        $response['liked'] = false;
    } else {
        $response['message'] = 'Failed to unlike event';
    }
} else {
    // Like the event
    $like_sql = "INSERT INTO Event_Likes (user_id, event_id) VALUES (?, ?)";
    $like_stmt = mysqli_prepare($conn, $like_sql);
    mysqli_stmt_bind_param($like_stmt, "ii", $user_id, $event_id);
    
    if (mysqli_stmt_execute($like_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Event liked successfully';
        $response['liked'] = true;
    } else {
        $response['message'] = 'Failed to like event';
    }
}

// Get updated like count
$count_sql = "SELECT COUNT(*) as likes FROM Event_Likes WHERE event_id = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $event_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$response['likes'] = (int)$count_row['likes'];

// Return response
echo json_encode($response);
?> 