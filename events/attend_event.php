<?php
// AJAX endpoint for attending events
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
    'attending' => false,
    'attendees' => 0
];

// Check if user is logged in
if (!isLoggedIn()) {
    $response['message'] = 'Please login to mark attendance';
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

// Check if user is already attending the event
$is_attending = isUserAttendingEvent($conn, $user_id, $event_id);

if ($is_attending) {
    // Remove attendance
    $unattend_sql = "DELETE FROM Event_Attendance WHERE user_id = ? AND event_id = ?";
    $unattend_stmt = mysqli_prepare($conn, $unattend_sql);
    mysqli_stmt_bind_param($unattend_stmt, "ii", $user_id, $event_id);
    
    if (mysqli_stmt_execute($unattend_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Attendance removed successfully';
        $response['attending'] = false;
    } else {
        $response['message'] = 'Failed to remove attendance';
    }
} else {
    // Mark attendance
    $attend_sql = "INSERT INTO Event_Attendance (user_id, event_id) VALUES (?, ?)";
    $attend_stmt = mysqli_prepare($conn, $attend_sql);
    mysqli_stmt_bind_param($attend_stmt, "ii", $user_id, $event_id);
    
    if (mysqli_stmt_execute($attend_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Attendance marked successfully';
        $response['attending'] = true;
    } else {
        $response['message'] = 'Failed to mark attendance';
    }
}

// Get updated attendee count
$count_sql = "SELECT COUNT(*) as attendees FROM Event_Attendance WHERE event_id = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $event_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$response['attendees'] = (int)$count_row['attendees'];

// Return response
echo json_encode($response);
?> 