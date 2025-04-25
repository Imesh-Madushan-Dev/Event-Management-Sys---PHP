<?php
// Handle profile update
if (!function_exists('session_status') || session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to update your profile.";
    redirect('../user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validate input
    if (empty($name) || empty($email)) {
        $_SESSION['error_message'] = "Name and email are required.";
        redirect('profile.php');
        exit();
    } 
    
    // Check if new email already exists (if email is changed)
    if ($email != $_SESSION['email']) {
        $check_sql = "SELECT 1 FROM Users WHERE email = ? AND user_id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['error_message'] = "Email already exists. Please use a different email.";
            redirect('profile.php');
            exit();
        }
    }
    
    // Handle profile picture upload
    $profile_picture = '';
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "../uploads/profile_pictures/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $file_name = $user_id . '_' . time() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error_message'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            redirect('profile.php');
            exit();
        }
        
        // Upload file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        } else {
            $_SESSION['error_message'] = "Failed to upload profile picture. Please try again.";
            redirect('profile.php');
            exit();
        }
    }
    
    // Update user information
    if (!empty($password)) {
        // Update with new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if (!empty($profile_picture)) {
            // Update with new password and profile picture
            $update_sql = "UPDATE Users SET name = ?, email = ?, password = ?, profile_picture = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssssi", $name, $email, $hashed_password, $profile_picture, $user_id);
        } else {
            // Update with new password only
            $update_sql = "UPDATE Users SET name = ?, email = ?, password = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sssi", $name, $email, $hashed_password, $user_id);
        }
    } else {
        if (!empty($profile_picture)) {
            // Update with profile picture but no password change
            $update_sql = "UPDATE Users SET name = ?, email = ?, profile_picture = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sssi", $name, $email, $profile_picture, $user_id);
        } else {
            // Update without changing password or profile picture
            $update_sql = "UPDATE Users SET name = ?, email = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssi", $name, $email, $user_id);
        }
    }
    
    if (mysqli_stmt_execute($update_stmt)) {
        // Update session variables
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        
        $_SESSION['success_message'] = "Profile updated successfully!";
        redirect('profile.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to update profile. Please try again. Error: " . mysqli_error($conn);
        redirect('profile.php');
        exit();
    }
} else {
    // Not a POST request, redirect to profile page
    redirect('profile.php');
    exit();
} 