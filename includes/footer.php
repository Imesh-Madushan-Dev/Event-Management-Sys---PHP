<?php
// Site footer
// Only show footer on non-dashboard pages
$current_page = basename($_SERVER['PHP_SELF']);
$dashboard_pages = ['dashboard.php', 'manage_events.php', 'manage_users.php', 'profile.php'];

// Check if we're in a dashboard page
$is_dashboard = in_array($current_page, $dashboard_pages);

// Get the base path if not already defined in header
if (!function_exists('getPath')) {
    $current_path = $_SERVER['PHP_SELF'];
    $path_parts = explode('/', $current_path);
    $depth = count($path_parts) - 1;
    
    $root_path = str_repeat('../', max(0, $depth - 1));
    if ($depth <= 1) {
        $root_path = './';
    }
    
    function getPath($path) {
        global $root_path;
        return $root_path . ltrim($path, '/');
    }
}

if (!$is_dashboard):
?>
    </div> <!-- End of main container -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <!-- About Section -->
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-4">About NIBM Unity</h5>
                    <p class="text-muted mb-4">NIBM Unity is your central platform for discovering and participating in events across all NIBM branches. Connect with students, attend exciting events, and make the most of your university experience.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo getPath('index.php'); ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo getPath('events/list_events.php'); ?>" class="text-muted text-decoration-none">Events</a></li>
                        <li class="mb-2"><a href="<?php echo getPath('about.php'); ?>" class="text-muted text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="<?php echo getPath('contact.php'); ?>" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>

                <!-- NIBM Branches -->
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-4">NIBM Branches</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo getPath('events/list_events.php?branch=colombo'); ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-map-marker-alt me-2"></i>Colombo
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo getPath('events/list_events.php?branch=kandy'); ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-map-marker-alt me-2"></i>Kandy
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo getPath('events/list_events.php?branch=kurunegala'); ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-map-marker-alt me-2"></i>Kurunegala
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-4">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@nibmunity.com" class="text-muted text-decoration-none">info@nibmunity.com</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:+94112345678" class="text-muted text-decoration-none">+94 11 234 5678</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <span class="text-muted">123 University Road, Colombo 03</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> NIBM Unity. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?php echo getPath('privacy.php'); ?>" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="<?php echo getPath('terms.php'); ?>" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
    // Custom JavaScript

    // Enable all tooltips
    document.addEventListener("DOMContentLoaded", function () {
        var tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function () {
            var alerts = document.querySelectorAll(".alert");
            alerts.forEach(function (alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });

    // Event liking functionality
    function toggleLike(eventId) {
        fetch('<?php echo getPath("ajax/toggle_like.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'event_id=' + eventId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeBtn = document.querySelector(`#likeBtn${eventId}`);
                const likeCount = document.querySelector(`#likeCount${eventId}`);
                if (data.liked) {
                    likeBtn.classList.add('text-danger');
                    likeBtn.classList.remove('text-muted');
                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                } else {
                    likeBtn.classList.remove('text-danger');
                    likeBtn.classList.add('text-muted');
                    likeCount.textContent = parseInt(likeCount.textContent) - 1;
                }
            }
        });
    }

    // Event attending functionality
    function toggleAttend(eventId) {
        fetch('<?php echo getPath("ajax/toggle_attend.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'event_id=' + eventId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const attendBtn = document.querySelector(`#attendBtn${eventId}`);
                const attendCount = document.querySelector(`#attendCount${eventId}`);
                if (data.attending) {
                    attendBtn.classList.add('text-success');
                    attendBtn.classList.remove('text-muted');
                    attendCount.textContent = parseInt(attendCount.textContent) + 1;
                } else {
                    attendBtn.classList.remove('text-success');
                    attendBtn.classList.add('text-muted');
                    attendCount.textContent = parseInt(attendCount.textContent) - 1;
                }
            }
        });
    }

    // QR Code generation
    function generateQRCode(ticketId) {
        fetch('<?php echo getPath("ajax/generate_qr.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ticket_id=' + ticketId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
                document.getElementById('qrCodeImage').src = data.qr_code;
                qrModal.show();
            }
        });
    }

    // Delete confirmation
    function confirmDelete(eventId) {
        if (confirm('Are you sure you want to delete this event?')) {
            window.location.href = '<?php echo getPath("admin/delete_event.php"); ?>?id=' + eventId;
        }
    }
    </script>
</body>
</html>
<?php
// Flush the output buffer and turn off output buffering
if (ob_get_length()) ob_end_flush();
endif; // End of footer visibility check
?> 