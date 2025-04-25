<?php
// Include any necessary PHP functions or configurations
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIBM Unity - Connect Across Campuses</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0a58ca;
            --secondary: #6c757d;
            --success: #198754;
            --info: #0dcaf0;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: #fff;
        }

        /* Enhanced Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.9), rgba(0, 0, 0, 0.8)),
                url('https://source.unsplash.com/random/1920x1080/?university,campus') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.5s;
        }

        .hero-image:hover img {
            transform: scale(1.03);
        }

        .hero-badge {
            position: absolute;
            top: -20px;
            right: -20px;
            background: var(--warning);
            color: var(--dark);
            padding: 15px;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transform: rotate(15deg);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            z-index: 3;
        }

        .hero-social {
            position: absolute;
            left: 0;
            bottom: 30px;
            z-index: 10;
        }

        .hero-social a {
            color: rgba(255, 255, 255, 0.8);
            margin: 0 10px;
            font-size: 1.5rem;
            transition: all 0.3s;
        }

        .hero-social a:hover {
            color: white;
            transform: translateY(-5px);
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero-text {
            font-size: 1.2rem;
            max-width: 80%;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .btn-hero {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.2);
            z-index: -2;
        }

        .btn-hero::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            z-index: -1;
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-hero:hover::before {
            width: 100%;
        }

        /* Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .fade-in.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scrolldown Indicator */
        .scroll-down {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 5;
        }

        .scroll-down span {
            color: white;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .scroll-down .mouse {
            width: 30px;
            height: 50px;
            border: 2px solid white;
            border-radius: 15px;
            position: relative;
        }

        .scroll-down .mouse::before {
            content: '';
            width: 4px;
            height: 10px;
            background: white;
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
            animation: scrollAnim 2s infinite;
        }

        @keyframes scrollAnim {
            0% {
                top: 8px;
                opacity: 1;
            }

            100% {
                top: 30px;
                opacity: 0;
            }
        }

        /* Other sections styling */
        .feature-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
        }

        .step-card {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            border-radius: 50%;
            margin: 0 auto;
        }

        .branch-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
        }

        .branch-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .branch-image {
            height: 200px;
            overflow: hidden;
        }

        .branch-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .branch-card:hover .branch-image img {
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3rem;
            }

            .hero-text {
                max-width: 100%;
            }

            .hero-section {
                min-height: auto;
                padding: 100px 0 150px;
            }

            .hero-image {
                margin-top: 50px;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-badge {
                width: 80px;
                height: 80px;
                top: -15px;
                right: -15px;
            }
        }
    </style>
</head>

