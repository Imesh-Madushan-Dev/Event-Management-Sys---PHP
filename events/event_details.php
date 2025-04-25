<?php
// Detailed view of a single event
require_once '../includes/header.php';

// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "No event specified.";
    redirect('list_events.php');
    exit();
}

$event_id = (int)$_GET['id'];

// Get event details
$sql = "SELECT e.*, a.name as admin_name,
        (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
        (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count
        FROM Events e
        JOIN Admins a ON e.admin_id = a.admin_id
        WHERE e.event_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Event not found.";
    redirect('list_events.php');
    exit();
}

$event = mysqli_fetch_assoc($result);

// Check if user has liked this event
$has_liked = false;
$is_attending = false;
$has_ticket = false;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    // Check if user has liked the event
    $has_liked = hasUserLikedEvent($conn, $user_id, $event_id);
    
    // Check if user is attending the event
    $is_attending = isUserAttendingEvent($conn, $user_id, $event_id);
    
    // Check if user has purchased a ticket
    $ticket_sql = "SELECT 1 FROM Tickets WHERE user_id = ? AND event_id = ?";
    $ticket_stmt = mysqli_prepare($conn, $ticket_sql);
    mysqli_stmt_bind_param($ticket_stmt, "ii", $user_id, $event_id);
    mysqli_stmt_execute($ticket_stmt);
    mysqli_stmt_store_result($ticket_stmt);
    $has_ticket = mysqli_stmt_num_rows($ticket_stmt) > 0;
}

// Process like action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_event'])) {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Please login to like events.";
        redirect('../user/login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // If user already liked the event, unlike it
    if ($has_liked) {
        $unlike_sql = "DELETE FROM Event_Likes WHERE user_id = ? AND event_id = ?";
        $unlike_stmt = mysqli_prepare($conn, $unlike_sql);
        mysqli_stmt_bind_param($unlike_stmt, "ii", $user_id, $event_id);
        
        if (mysqli_stmt_execute($unlike_stmt)) {
            $has_liked = false;
            $event['likes_count']--;
            $_SESSION['success_message'] = "You have unliked this event.";
        } else {
            $_SESSION['error_message'] = "Failed to unlike the event.";
        }
    } else {
        // Like the event
        $like_sql = "INSERT INTO Event_Likes (user_id, event_id) VALUES (?, ?)";
        $like_stmt = mysqli_prepare($conn, $like_sql);
        mysqli_stmt_bind_param($like_stmt, "ii", $user_id, $event_id);
        
        if (mysqli_stmt_execute($like_stmt)) {
            $has_liked = true;
            $event['likes_count']++;
            $_SESSION['success_message'] = "You have liked this event!";
        } else {
            $_SESSION['error_message'] = "Failed to like the event.";
        }
    }
    
    // Redirect to refresh page and avoid form resubmission
    redirect("event_details.php?id=$event_id");
    exit();
}

// Process attend action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attend_event'])) {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Please login to mark attendance.";
        redirect('../user/login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // If user already marked attendance, un-mark it
    if ($is_attending) {
        $unattend_sql = "DELETE FROM Event_Attendance WHERE user_id = ? AND event_id = ?";
        $unattend_stmt = mysqli_prepare($conn, $unattend_sql);
        mysqli_stmt_bind_param($unattend_stmt, "ii", $user_id, $event_id);
        
        if (mysqli_stmt_execute($unattend_stmt)) {
            $is_attending = false;
            $event['attendees_count']--;
            $_SESSION['success_message'] = "You are no longer marked as attending this event.";
        } else {
            $_SESSION['error_message'] = "Failed to update attendance.";
        }
    } else {
        // Mark attendance
        $attend_sql = "INSERT INTO Event_Attendance (user_id, event_id) VALUES (?, ?)";
        $attend_stmt = mysqli_prepare($conn, $attend_sql);
        mysqli_stmt_bind_param($attend_stmt, "ii", $user_id, $event_id);
        
        if (mysqli_stmt_execute($attend_stmt)) {
            $is_attending = true;
            $event['attendees_count']++;
            $_SESSION['success_message'] = "You are now attending this event!";
        } else {
            $_SESSION['error_message'] = "Failed to mark attendance.";
        }
    }
    
    // Redirect to refresh page and avoid form resubmission
    redirect("event_details.php?id=$event_id");
    exit();
}

