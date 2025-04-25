<?php
// CRUD for events
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in as an admin to access this page.";
    redirect('/user/login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get all branches for the form
$branches = getNIBMBranches();

// Handle event actions (create, update, delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create or Update event
    if (isset($_POST['save_event'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $img_url = sanitize($_POST['img_url']);
        $price = empty($_POST['price']) ? null : (float)$_POST['price'];
        $branch = sanitize($_POST['branch']);
        
        // Validate input
        if (empty($name) || empty($description) || empty($branch)) {
            $_SESSION['error_message'] = "Name, description, and branch are required.";
        } else {
            if ($_POST['event_id'] > 0) {
                // Update existing event
                $sql = "UPDATE Events SET name = ?, description = ?, img_url = ?, price = ?, branch = ? WHERE event_id = ? AND admin_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssdsis", $name, $description, $img_url, $price, $branch, $_POST['event_id'], $admin_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Event updated successfully!";
                    redirect('manage_events.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = "Failed to update event. Please try again.";
                }
            } else {
                // Create new event
                $sql = "INSERT INTO Events (admin_id, name, description, img_url, price, branch) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "isssds", $admin_id, $name, $description, $img_url, $price, $branch);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Event created successfully!";
                    redirect('manage_events.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = "Failed to create event. Please try again.";
                }
            }
        }
    }
    
    // Delete event
    if (isset($_POST['delete_event'])) {
        $event_id_to_delete = (int)$_POST['event_id'];
        
        $sql = "DELETE FROM Events WHERE event_id = ? AND admin_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $event_id_to_delete, $admin_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Event deleted successfully!";
            redirect('manage_events.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to delete event. Please try again.";
        }
    }
}

// Get event details if editing
$event = null;
if ($action == 'edit' && $event_id > 0) {
    $sql = "SELECT * FROM Events WHERE event_id = ? AND admin_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $event = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error_message'] = "Event not found or you don't have permission to edit it.";
        redirect('manage_events.php');
        exit();
    }
}

// Get all events for this admin
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
        (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count,
        (SELECT COUNT(*) FROM Tickets WHERE event_id = e.event_id) as tickets_count
        FROM Events e
        WHERE e.admin_id = ?
        ORDER BY e.created_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?php echo ($action == 'create' || $action == 'edit') ? ($action == 'create' ? 'Create New Event' : 'Edit Event') : 'Manage Events'; ?></h1>
        </div>
        <div class="col-md-4 text-end">
            <?php if ($action != 'create' && $action != 'edit'): ?>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
            <?php else: ?>
                <a href="manage_events.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($action == 'create' || $action == 'edit'): ?>
        <!-- Event Form -->
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?php echo $action == 'create' ? 'Create New Event' : 'Edit Event'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="event_id" value="<?php echo $event ? $event['event_id'] : 0; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Event Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $event ? $event['name'] : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $event ? $event['description'] : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="img_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="img_url" name="img_url" value="<?php echo $event ? $event['img_url'] : ''; ?>" placeholder="https://example.com/image.jpg">
                                <small class="text-muted">Leave empty for a default image</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (Rs.)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $event ? $event['price'] : ''; ?>" placeholder="Leave empty for free events">
                            </div>
                            
                            <div class="mb-3">
                                <label for="branch" class="form-label">Branch</label>
                                <select class="form-select" id="branch" name="branch" required>
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo $branch; ?>" <?php echo ($event && $event['branch'] == $branch) ? 'selected' : ''; ?>>
                                            <?php echo $branch; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="save_event" class="btn btn-primary">
                                    <?php echo $action == 'create' ? 'Create Event' : 'Update Event'; ?>
                                </button>
                                <a href="manage_events.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Events List -->
        <div class="row">
            <div class="col-md-12">
                <?php if (empty($events)): ?>
                    <div class="alert alert-info">
                        <p class="mb-0">You haven't created any events yet. <a href="?action=create" class="alert-link">Create your first event</a>.</p>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Event Name</th>
                                            <th>Branch</th>
                                            <th>Price</th>
                                            <th>Stats</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td><?php echo $event['name']; ?></td>
                                                <td><?php echo $event['branch']; ?></td>
                                                <td>
                                                    <?php if ($event['price'] > 0): ?>
                                                        Rs. <?php echo number_format($event['price'], 2); ?>
                                                    <?php else: ?>
                                                        <span class="text-success">Free</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger me-1" title="Likes">
                                                        <i class="fas fa-heart"></i> <?php echo $event['likes_count']; ?>
                                                    </span>
                                                    <span class="badge bg-success me-1" title="Attendees">
                                                        <i class="fas fa-users"></i> <?php echo $event['attendees_count']; ?>
                                                    </span>
                                                    <span class="badge bg-primary" title="Tickets Sold">
                                                        <i class="fas fa-ticket-alt"></i> <?php echo $event['tickets_count']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($event['created_date'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="/events/event_details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-primary" title="View Event">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="?action=edit&id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit Event">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="" id="delete-form-<?php echo $event['event_id']; ?>" class="d-inline">
                                                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Event" 
                                                                    onclick="confirmDelete('Are you sure you want to delete this event?', 'delete-form-<?php echo $event['event_id']; ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <button type="submit" name="delete_event" class="d-none"></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?> 