<body>
    <!-- Include Header -->
    <div id="header-container"></div>

    <!-- Enhanced Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6 fade-in" data-delay="100">
                    <h1 class="hero-title">Experience Unity at <span class="text-warning">NIBM</span></h1>
                    <p class="hero-text">Your ultimate platform for discovering and participating in events across all
                        NIBM branches. Connect with peers, expand your horizons, and make unforgettable memories during
                        your university journey.</p>
                    <div class="d-flex flex-wrap gap-3" id="hero-buttons">
                        <a href="/nibm-unity/events/list_events.php" class="btn btn-light btn-lg btn-hero">
                            <i class="fas fa-calendar-alt me-2"></i>Explore Events
                        </a>
                        <a href="/nibm-unity/user/register.php" id="joinButton"
                            class="btn btn-outline-light btn-lg btn-hero">
                            <i class="fas fa-user-plus me-2"></i>Join the Community
                        </a>
                    </div>
                    <!-- User info display container -->
                    <div id="user-info" class="mt-3 d-none">
                        <div class="bg-white bg-opacity-25 rounded p-3 backdrop-blur">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle fa-3x text-white me-3"></i>
                                <div>
                                    <h5 class="mb-1 text-white">Welcome, <span id="welcome-username">User</span>!</h5>
                                    <div class="d-flex gap-2">
                                        <a href="/nibm-unity/user/profile.php" class="btn btn-sm btn-light">
                                            <i class="fas fa-user-edit me-1"></i>My Profile
                                        </a>
                                        <a href="/nibm-unity/user/login.php?logout=1" class="btn btn-sm btn-danger">
                                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 d-none d-lg-block position-relative fade-in" data-delay="300">
                    <div class="hero-image floating">
                        <img src="https://source.unsplash.com/random/800x600/?university,students" alt="University Life"
                            class="img-fluid rounded-4 shadow-lg">
                        <div class="hero-badge">New Events!</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-down">
            <span>Scroll down</span>
            <div class="mouse"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5 fade-in" data-delay="100">
                <h2 class="fw-bold">Why Choose NIBM Unity?</h2>
                <p class="text-muted">Discover the benefits of our platform</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 fade-in" data-delay="200">
                    <div class="feature-card p-4 text-center">
                        <div class="feature-icon mb-4">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Easy Event Management</h3>
                        <p class="text-muted mb-0">Seamlessly manage and attend events across all NIBM branches from a
                            single platform.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-in" data-delay="400">
                    <div class="feature-card p-4 text-center">
                        <div class="feature-icon mb-4">
                            <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Digital Ticketing</h3>
                        <p class="text-muted mb-0">Get instant access to your event tickets with QR codes for
                            hassle-free entry.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-in" data-delay="600">
                    <div class="feature-card p-4 text-center">
                        <div class="feature-icon mb-4">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Community Engagement</h3>
                        <p class="text-muted mb-0">Connect with students from other branches and expand your network.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5 fade-in" data-delay="100">
                <h2 class="fw-bold">How It Works</h2>
                <p class="text-muted">Get started in just a few simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 fade-in" data-delay="200">
                    <div class="step-card text-center">
                        <div class="step-number mb-3">1</div>
                        <h3 class="h5 fw-bold mb-3">Create Account</h3>
                        <p class="text-muted mb-0">Sign up for free and join our community</p>
                    </div>
                </div>
                <div class="col-md-3 fade-in" data-delay="400">
                    <div class="step-card text-center">
                        <div class="step-number mb-3">2</div>
                        <h3 class="h5 fw-bold mb-3">Browse Events</h3>
                        <p class="text-muted mb-0">Explore events from all NIBM branches</p>
                    </div>
                </div>
                <div class="col-md-3 fade-in" data-delay="600">
                    <div class="step-card text-center">
                        <div class="step-number mb-3">3</div>
                        <h3 class="h5 fw-bold mb-3">Book Tickets</h3>
                        <p class="text-muted mb-0">Secure your spot with easy booking</p>
                    </div>
                </div>
                <div class="col-md-3 fade-in" data-delay="800">
                    <div class="step-card text-center">
                        <div class="step-number mb-3">4</div>
                        <h3 class="h5 fw-bold mb-3">Attend Events</h3>
                        <p class="text-muted mb-0">Show your QR code and enjoy the event</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NIBM Branches Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5 fade-in" data-delay="100">
                <h2 class="fw-bold">NIBM Branches</h2>
                <p class="text-muted">Explore events from different branches</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 fade-in" data-delay="200">
                    <div class="branch-card">
                        <div class="branch-image">
                            <img src="https://source.unsplash.com/random/600x400/?college" alt="NIBM Colombo">
                        </div>
                        <div class="p-4">
                            <h3 class="h5 fw-bold mb-3">NIBM Colombo</h3>
                            <p class="text-muted mb-3">The main branch offering diverse events and activities.</p>
                            <a href="/nibm-unity/events/list_events.php?branch=colombo" class="btn btn-outline-primary">
                                View Events
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-in" data-delay="400">
                    <div class="branch-card">
                        <div class="branch-image">
                            <img src="https://source.unsplash.com/random/600x400/?university" alt="NIBM Kandy">
                        </div>
                        <div class="p-4">
                            <h3 class="h5 fw-bold mb-3">NIBM Kandy</h3>
                            <p class="text-muted mb-3">Experience unique events in the hill capital.</p>
                            <a href="/nibm-unity/events/list_events.php?branch=kandy" class="btn btn-outline-primary">
                                View Events
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-in" data-delay="600">
                    <div class="branch-card">
                        <div class="branch-image">
                            <img src="https://source.unsplash.com/random/600x400/?campus" alt="NIBM Kurunegala">
                        </div>
                        <div class="p-4">
                            <h3 class="h5 fw-bold mb-3">NIBM Kurunegala</h3>
                            <p class="text-muted mb-3">Discover exciting events in the cultural triangle.</p>
                            <a href="/nibm-unity/events/list_events.php?branch=kurunegala"
                                class="btn btn-outline-primary">
                                View Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center fade-in" data-delay="100">
            <h2 class="fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Join NIBM Unity today and unlock a world of opportunities</p>
            <div id="cta-buttons">
                <!-- This will be populated by JavaScript based on login status -->
                <div class="d-flex justify-content-center gap-3">
                    <a href="/nibm-unity/user/register.php" class="btn btn-light btn-lg btn-hero">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                    <a href="/nibm-unity/user/login.php" class="btn btn-outline-light btn-lg btn-hero">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <div id="footer-container"></div>

    <!-- JavaScript to handle PHP functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load header and footer
        $(document).ready(function () {
            // Load header and footer using AJAX with absolute paths
            $("#header-container").load("/nibm-unity/includes/header.html", function () {
                // The login status check is now handled inside header.html
            });

            $("#footer-container").load("/nibm-unity/includes/footer.html");

            // Initial animation for elements
            setTimeout(function () {
                animateFadeIn();
            }, 100);

            // Animation on scroll
            window.addEventListener('scroll', function () {
                animateFadeIn();
            });

            // Check login status for the CTA section and hero section
            checkLoginStatus();
        });

        // Function to check login status via AJAX
        function checkLoginStatus() {
            $.ajax({
                url: '/nibm-unity/ajax/check_login.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.loggedIn) {
                        // User is logged in
                        $("#joinButton").hide();
                        
                        // Show user info section
                        $("#user-info").removeClass('d-none');
                        $("#welcome-username").text(response.userName);

                        // Update CTA section
                        $("#cta-buttons").html(`
                            <a href="/nibm-unity/events/list_events.php" class="btn btn-light btn-lg btn-hero">
                                <i class="fas fa-calendar-alt me-2"></i>Browse Events
                            </a>
                        `);
                    }
                },
                error: function () {
                    console.log("Error checking login status");
                }
            });
        }

        // Function to animate elements on scroll
        function animateFadeIn() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(el => {
                const position = el.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;

                if (position < screenPosition) {
                    setTimeout(() => {
                        el.classList.add('active');
                    }, el.dataset.delay || 0);
                }
            });
        }
    </script>
</body>

</html>