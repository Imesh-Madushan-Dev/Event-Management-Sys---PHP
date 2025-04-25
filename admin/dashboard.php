<?php
// Admin dashboard overview
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in as an admin to access this page.";
    redirect('/user/login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get admin's events count
$events_sql = "SELECT COUNT(*) as total_events FROM Events WHERE admin_id = ?";
$events_stmt = mysqli_prepare($conn, $events_sql);
mysqli_stmt_bind_param($events_stmt, "i", $admin_id);
mysqli_stmt_execute($events_stmt);
$events_result = mysqli_stmt_get_result($events_stmt);
$events_count = mysqli_fetch_assoc($events_result)['total_events'];

// Get total users count
$users_sql = "SELECT COUNT(*) as total_users FROM Users";
$users_result = mysqli_query($conn, $users_sql);
$users_count = mysqli_fetch_assoc($users_result)['total_users'];

// Get total likes for admin's events
$likes_sql = "SELECT COUNT(*) as total_likes 
              FROM Event_Likes el
              JOIN Events e ON el.event_id = e.event_id
              WHERE e.admin_id = ?";
$likes_stmt = mysqli_prepare($conn, $likes_sql);
mysqli_stmt_bind_param($likes_stmt, "i", $admin_id);
mysqli_stmt_execute($likes_stmt);
$likes_result = mysqli_stmt_get_result($likes_stmt);
$likes_count = mysqli_fetch_assoc($likes_result)['total_likes'];

// Get total attendees for admin's events
$attendees_sql = "SELECT COUNT(*) as total_attendees 
                 FROM Event_Attendance ea
                 JOIN Events e ON ea.event_id = e.event_id
                 WHERE e.admin_id = ?";
$attendees_stmt = mysqli_prepare($conn, $attendees_sql);
mysqli_stmt_bind_param($attendees_stmt, "i", $admin_id);
mysqli_stmt_execute($attendees_stmt);
$attendees_result = mysqli_stmt_get_result($attendees_stmt);
$attendees_count = mysqli_fetch_assoc($attendees_result)['total_attendees'];

// Get total tickets sold for admin's events
$tickets_sql = "SELECT COUNT(*) as total_tickets, SUM(t.price) as total_revenue
               FROM Tickets t
               JOIN Events e ON t.event_id = e.event_id
               WHERE e.admin_id = ?";
$tickets_stmt = mysqli_prepare($conn, $tickets_sql);
mysqli_stmt_bind_param($tickets_stmt, "i", $admin_id);
mysqli_stmt_execute($tickets_stmt);
$tickets_result = mysqli_stmt_get_result($tickets_stmt);
$tickets_data = mysqli_fetch_assoc($tickets_result);
$tickets_count = $tickets_data['total_tickets'];
$total_revenue = $tickets_data['total_revenue'] ?: 0;

// Get recent events created by admin
$recent_events_sql = "SELECT * FROM Events WHERE admin_id = ? ORDER BY created_date DESC LIMIT 5";
$recent_events_stmt = mysqli_prepare($conn, $recent_events_sql);
mysqli_stmt_bind_param($recent_events_stmt, "i", $admin_id);
mysqli_stmt_execute($recent_events_stmt);
$recent_events_result = mysqli_stmt_get_result($recent_events_stmt);
$recent_events = mysqli_fetch_all($recent_events_result, MYSQLI_ASSOC);

// Get most popular events (by likes)
$popular_events_sql = "SELECT e.*, COUNT(el.event_like_id) as likes_count
                      FROM Events e
                      LEFT JOIN Event_Likes el ON e.event_id = el.event_id
                      WHERE e.admin_id = ?
                      GROUP BY e.event_id
                      ORDER BY likes_count DESC
                      LIMIT 5";
$popular_events_stmt = mysqli_prepare($conn, $popular_events_sql);
mysqli_stmt_bind_param($popular_events_stmt, "i", $admin_id);
mysqli_stmt_execute($popular_events_stmt);
$popular_events_result = mysqli_stmt_get_result($popular_events_stmt);
$popular_events = mysqli_fetch_all($popular_events_result, MYSQLI_ASSOC);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Admin Dashboard</h1>
            <p class="text-muted">Welcome back, <?php echo $_SESSION['name']; ?>!</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="manage_events.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Event
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card stats-card primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Events</h6>
                            <h3 class="mb-0"><?php echo $events_count; ?></h3>
                        </div>
                        <div class="stats-icon text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Likes</h6>
                            <h3 class="mb-0"><?php echo $likes_count; ?></h3>
                        </div>
                        <div class="stats-icon text-success">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Attendees</h6>
                            <h3 class="mb-0"><?php echo $attendees_count; ?></h3>
                        </div>
                        <div class="stats-icon text-info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Tickets Sold</h6>
                            <h3 class="mb-0"><?php echo $tickets_count; ?></h3>
                            <small class="text-muted">Rs. <?php echo number_format($total_revenue, 2); ?> Revenue</small>
                        </div>
                        <div class="stats-icon text-warning">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Events -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Events</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_events)): ?>
                        <p class="text-muted">No events created yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Branch</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                        <tr>
                                            <td><?php echo $event['name']; ?></td>
                                            <td><?php echo $event['branch']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($event['created_date'])); ?></td>
                                            <td>
                                                <a href="/events/event_details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="manage_events.php" class="btn btn-outline-primary btn-sm">View All Events</a>
                </div>
            </div>
        </div>
        
        <!-- Popular Events -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Most Popular Events</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($popular_events)): ?>
                        <p class="text-muted">No events have been liked yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Branch</th>
                                        <th>Likes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_events as $event): ?>
                                        <tr>
                                            <td><?php echo $event['name']; ?></td>
                                            <td><?php echo $event['branch']; ?></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?php echo $event['likes_count']; ?> <i class="fas fa-heart"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/events/event_details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="manage_events.php" class="btn btn-outline-primary btn-sm">Manage Events</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Admin Actions & Quick Links -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="manage_events.php" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-alt"></i> Manage Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_users.php" class="btn btn-success w-100">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/events/list_events.php" class="btn btn-info w-100 text-white">
                                <i class="fas fa-list"></i> View All Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_events.php?action=create" class="btn btn-warning w-100">
                                <i class="fas fa-plus"></i> Create New Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 