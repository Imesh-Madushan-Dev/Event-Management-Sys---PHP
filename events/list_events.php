<?php
// List all available events 
require_once '../includes/header.php';

// Process branch filter if provided
$branch_filter = isset($_GET['branch']) ? $_GET['branch'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Determine total number of events (for pagination)
$count_sql = "SELECT COUNT(*) as total FROM Events WHERE 1=1";
$params = [];
$types = "";

if (!empty($branch_filter)) {
    $count_sql .= " AND branch = ?";
    $params[] = $branch_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $count_sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$count_stmt = mysqli_prepare($conn, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_events = $count_row['total'];

// Pagination setup
$events_per_page = 9; // 3x3 grid
$total_pages = ceil($total_events / $events_per_page);
$current_page = isset($_GET['page']) ? max(1, min($_GET['page'], $total_pages)) : 1;
$offset = ($current_page - 1) * $events_per_page;

// Get events with pagination
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM Event_Likes WHERE event_id = e.event_id) as likes_count,
        (SELECT COUNT(*) FROM Event_Attendance WHERE event_id = e.event_id) as attendees_count
        FROM Events e
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($branch_filter)) {
    $sql .= " AND branch = ?";
    $params[] = $branch_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY created_date DESC LIMIT ? OFFSET ?";
$params[] = $events_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$events = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get all branch options for filter
$branches_sql = "SELECT DISTINCT branch FROM Events ORDER BY branch";
$branches_result = mysqli_query($conn, $branches_sql);
$branches = [];
while ($row = mysqli_fetch_assoc($branches_result)) {
    $branches[] = $row['branch'];
}
?>

<!-- Hero Section -->
<div class="events-hero">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-md-10">
                <h1 class="display-4 fw-bold text-white mb-3">
                    <?php if (!empty($branch_filter)): ?>
                        Events at <?php echo $branch_filter; ?> Branch
                    <?php elseif (!empty($search_query)): ?>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                    <?php else: ?>
                        Discover Exciting Events
                    <?php endif; ?>
                </h1>
                <p class="lead text-white-50 mb-4">
                    Browse through our collection of events, filter by branch, or search for specific events.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container events-container">
    <!-- Search & Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="row g-3">
                    <!-- Search Form -->
                    <div class="col-md-6">
                        <form action="list_events.php" method="GET" class="input-group">
                            <?php if (!empty($branch_filter)): ?>
                                <input type="hidden" name="branch" value="<?php echo htmlspecialchars($branch_filter); ?>">
                            <?php endif; ?>
                            <input type="text" name="search" class="form-control form-control-lg rounded-start-pill" 
                                   placeholder="Search events..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-primary rounded-end-pill px-4" type="submit">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </form>
                    </div>
                    
                    <!-- Branch Filter -->
                    <div class="col-md-6">
                        <div class="card-text p-2 bg-light rounded-pill">
                            <div class="d-flex align-items-center flex-wrap">
                                <span class="fw-bold ms-3 me-2 text-dark">
                                    <i class="fas fa-filter me-1"></i> Filter by:
                                </span>
                                <div class="d-flex flex-wrap">
                                    <a href="list_events.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" 
                                       class="btn btn-sm <?php echo empty($branch_filter) ? 'btn-primary' : 'btn-outline-primary'; ?> rounded-pill m-1">
                                        All Branches
                                    </a>
                                    <?php foreach ($branches as $branch): ?>
                                        <a href="list_events.php?branch=<?php echo urlencode($branch); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                                           class="btn btn-sm <?php echo $branch === $branch_filter ? 'btn-primary' : 'btn-outline-primary'; ?> rounded-pill m-1">
                                            <?php echo $branch; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info p-4 shadow-sm rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x text-info"></i>
                        </div>
                        <div>
                            <h4 class="alert-heading mb-2">No Events Found</h4>
                            <p class="mb-0">
                                <?php if (!empty($branch_filter) && !empty($search_query)): ?>
                                    No events found matching "<?php echo htmlspecialchars($search_query); ?>" at <?php echo $branch_filter; ?> branch.
                                <?php elseif (!empty($branch_filter)): ?>
                                    No events found at <?php echo $branch_filter; ?> branch.
                                <?php elseif (!empty($search_query)): ?>
                                    No events found matching "<?php echo htmlspecialchars($search_query); ?>".
                                <?php else: ?>
                                    There are no events available at this time. Please check back later.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="list_events.php" class="btn btn-lg btn-outline-primary rounded-pill">
                        <i class="fas fa-sync-alt me-2"></i> View All Events
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Events Count -->
        <div class="row mb-3">
            <div class="col-md-12">
                <p class="text-muted">
                    Found <span class="fw-bold"><?php echo $total_events; ?></span> event<?php echo $total_events != 1 ? 's' : ''; ?>
                    <?php if (!empty($branch_filter)): ?>
                        at <span class="fw-bold"><?php echo $branch_filter; ?></span> branch
                    <?php endif; ?>
                    <?php if (!empty($search_query)): ?>
                        matching "<span class="fw-bold"><?php echo htmlspecialchars($search_query); ?></span>"
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Events Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($events as $event): 
                // Check if event is new (created within last 7 days)
                $is_new = (strtotime($event['created_date']) > strtotime('-7 days'));
            ?>
                <div class="col">
                    <div class="card h-100 event-card border-0 shadow-sm rounded-4 overflow-hidden">
                        <!-- Event Image -->
                        <div class="event-image position-relative">
                            <img src="<?php echo !empty($event['img_url']) ? $event['img_url'] : 'https://source.unsplash.com/random/800x600/?event,' . urlencode($event['name']); ?>" 
                                 class="card-img-top" alt="<?php echo $event['name']; ?>">
                            
                            <!-- Event Badge -->
                            <div class="event-badges">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 rounded-pill">
                                    <i class="fas fa-university me-1"></i> <?php echo $event['branch']; ?>
                                </span>
                                
                                <?php if ($event['price'] > 0): ?>
                                    <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill">
                                        <i class="fas fa-tag me-1"></i> Rs. <?php echo number_format($event['price'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info position-absolute top-0 end-0 m-3 rounded-pill">
                                        <i class="fas fa-gift me-1"></i> Free
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($is_new): ?>
                                    <span class="badge bg-danger position-absolute bottom-0 start-0 m-3 rounded-pill">
                                        <i class="fas fa-star me-1"></i> New
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Event Content -->
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3"><?php echo $event['name']; ?></h5>
                            <p class="card-text text-muted mb-3">
                                <?php echo substr($event['description'], 0, 120) . (strlen($event['description']) > 120 ? '...' : ''); ?>
                            </p>
                            
                            <!-- Event Social Proof -->
                            <div class="d-flex justify-content-between align-items-center text-muted mb-3">
                                <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-title="People who liked this event">
                                    <i class="fas fa-heart text-danger me-1"></i>
                                    <span><?php echo $event['likes_count']; ?></span>
                                </div>
                                <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-title="People attending this event">
                                    <i class="fas fa-users text-primary me-1"></i>
                                    <span><?php echo $event['attendees_count']; ?></span>
                                </div>
                                <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-title="Event date">
                                    <i class="fas fa-calendar-alt text-info me-1"></i>
                                    <span><?php echo date('M d, Y', strtotime($event['created_date'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Footer -->
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <a href="event_details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-primary w-100 rounded-pill">
                                <i class="fas fa-info-circle me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row mt-5">
                <div class="col-md-12">
                    <nav aria-label="Events pagination">
                        <ul class="pagination justify-content-center">
                            <!-- Previous page -->
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-start-pill" href="?<?php
                                    $params = $_GET;
                                    $params['page'] = $current_page - 1;
                                    echo http_build_query($params);
                                ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Page numbers -->
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?';
                                $params = $_GET;
                                $params['page'] = 1;
                                echo http_build_query($params);
                                echo '">1</a></li>';
                                
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ';
                                echo ($i == $current_page) ? 'active' : '';
                                echo '"><a class="page-link" href="?';
                                $params = $_GET;
                                $params['page'] = $i;
                                echo http_build_query($params);
                                echo '">' . $i . '</a></li>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                
                                echo '<li class="page-item"><a class="page-link" href="?';
                                $params = $_GET;
                                $params['page'] = $total_pages;
                                echo http_build_query($params);
                                echo '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <!-- Next page -->
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-end-pill" href="?<?php
                                    $params = $_GET;
                                    $params['page'] = $current_page + 1;
                                    echo http_build_query($params);
                                ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.events-hero {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.9), rgba(0, 0, 0, 0.7)), 
               url('https://source.unsplash.com/random/1600x900/?university,event') no-repeat center center;
    background-size: cover;
    height: 250px;
    margin-top: -2rem;
    display: flex;
    align-items: center;
    border-radius: 0 0 30px 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.events-container {
    margin-top: -50px;
    margin-bottom: 50px;
    position: relative;
    z-index: 10;
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

.event-badges .badge {
    padding: 8px 12px;
    font-size: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-title {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.page-link {
    color: #0d6efd;
    border: none;
    padding: 0.6rem 1rem;
    margin: 0 0.2rem;
    transition: all 0.3s;
}

.page-link:hover {
    background-color: #e9ecef;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<script>
// Initialize dropdowns when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown toggle elements
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    
    // Initialize Bootstrap dropdowns for each element
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl)
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>