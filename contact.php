<?php
// Contact page
require_once 'includes/header.php';

$success = false;
$error = '';

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Basic form validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // In a real-world scenario, you would send an email here
        // For demo purposes, we'll just show a success message
        $success = true;
    }
}

// Get all branches for display
$branches = getNIBMBranches();
?>

<div class="container">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4">Contact Us</h1>
            <p class="lead">We'd love to hear from you! Reach out with any questions, suggestions, or feedback.</p>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h4 class="alert-heading">Message Sent!</h4>
                    <p>Thank you for contacting us. We'll get back to you as soon as possible.</p>
                    <hr>
                    <p class="mb-0">Feel free to browse our <a href="/events/list_events.php" class="alert-link">events</a> while you wait for our response.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Send a Message</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                    value="<?php echo isset($_POST['name']) ? $_POST['name'] : (isLoggedIn() ? $_SESSION['name'] : ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?php echo isset($_POST['email']) ? $_POST['email'] : (isLoggedIn() ? $_SESSION['email'] : ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required
                                    value="<?php echo isset($_POST['subject']) ? $_POST['subject'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h4 class="mb-0">Contact Information</h4>
                </div>
                <div class="card-body">
                    <h5>Main Office</h5>
                    <p>
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i> 120/5 Wijerama Mawatha, Colombo 07, Sri Lanka<br>
                        <i class="fas fa-phone me-2 text-primary"></i> +94 11 2123456<br>
                        <i class="fas fa-envelope me-2 text-primary"></i> info@nibmevents.lk
                    </p>
                    
                    <hr>
                    
                    <h5>Branch Locations</h5>
                    <div class="row">
                        <?php foreach (array_slice($branches, 0, 4) as $branch): ?>
                            <div class="col-md-6 mb-3">
                                <h6><?php echo $branch; ?> Branch</h6>
                                <p class="small text-muted mb-1">
                                    <i class="fas fa-phone me-1"></i> +94 11 2<?php echo rand(100000, 999999); ?>
                                </p>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i> <?php echo strtolower(str_replace(' ', '.', $branch)); ?>@nibm.lk
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <p class="mt-2"><a href="/about.php" class="text-primary">View all branches <i class="fas fa-arrow-right"></i></a></p>
                    
                    <hr>
                    
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-info me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-danger me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-primary"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    
                    <hr>
                    
                    <h5>Office Hours</h5>
                    <p>
                        Monday - Friday: 8:30 AM - 5:00 PM<br>
                        Saturday: 9:00 AM - 1:00 PM<br>
                        Sunday: Closed
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4>Need Quick Answers?</h4>
                    <p class="mb-3">Check out our frequently asked questions or browse upcoming events.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/events/list_events.php" class="btn btn-primary">Browse Events</a>
                        <a href="/about.php" class="btn btn-outline-primary">About NIBM</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 