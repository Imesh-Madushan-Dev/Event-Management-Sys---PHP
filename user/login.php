<?php
// Login page
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
$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
$account_type = isset($_POST['account_type']) ? sanitize($_POST['account_type']) : 'user';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $account_type = sanitize($_POST['account_type']);
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if ($account_type == 'user') {
            // User login
            $sql = "SELECT * FROM Users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = 'user';
                    $_SESSION['success_message'] = "Welcome back, " . $user['name'] . "!";
                    
                    // Redirect based on intended destination if set
                    $redirect_to = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '../index.php';
                    unset($_SESSION['redirect_after_login']);
                    
                    redirect($redirect_to);
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "No user account found with this email.";
            }
        } else {
            // Admin login
            $admin_sql = "SELECT * FROM Admins WHERE email = ?";
            $admin_stmt = mysqli_prepare($conn, $admin_sql);
            mysqli_stmt_bind_param($admin_stmt, "s", $email);
            mysqli_stmt_execute($admin_stmt);
            $admin_result = mysqli_stmt_get_result($admin_stmt);
            
            if (mysqli_num_rows($admin_result) == 1) {
                $admin = mysqli_fetch_assoc($admin_result);
                if (password_verify($password, $admin['password'])) {
                    // Set admin session variables
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['name'] = $admin['name'];
                    $_SESSION['email'] = $admin['email'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['success_message'] = "Welcome back, Admin " . $admin['name'] . "!";
                    
                    redirect('../admin/dashboard.php');
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "No admin account found with this email.";
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
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Please login to your account</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <div><?php echo $_SESSION['success_message']; ?></div>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <!-- Account Type Selection Tabs -->
                    <ul class="nav nav-pills nav-justified mb-4" id="accountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($account_type == 'user') ? 'active' : ''; ?>" 
                                    id="user-tab" data-bs-toggle="pill" data-bs-target="#user-login" 
                                    type="button" role="tab" aria-selected="<?php echo ($account_type == 'user') ? 'true' : 'false'; ?>">
                                <i class="fas fa-user me-2"></i>User Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($account_type == 'admin') ? 'active' : ''; ?>" 
                                    id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-login" 
                                    type="button" role="tab" aria-selected="<?php echo ($account_type == 'admin') ? 'true' : 'false'; ?>">
                                <i class="fas fa-user-shield me-2"></i>Admin Login
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="accountTabsContent">
                        <div class="tab-pane fade <?php echo ($account_type == 'user') ? 'show active' : ''; ?>" id="user-login" role="tabpanel">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="account_type" value="user" id="account_type_user">
                                
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

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Password</label>
                                        <a href="#" class="text-decoration-none small">Forgot Password?</a>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" name="password" required
                                               id="password_user" placeholder="Enter your password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#password_user')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please enter your password.</div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login as User
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade <?php echo ($account_type == 'admin') ? 'show active' : ''; ?>" id="admin-login" role="tabpanel">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <input type="hidden" name="account_type" value="admin" id="account_type_admin">
                                
                                <div class="mb-3">
                                    <label class="form-label">Admin Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-lg" name="email" required 
                                               value="<?php echo htmlspecialchars($email); ?>"
                                               placeholder="Enter your admin email">
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Admin Password</label>
                                        <a href="#" class="text-decoration-none small">Forgot Password?</a>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" name="password" required
                                               id="password_admin" placeholder="Enter your admin password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('#password_admin')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="invalid-feedback">Please enter your password.</div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg py-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="fw-semibold text-decoration-none">Register here</a></p>
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
    const passwordInput = document.querySelector(inputId);
    const icon = passwordInput.nextElementSibling.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
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