// Process ticket purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase_ticket'])) {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Please login to purchase tickets.";
        redirect('../user/login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Check if user already has a ticket
    if ($has_ticket) {
        $_SESSION['error_message'] = "You already have a ticket for this event.";
        redirect("event_details.php?id=$event_id");
        exit();
    }
    
    // Get user name for ticket data
    $user_sql = "SELECT name FROM Users WHERE user_id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user_row = mysqli_fetch_assoc($user_result);
    $user_name = $user_row['name'];
    
    // Create ticket data with event title and user name for QR code
    $event_title = $event['name'];
    $qr_data = json_encode([
        'ticket_id' => uniqid(),
        'event_id' => $event_id,
        'event_title' => $event_title,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'timestamp' => time()
    ]);
    
    // Generate ticket code with all relevant information
    $ticket_code = strtoupper(uniqid() . bin2hex(random_bytes(4)));
    
    // Insert ticket record
    $price = $event['price'] ?: 0; // Use event price or 0 if null
    $ticket_sql = "INSERT INTO Tickets (user_id, event_id, ticket_code, price, qr_data) VALUES (?, ?, ?, ?, ?)";
    $ticket_stmt = mysqli_prepare($conn, $ticket_sql);
    mysqli_stmt_bind_param($ticket_stmt, "iisds", $user_id, $event_id, $ticket_code, $price, $qr_data);
    
    if (mysqli_stmt_execute($ticket_stmt)) {
        $_SESSION['success_message'] = "Ticket purchased successfully!";
        $has_ticket = true;
    } else {
        $_SESSION['error_message'] = "Failed to purchase ticket. Please try again.";
    }
    
    // Redirect to refresh page and avoid form resubmission
    redirect("event_details.php?id=$event_id");
    exit();
}
?>

