<?php
// Only start a session if one doesn't already exist and session functions are available
if (!function_exists('session_status') || session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Start output buffering
ob_start();

// Check if we're in a dashboard page
$current_page = basename($_SERVER['PHP_SELF']);
$is_dashboard = strpos($current_page, 'dashboard') !== false || 
                strpos($current_page, 'manage_') !== false || 
                strpos($current_page, 'profile') !== false;

// Get the document root and site URL for correct path generation
$document_root = $_SERVER['DOCUMENT_ROOT'];
$site_root = dirname(dirname(__FILE__));
$relative_path = str_replace('\\', '/', str_replace($document_root, '', $site_root));

// Function to create path
function getPath($path) {
    global $relative_path;
    $path = ltrim($path, '/');
    
    // If accessing from subdirectory, determine the correct path
    $current_dir = dirname($_SERVER['PHP_SELF']);
    $levels_up = substr_count($current_dir, '/');
    
    if ($levels_up > 1) {
        // We're in a subdirectory
        $prefix = str_repeat('../', $levels_up - 1);
        return $prefix . $path;
    } else {
        // We're at root level
        return $path;
    }
}

// Check for logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    header('Location: ' . getPath('index.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIBM Unity - Event Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- QR Code Generator -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <!-- Custom CSS -->
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #2d3436;
        }

        /* Modern Navbar */
        .navbar {
            background: rgba(13, 110, 253, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            color: white !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Auth Buttons */
        .auth-buttons .btn {
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-buttons .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.9);
            color: white;
        }

        .auth-buttons .btn-outline-light:hover {
            background: white;
            color: #0d6efd;
            transform: translateY(-2px);
        }

        .auth-buttons .btn-light {
            background: white;
            color: #0d6efd;
        }

        .auth-buttons .btn-light:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }

        /* Alert Messages */
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        /* Profile Styling */
        .profile-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .profile-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .profile-stat {
            text-align: center;
        }

        .profile-stat-number {
            font-size: 2rem;
            font-weight: 700;
        }

        .profile-stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .dashboard-sidebar {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .dashboard-sidebar .nav-link {
            color: #495057 !important;
            padding: 0.75rem 1rem !important;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .dashboard-sidebar .nav-link:hover,
        .dashboard-sidebar .nav-link.active {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd !important;
        }

        .dashboard-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .event-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            height: 200px;
            overflow: hidden;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-image img {
            transform: scale(1.1);
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        /* Step Cards */
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-5px);
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 auto 1rem;
        }

        /* Branch Cards */
        .branch-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .branch-card:hover {
            transform: translateY(-5px);
        }

        .branch-image {
            height: 200px;
            overflow: hidden;
        }

        .branch-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .branch-card:hover .branch-image img {
            transform: scale(1.1);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.9), rgba(0, 0, 0, 0.7)), url('https://source.unsplash.com/random/1600x900/?university') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 8rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .auth-buttons {
                margin-top: 1rem;
                display: flex;
                gap: 0.5rem;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getPath('index.php'); ?>">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>NIBM Unity</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (!$is_dashboard): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('index.php'); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('events/list_events.php'); ?>">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('about.php'); ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getPath('contact.php'); ?>">Contact</a>
                    </li>
                </ul>
                <?php endif; ?>
                <ul class="navbar-nav">
                    <?php if (isAdminLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-shield me-1"></i>Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="<?php echo getPath('admin/dashboard.php'); ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo getPath('admin/manage_events.php'); ?>">
                                    <i class="fas fa-calendar me-2"></i>Manage Events
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo getPath('admin/manage_users.php'); ?>">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo getPath('user/logout.php'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                        <!-- Added Sign Out Button -->
                        <li class="nav-item ms-2">
                            <a class="btn btn-danger btn-sm" href="<?php echo getPath('user/logout.php'); ?>">
                                <i class="fas fa-sign-out-alt me-1"></i>Sign Out
                            </a>
                        </li>
                    <?php elseif (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle btn btn-link" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>My Account
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo getPath('user/profile.php'); ?>">
                                    <i class="fas fa-user-circle me-2"></i>My Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo getPath('user/logout.php'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                        <!-- Added Sign Out Button -->
                        <li class="nav-item ms-2">
                            <a class="btn btn-danger btn-sm" href="<?php echo getPath('user/logout.php'); ?>">
                                <i class="fas fa-sign-out-alt me-1"></i>Sign Out
                            </a>
                        </li>
                    <?php else: ?>
                        <div class="auth-buttons d-flex">
                            <a href="<?php echo getPath('user/login.php'); ?>" class="btn btn-outline-light me-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <a href="<?php echo getPath('user/register.php'); ?>" class="btn btn-light">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container mt-4 mb-4">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Initialize Bootstrap Components -->
    <script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns
        var dropdownElements = document.querySelectorAll('.dropdown-toggle');
        dropdownElements.forEach(function(element) {
            new bootstrap.Dropdown(element);
        });
        
        // Enable all tooltips if any
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
    // QR Code Generator Script
    function generateQRCode(elementId, data) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        // Clear previous content
        element.innerHTML = '';
        
        // Generate QR code
        const typeNumber = 4;
        const errorCorrectionLevel = 'L';
        const qr = qrcode(typeNumber, errorCorrectionLevel);
        qr.addData(data);
        qr.make();
        
        // Create and append QR code image
        element.innerHTML = qr.createImgTag(5);
    }
    </script>
</body>
</html>