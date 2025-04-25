<?php
// Registration page
if (!function_exists('session_status') || session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';
$name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
$account_type = isset($_POST['account_type']) ? sanitize($_POST['account_type']) : 'user';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $account_type = sanitize($_POST['account_type']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists in Users table
        $check_sql = "SELECT 1 FROM Users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        // Check if email exists in Admins table
        $admin_check_sql = "SELECT 1 FROM Admins WHERE email = ?";
        $admin_check_stmt = mysqli_prepare($conn, $admin_check_sql);
        mysqli_stmt_bind_param($admin_check_stmt, "s", $email);
        mysqli_stmt_execute($admin_check_stmt);
        mysqli_stmt_store_result($admin_check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0 || mysqli_stmt_num_rows($admin_check_stmt) > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($account_type == 'user') {
                // Insert new user
                $sql = "INSERT INTO Users (name, email, password) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "User registration successful! Please login with your new account.";
                    redirect('login.php');
                    exit();
                } else {
                    $error = "Registration failed. Please try again. Error: " . mysqli_error($conn);
                }
            } else {
                // Admin registration
                // You might want to add additional security for admin registration
                // such as requiring an admin code or approval process
                
                // For now, we'll just insert into the Admins table
                $sql = "INSERT INTO Admins (name, email, password) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Admin registration successful! Please login with your new admin account.";
                    redirect('login.php');
                    exit();
                } else {
                    $error = "Admin registration failed. Please try again. Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Include header after processing to avoid header already sent issues
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join NIBM Unity to access all features</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <div><?php echo $success; ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Account Type Selection Tabs -->
                    <ul class="nav nav-pills nav-justified mb-4" id="accountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($account_type == 'user') ? 'active' : ''; ?>" 
                                    id="user-tab" data-bs-toggle="pill" data-bs-target="#user-register" 
                                    type="button" role="tab" aria-selected="<?php echo ($account_type == 'user') ? 'true' : 'false'; ?>">
                                <i class="fas fa-user me-2"></i>User Registration
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($account_type == 'admin') ? 'active' : ''; ?>" 
                                    id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-register" 
                                    type="button" role="tab" aria-selected="<?php echo ($account_type == 'admin') ? 'true' : 'false'; ?>">
                                <i class="fas fa-user-shield me-2"></i>Admin Registration
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="accountTabsContent">
                        <!-- User Registration Form -->
                        <div class="tab-pane fade <?php echo ($account_type == 'user') ? 'show active' : ''; ?>" id="user-register" role="tabpanel">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="account_type" value="user" id="account_type_user">
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-lg" name="name" required 
                                               value="<?php echo htmlspecialchars($name); ?>"
                                               placeholder="Enter your full name">
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-lg" name="email" required 
                                               value="<?php echo htmlspecialchars($email); ?>"
                                               placeholder="Enter your email">
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="password_user" name="password" required
                                               placeholder="Create a password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#password_user')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please create a password.</div>
                                    </div>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="confirm_password_user" name="confirm_password" required
                                               placeholder="Confirm your password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#confirm_password_user')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please confirm your password.</div>
                                    </div>
                                </div>

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms_user" required>
                                    <label class="form-check-label" for="terms_user">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree to our terms and conditions.
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="fas fa-user-plus me-2"></i>Create User Account
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Admin Registration Form -->
                        <div class="tab-pane fade <?php echo ($account_type == 'admin') ? 'show active' : ''; ?>" id="admin-register" role="tabpanel">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="account_type" value="admin" id="account_type_admin">
                                
                                <div class="mb-3">
                                    <label class="form-label">Admin Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user-shield text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-lg" name="name" required 
                                               value="<?php echo htmlspecialchars($name); ?>"
                                               placeholder="Enter admin name">
                                        <div class="invalid-feedback">Please enter admin name.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Admin Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-lg" name="email" required 
                                               value="<?php echo htmlspecialchars($email); ?>"
                                               placeholder="Enter admin email">
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Admin Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="password_admin" name="password" required
                                               placeholder="Create admin password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#password_admin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please create a password.</div>
                                    </div>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Confirm Admin Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="confirm_password_admin" name="confirm_password" required
                                               placeholder="Confirm admin password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#confirm_password_admin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please confirm your password.</div>
                                    </div>
                                </div>

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms_admin" required>
                                    <label class="form-check-label" for="terms_admin">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree to our terms and conditions.
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="fas fa-user-shield me-2"></i>Create Admin Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="login.php" class="fw-semibold text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.querySelector(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Update hidden input when tabs change
document.addEventListener('DOMContentLoaded', function() {
    const userTab = document.getElementById('user-tab');
    const adminTab = document.getElementById('admin-tab');
    
    userTab.addEventListener('click', function() {
        document.getElementById('account_type_user').value = 'user';
    });
    
    adminTab.addEventListener('click', function() {
        document.getElementById('account_type_admin').value = 'admin';
    });
    
    // Password match validation for user form
    const passwordUser = document.getElementById('password_user');
    const confirmPasswordUser = document.getElementById('confirm_password_user');
    
    function validatePasswordUser() {
        if(passwordUser.value != confirmPasswordUser.value) {
            confirmPasswordUser.setCustomValidity("Passwords don't match");
        } else {
            confirmPasswordUser.setCustomValidity('');
        }
    }
    
    if (passwordUser && confirmPasswordUser) {
        passwordUser.addEventListener('change', validatePasswordUser);
        confirmPasswordUser.addEventListener('keyup', validatePasswordUser);
    }
    
    // Password match validation for admin form
    const passwordAdmin = document.getElementById('password_admin');
    const confirmPasswordAdmin = document.getElementById('confirm_password_admin');
    
    function validatePasswordAdmin() {
        if(passwordAdmin.value != confirmPasswordAdmin.value) {
            confirmPasswordAdmin.setCustomValidity("Passwords don't match");
        } else {
            confirmPasswordAdmin.setCustomValidity('');
        }
    }
    
    if (passwordAdmin && confirmPasswordAdmin) {
        passwordAdmin.addEventListener('change', validatePasswordAdmin);
        confirmPasswordAdmin.addEventListener('keyup', validatePasswordAdmin);
    }
});

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once '../includes/footer.php'; ?> 