<!-- Event Hero Section -->
<div class="event-hero">
    <div class="container position-relative">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb" class="event-breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="list_events.php">Events</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $event['name']; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container event-details-container">
    <div class="row">
        <!-- Event Details Card -->
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden event-main-card">
                <div class="row g-0">
                    <!-- Event Image -->
                    <div class="col-lg-6 event-image-container">
                        <img src="<?php echo !empty($event['img_url']) ? $event['img_url'] : 'https://source.unsplash.com/random/800x600/?event,' . urlencode($event['name']); ?>" 
                             class="event-image" alt="<?php echo $event['name']; ?>">
                             
                        <div class="event-badges">
                            <span class="badge bg-primary me-2">
                                <i class="fas fa-university"></i> <?php echo $event['branch']; ?>
                            </span>
                            <?php if ($event['price'] > 0): ?>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-tag"></i> Rs. <?php echo number_format($event['price'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-info me-2">
                                    <i class="fas fa-gift"></i> Free
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-secondary me-2">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($event['created_date'])); ?>
                            </span>
                            <span class="badge bg-dark">
                                <i class="fas fa-user-tie"></i> By: <?php echo $event['admin_name']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Event Details -->
                    <div class="col-lg-6">
                        <div class="event-content">
                            <h1 class="display-5 fw-bold mb-3"><?php echo $event['name']; ?></h1>
                            <div class="event-description mb-4">
                                <?php echo nl2br($event['description']); ?>
                            </div>
                            
                            <!-- Event Stats -->
                            <div class="event-stats mb-4">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="event-stat-card bg-light text-center p-3 rounded-4">
                                            <div class="stat-icon mb-2 text-primary">
                                                <i class="fas fa-heart"></i>
                                            </div>
                                            <div class="stat-value fw-bold fs-4"><?php echo $event['likes_count']; ?></div>
                                            <div class="stat-label small text-muted">Likes</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="event-stat-card bg-light text-center p-3 rounded-4">
                                            <div class="stat-icon mb-2 text-success">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="stat-value fw-bold fs-4"><?php echo $event['attendees_count']; ?></div>
                                            <div class="stat-label small text-muted">Attending</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="event-stat-card bg-light p-3 rounded-4">
                                            <div class="d-flex align-items-center">
                                                <div class="stat-icon me-3 fs-1 text-info">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div>
                                                    <div class="stat-value fw-bold"><?php echo $event['branch']; ?> Campus</div>
                                                    <div class="stat-label small text-muted">Event Location</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="event-actions">
                                <?php if (isLoggedIn()): ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <!-- Like Button -->
                                        <form method="POST" action="" class="d-inline-block">
                                            <button type="submit" name="like_event" class="btn btn-lg <?php echo $has_liked ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                                <i class="<?php echo $has_liked ? 'fas' : 'far'; ?> fa-heart me-2"></i> 
                                                <?php echo $has_liked ? 'Unlike' : 'Like'; ?>
                                            </button>
                                        </form>
                                        
                                        <!-- Attend Button -->
                                        <form method="POST" action="" class="d-inline-block">
                                            <button type="submit" name="attend_event" class="btn btn-lg <?php echo $is_attending ? 'btn-success' : 'btn-outline-success'; ?>">
                                                <i class="<?php echo $is_attending ? 'fas' : 'far'; ?> fa-check-circle me-2"></i> 
                                                <?php echo $is_attending ? 'Attending' : 'Attend'; ?>
                                            </button>
                                        </form>
                                        
                                        <!-- Purchase Ticket Button (only if has price and user doesn't have ticket yet) -->
                                        <?php if (!$has_ticket): ?>
                                            <form method="POST" action="" class="d-inline-block">
                                                <button type="submit" name="purchase_ticket" class="btn btn-lg btn-primary">
                                                    <i class="fas fa-ticket-alt me-2"></i> Purchase Ticket
                                                    <?php if ($event['price'] > 0): ?>
                                                        (Rs. <?php echo number_format($event['price'], 2); ?>)
                                                    <?php else: ?>
                                                        (Free)
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="../user/profile.php" class="btn btn-lg btn-outline-primary">
                                                <i class="fas fa-ticket-alt me-2"></i> View Your Ticket
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="login-prompt p-4 bg-light rounded-4 text-center">
                                        <p class="mb-3">Please login to interact with this event and purchase tickets.</p>
                                        <a href="../user/login.php" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </a>
                                        <a href="../user/register.php" class="btn btn-outline-primary btn-lg ms-2">
                                            <i class="fas fa-user-plus me-2"></i>Register
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Events -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="section-title">
                <i class="fas fa-university me-2"></i>
                More Events at <?php echo $event['branch']; ?>
            </h3>
            <div class="section-divider mb-4"></div>
        </div>
        
        <?php
        // Get other events from the same branch
        $related_sql = "SELECT e.*, 
                        (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
                        (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count
                        FROM Events e
                        WHERE e.branch = ? AND e.event_id != ?
                        ORDER BY e.created_date DESC
                        LIMIT 3";
        $related_stmt = mysqli_prepare($conn, $related_sql);
        mysqli_stmt_bind_param($related_stmt, "si", $event['branch'], $event_id);
        mysqli_stmt_execute($related_stmt);
        $related_result = mysqli_stmt_get_result($related_stmt);
        $related_events = mysqli_fetch_all($related_result, MYSQLI_ASSOC);
        
        if (empty($related_events)):
        ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No other events found at this branch.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($related_events as $related_event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card event-card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="position-relative event-image">
                            <img src="<?php echo !empty($related_event['img_url']) ? $related_event['img_url'] : 'https://source.unsplash.com/random/600x400/?event,' . urlencode($related_event['name']); ?>" class="card-img-top" alt="<?php echo $related_event['name']; ?>">
                            <?php if ($related_event['price'] > 0): ?>
                                <div class="event-price badge bg-success position-absolute top-0 end-0 m-3">
                                    <i class="fas fa-tag me-1"></i> Rs. <?php echo number_format($related_event['price'], 2); ?>
                                </div>
                            <?php else: ?>
                                <div class="event-price badge bg-info position-absolute top-0 end-0 m-3">
                                    <i class="fas fa-gift me-1"></i> Free
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $related_event['name']; ?></h5>
                            <p class="card-text"><?php echo substr($related_event['description'], 0, 100) . (strlen($related_event['description']) > 100 ? '...' : ''); ?></p>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center border-0">
                            <small class="text-muted">
                                <i class="fas fa-heart text-danger me-1"></i> <?php echo $related_event['likes_count']; ?> &nbsp;
                                <i class="fas fa-users text-primary me-1"></i> <?php echo $related_event['attendees_count']; ?>
                            </small>
                            <a href="event_details.php?id=<?php echo $related_event['event_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle me-1"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Back to Events Button -->
    <div class="row mt-4 mb-5">
        <div class="col-12 text-center">
            <a href="list_events.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Back to All Events
            </a>
        </div>
    </div>
</div>

<style>
.event-hero {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.9), rgba(0, 0, 0, 0.7)), 
               url('https://source.unsplash.com/random/1600x900/?university,event') no-repeat center center;
    background-size: cover;
    height: 150px;
    margin-top: -2rem;
    position: relative;
    border-radius: 0 0 30px 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.event-breadcrumb {
    position: absolute;
    bottom: 15px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    backdrop-filter: blur(5px);
}

.event-breadcrumb .breadcrumb {
    margin-bottom: 0;
}

.event-breadcrumb .breadcrumb-item a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
}

.event-breadcrumb .breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.8);
}

.event-details-container {
    margin-top: -50px;
    margin-bottom: 50px;
    position: relative;
    z-index: 10;
}

.event-main-card {
    overflow: hidden;
}

.event-image-container {
    position: relative;
    height: 100%;
    min-height: 400px;
}

.event-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-badges {
    position: absolute;
    bottom: 20px;
    left: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.event-badges .badge {
    font-size: 0.9rem;
    padding: 8px 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.event-content {
    padding: 2.5rem;
}

.event-description {
    line-height: 1.8;
    color: #495057;
}

.event-stat-card {
    transition: all 0.3s ease;
}

.event-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.event-actions {
    margin-top: 2rem;
}

.event-card {
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}

.event-image {
    height: 200px;
    overflow: hidden;
}

.event-card .event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.event-card:hover .event-image img {
    transform: scale(1.1);
}

.section-title {
    position: relative;
    display: inline-block;
    font-weight: 600;
    color: #212529;
}

.section-divider {
    height: 4px;
    width: 100px;
    background: linear-gradient(to right, #0d6efd, #0dcaf0);
    border-radius: 2px;
}

@media (max-width: 991.98px) {
    .event-content {
        padding: 1.5rem;
    }
    
    .event-image-container {
        min-height: 300px;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?> 