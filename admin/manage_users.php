<?php
// CRUD for user accounts
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in as an admin to access this page.";
    redirect('/user/login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$error = '';
$success = '';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    $sql = "DELETE FROM Users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "User deleted successfully!";
        redirect('/admin/manage_users.php');
        exit();
    } else {
        $error = "Failed to delete user. Please try again.";
    }
}

// Get search parameters
$search_term = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build SQL query
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM Event_Likes WHERE user_id = u.user_id) as likes_count,
        (SELECT COUNT(*) FROM Event_Attendance WHERE user_id = u.user_id) as attendance_count,
        (SELECT COUNT(*) FROM Tickets WHERE user_id = u.user_id) as tickets_count
        FROM Users u
        WHERE 1=1";

// Add search filter if specified
if (!empty($search_term)) {
    $sql .= " AND (u.name LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%' 
              OR u.email LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%')";
}

// Add sorting
switch ($sort_by) {
    case 'name_asc':
        $sql .= " ORDER BY u.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY u.name DESC";
        break;
    case 'email':
        $sql .= " ORDER BY u.email ASC";
        break;
    case 'likes':
        $sql .= " ORDER BY likes_count DESC";
        break;
    case 'attendance':
        $sql .= " ORDER BY attendance_count DESC";
        break;
    case 'tickets':
        $sql .= " ORDER BY tickets_count DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY u.user_id ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY u.user_id DESC";
        break;
}

$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Count total users
$count_sql = "SELECT COUNT(*) as total FROM Users";
$count_result = mysqli_query($conn, $count_sql);
$total_users = mysqli_fetch_assoc($count_result)['total'];
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Manage Users</h1>
            <p class="text-muted">Total users: <?php echo $total_users; ?></p>
        </div>
    </div>
    
    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <!-- Search Box -->
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Name or email" value="<?php echo $search_term; ?>">
                        </div>
                        
                        <!-- Sort By -->
                        <div class="col-md-4">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo ($sort_by === 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="email" <?php echo ($sort_by === 'email') ? 'selected' : ''; ?>>Email</option>
                                <option value="likes" <?php echo ($sort_by === 'likes') ? 'selected' : ''; ?>>Most Likes</option>
                                <option value="attendance" <?php echo ($sort_by === 'attendance') ? 'selected' : ''; ?>>Most Attendance</option>
                                <option value="tickets" <?php echo ($sort_by === 'tickets') ? 'selected' : ''; ?>>Most Tickets</option>
                            </select>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($users)): ?>
                <div class="alert alert-info">
                    <p class="mb-0">No users found <?php echo !empty($search_term) ? "matching '$search_term'" : ''; ?>.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Activity Stats</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td><?php echo $user['name']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td>
                                                <span class="badge bg-danger me-1" data-bs-toggle="tooltip" title="Likes">
                                                    <i class="fas fa-heart"></i> <?php echo $user['likes_count']; ?>
                                                </span>
                                                <span class="badge bg-success me-1" data-bs-toggle="tooltip" title="Events Attending">
                                                    <i class="fas fa-users"></i> <?php echo $user['attendance_count']; ?>
                                                </span>
                                                <span class="badge bg-primary" data-bs-toggle="tooltip" title="Tickets Purchased">
                                                    <i class="fas fa-ticket-alt"></i> <?php echo $user['tickets_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" id="delete-form-<?php echo $user['user_id']; ?>" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete('Are you sure you want to delete this user? All associated data will be lost.', 'delete-form-<?php echo $user['user_id']; ?>')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                    <button type="submit" name="delete_user" class="d-none"></button>
                                                </form>
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
    
    <!-- Admin Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="dashboard.php" class="btn btn-primary w-100">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_events.php" class="btn btn-success w-100">
                                <i class="fas fa-calendar-alt"></i> Manage Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/events/list_events.php" class="btn btn-info w-100 text-white">
                                <i class="fas fa-list"></i> View Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_events.php?action=create" class="btn btn-warning w-100">
                                <i class="fas fa-plus"></i> Create Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 