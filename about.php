<?php
// About page
require_once 'includes/header.php';

// Get all branches
$branches = getNIBMBranches();
?>

<div class="container">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1 class="display-4">About NIBM Events</h1>
            <p class="lead">Connecting Students Across All NIBM Branches</p>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <h2>Our Mission</h2>
            <p>NIBM Events is dedicated to enhancing the educational experience of all NIBM students by providing a centralized platform for discovering and participating in events across all 8 campus branches.</p>
            <p>Our goal is to foster connections between students, facilitate knowledge sharing, and build a vibrant community that spans across all NIBM locations.</p>
            <p>Through this platform, we aim to:</p>
            <ul>
                <li>Provide easy access to information about upcoming events</li>
                <li>Allow students to engage with events through likes and attendance tracking</li>
                <li>Simplify the ticket purchasing process for paid events</li>
                <li>Give event organizers tools to create and manage successful events</li>
                <li>Create opportunities for cross-branch collaboration and networking</li>
            </ul>
        </div>
        <div class="col-md-6">
            <img src="https://source.unsplash.com/random/600x400/?university,campus" alt="NIBM Campus" class="img-fluid rounded shadow">
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Our Branches</h2>
            <div class="row">
                <?php foreach ($branches as $index => $branch): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 text-center shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-university fa-3x mb-3 text-primary"></i>
                                <h4 class="card-title"><?php echo $branch; ?></h4>
                                <p class="card-text">
                                    <a href="/events/list_events.php?branch=<?php echo urlencode($branch); ?>" class="btn btn-sm btn-outline-primary">
                                        View Events
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2>About NIBM</h2>
                            <p>The National Institute of Business Management (NIBM) is a premier educational institution in Sri Lanka dedicated to providing high-quality education in business management and related fields.</p>
                            <p>With 8 branches located throughout the country, NIBM offers students convenient access to top-notch education wherever they are located.</p>
                            <p>NIBM has established itself as a leader in business education, with a focus on practical skills, industry relevance, and preparing students for successful careers in the global marketplace.</p>
                        </div>
                        <div class="col-md-6">
                            <h2>Our Values</h2>
                            <ul>
                                <li><strong>Excellence:</strong> We strive for excellence in everything we do</li>
                                <li><strong>Innovation:</strong> We embrace new ideas and approaches</li>
                                <li><strong>Inclusivity:</strong> We welcome students from all backgrounds</li>
                                <li><strong>Collaboration:</strong> We believe in the power of working together</li>
                                <li><strong>Integrity:</strong> We uphold the highest ethical standards</li>
                                <li><strong>Student-centered:</strong> We put students at the heart of all decisions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 text-center">
            <h2 class="mb-4">Join Our Community</h2>
            <p class="lead mb-4">Be part of the vibrant NIBM community and never miss an event!</p>
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/user/register.php" class="btn btn-primary">Sign Up Now</a>
                    <a href="/user/login.php" class="btn btn-outline-primary">Login</a>
                </div>
            <?php else: ?>
                <a href="/events/list_events.php" class="btn btn-primary">Explore Events</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 