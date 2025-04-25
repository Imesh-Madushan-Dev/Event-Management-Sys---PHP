<?php
// User profile & settings
require_once '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Get user's tickets
$tickets_sql = "SELECT t.*, e.name as event_name, e.branch, e.img_url, e.created_date, t.qr_data
                FROM Tickets t 
                JOIN Events e ON t.event_id = e.event_id 
                WHERE t.user_id = ? 
                ORDER BY t.purchase_date DESC";
$stmt = mysqli_prepare($conn, $tickets_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$tickets = mysqli_stmt_get_result($stmt);

// Get user's liked events
$liked_sql = "SELECT e.*, a.name as admin_name,
              (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
              (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count
              FROM Events e
              JOIN Event_Likes el ON e.event_id = el.event_id
              JOIN Admins a ON e.admin_id = a.admin_id
              WHERE el.user_id = ?
              ORDER BY el.liked_at DESC";
$stmt = mysqli_prepare($conn, $liked_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$liked_events = mysqli_stmt_get_result($stmt);

// Get user's attending events
$attending_sql = "SELECT e.*, a.name as admin_name,
                 (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
                 (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count
                 FROM Events e
                 JOIN Event_Attendance ea ON e.event_id = ea.event_id
                 JOIN Admins a ON e.admin_id = a.admin_id
                 WHERE ea.user_id = ?
                 ORDER BY ea.attend_at DESC";
$stmt = mysqli_prepare($conn, $attending_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$attending_events = mysqli_stmt_get_result($stmt);
?>

<!-- Add some custom styles for QR codes -->
<style>
.qr-container {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 10px;
    margin-top: 15px;
    display: none; /* Initially hidden */
}

.qr-container img {
    max-width: 100%;
    height: auto;
    border: 5px solid white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.ticket-info {
    margin-top: 10px;
    padding: 10px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.event-card {
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.event-image {
    height: 180px;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.event-card:hover .event-image img {
    transform: scale(1.1);
}
</style>

<!-- Updated JavaScript for showing/hiding QR codes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update the generateQRCode function to show the container
    const originalGenerateQRCode = window.generateQRCode;
    window.generateQRCode = function(elementId, ticketCode, eventTitle, userName) {
        const container = document.getElementById(elementId);
        if (container) {
            // Toggle visibility
            if (container.style.display === 'block') {
                container.style.display = 'none';
                return;
            }
            container.style.display = 'block';
        }
        
        // Call the original function
        originalGenerateQRCode(elementId, ticketCode, eventTitle, userName);
    };
});
</script>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'https://via.placeholder.com/150'; ?>" 
                     class="profile-avatar" alt="Profile Picture">
            </div>
            <div class="col-md-9">
                <h1 class="mb-2"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-number"><?php echo mysqli_num_rows($tickets); ?></div>
                        <div class="profile-stat-label">Tickets</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-number"><?php echo mysqli_num_rows($liked_events); ?></div>
                        <div class="profile-stat-label">Liked Events</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-number"><?php echo mysqli_num_rows($attending_events); ?></div>
                        <div class="profile-stat-label">Attending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Content -->
<div class="container">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-md-3">
            <div class="dashboard-sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#tickets" data-bs-toggle="tab">
                            <i class="fas fa-ticket-alt"></i> My Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#liked" data-bs-toggle="tab">
                            <i class="fas fa-heart"></i> Liked Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#attending" data-bs-toggle="tab">
                            <i class="fas fa-calendar-check"></i> Attending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#settings" data-bs-toggle="tab">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="col-md-9">
            <div class="dashboard-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div><?php echo $_SESSION['success_message']; ?></div>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?php echo $_SESSION['error_message']; ?></div>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <div class="tab-content">
                    <!-- Tickets Tab -->
                    <div class="tab-pane fade show active" id="tickets">
                        <h3 class="mb-4">My Tickets</h3>
                        <?php if (mysqli_num_rows($tickets) > 0): ?>
                            <div class="row g-4">
                                <?php while ($ticket = mysqli_fetch_assoc($tickets)): 
                                    // Parse QR data if available
                                    $qr_data = !empty($ticket['qr_data']) ? json_decode($ticket['qr_data'], true) : null;
                                    $event_title = isset($qr_data['event_title']) ? $qr_data['event_title'] : $ticket['event_name'];
                                    $user_name = isset($qr_data['user_name']) ? $qr_data['user_name'] : $user['name'];
                                ?>
                                    <div class="col-md-6">
                                        <div class="event-card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                            <div class="event-image">
                                                <img src="<?php echo !empty($ticket['img_url']) ? $ticket['img_url'] : 'https://source.unsplash.com/random/600x400/?event,' . urlencode($ticket['event_name']); ?>" 
                                                     class="img-fluid" alt="<?php echo $ticket['event_name']; ?>">
                                            </div>
                                            <div class="card-body p-4">
                                                <h5 class="card-title fw-bold mb-3"><?php echo $ticket['event_name']; ?></h5>
                                                <p class="card-text mb-3">
                                                    <i class="fas fa-calendar me-2 text-primary"></i><?php echo date('F j, Y', strtotime($ticket['created_date'])); ?><br>
                                                    <i class="fas fa-university me-2 text-primary"></i><?php echo $ticket['branch']; ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                                        <i class="fas fa-ticket-alt me-1"></i> Ticket #<?php echo substr($ticket['ticket_code'], 0, 8); ?>
                                                    </span>
                                                    <button class="btn btn-outline-primary rounded-pill" 
                                                            onclick="generateQRCode('qr-<?php echo $ticket['ticket_id']; ?>', '<?php echo $ticket['ticket_code']; ?>', '<?php echo addslashes($event_title); ?>', '<?php echo addslashes($user_name); ?>')">
                                                        <i class="fas fa-qrcode me-2"></i> Show QR
                                                    </button>
                                                </div>
                                                <div id="qr-<?php echo $ticket['ticket_id']; ?>" class="text-center mt-3 qr-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">You haven't purchased any tickets yet.</p>
                                <a href="../events/list_events.php" class="btn btn-primary rounded-pill">
                                    <i class="fas fa-calendar-alt me-2"></i> Browse Events
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Liked Events Tab -->
                    <div class="tab-pane fade" id="liked">
                        <h3 class="mb-4">Liked Events</h3>
                        <?php if (mysqli_num_rows($liked_events) > 0): ?>
                            <div class="row g-4">
                                <?php while ($event = mysqli_fetch_assoc($liked_events)): ?>
                                    <div class="col-md-6">
                                        <div class="event-card h-100">
                                            <div class="event-image">
                                                <img src="<?php echo !empty($event['img_url']) ? $event['img_url'] : 'https://source.unsplash.com/random/600x400/?event,' . urlencode($event['name']); ?>" 
                                                     class="img-fluid" alt="<?php echo $event['name']; ?>">
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $event['name']; ?></h5>
                                                <p class="card-text"><?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-heart text-danger"></i> <?php echo $event['likes_count']; ?> likes
                                                        </small>
                                                    </div>
                                                    <a href="../events/event_details.php?id=<?php echo $event['event_id']; ?>" 
                                                       class="btn btn-sm btn-primary">View Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">You haven't liked any events yet.</p>
                                <a href="../events/list_events.php" class="btn btn-primary">Browse Events</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Attending Events Tab -->
                    <div class="tab-pane fade" id="attending">
                        <h3 class="mb-4">Events I'm Attending</h3>
                        <?php if (mysqli_num_rows($attending_events) > 0): ?>
                            <div class="row g-4">
                                <?php while ($event = mysqli_fetch_assoc($attending_events)): ?>
                                    <div class="col-md-6">
                                        <div class="event-card h-100">
                                            <div class="event-image">
                                                <img src="<?php echo !empty($event['img_url']) ? $event['img_url'] : 'https://source.unsplash.com/random/600x400/?event,' . urlencode($event['name']); ?>" 
                                                     class="img-fluid" alt="<?php echo $event['name']; ?>">
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $event['name']; ?></h5>
                                                <p class="card-text">
                                                    <i class="fas fa-calendar me-2"></i><?php echo date('F j, Y', strtotime($event['created_date'])); ?><br>
                                                    <i class="fas fa-university me-2"></i><?php echo $event['branch']; ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-users"></i> <?php echo $event['attendees_count']; ?> attending
                                                        </small>
                                                    </div>
                                                    <a href="../events/event_details.php?id=<?php echo $event['event_id']; ?>" 
                                                       class="btn btn-sm btn-primary">View Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                <p class="text-muted">You're not attending any events yet.</p>
                                <a href="../events/list_events.php" class="btn btn-primary">Browse Events</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings">
                        <h3 class="mb-4">Profile Settings</h3>
                        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" name="password">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 