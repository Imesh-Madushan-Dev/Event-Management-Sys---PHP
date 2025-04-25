<?php
// Ticket purchase page
require_once '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to purchase tickets.";
    redirect('/user/login.php');
    exit();
}

// Check if event_id is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    $_SESSION['error_message'] = "No event specified.";
    redirect('/events/list_events.php');
    exit();
}

$event_id = (int)$_GET['event_id'];
$user_id = $_SESSION['user_id'];

// Get event details
$event = getEventById($conn, $event_id);

if (!$event) {
    $_SESSION['error_message'] = "Event not found.";
    redirect('/events/list_events.php');
    exit();
}

// Check if user already has a ticket for this event
$ticket_check_sql = "SELECT 1 FROM Tickets WHERE user_id = ? AND event_id = ?";
$ticket_check_stmt = mysqli_prepare($conn, $ticket_check_sql);
mysqli_stmt_bind_param($ticket_check_stmt, "ii", $user_id, $event_id);
mysqli_stmt_execute($ticket_check_stmt);
mysqli_stmt_store_result($ticket_check_stmt);

if (mysqli_stmt_num_rows($ticket_check_stmt) > 0) {
    $_SESSION['error_message'] = "You already have a ticket for this event.";
    redirect("/events/event_details.php?id=$event_id");
    exit();
}

// Process ticket purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase_ticket'])) {
    // Generate unique ticket code
    $ticket_code = generateTicketCode();
    $price = $event['price'];
    
    // Insert ticket
    $insert_sql = "INSERT INTO Tickets (event_id, user_id, ticket_code, price, purchase_date) 
                  VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "iiss", $event_id, $user_id, $ticket_code, $price);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        // Get the ticket_id of the newly inserted ticket
        $ticket_id = mysqli_insert_id($conn);
        $_SESSION['success_message'] = "Ticket purchased successfully!";
        redirect("/tickets/ticket_confirmation.php?ticket_id=$ticket_id");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to purchase ticket. Please try again.";
    }
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="/events/list_events.php">Events</a></li>
                    <li class="breadcrumb-item"><a href="/events/event_details.php?id=<?php echo $event_id; ?>"><?php echo $event['name']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Purchase Ticket</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Purchase Ticket</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="<?php echo !empty($event['img_url']) ? $event['img_url'] : 'https://source.unsplash.com/random/300x200/?event,' . urlencode($event['name']); ?>" class="img-fluid rounded" alt="<?php echo $event['name']; ?>">
                        </div>
                        <div class="col-md-8">
                            <h5><?php echo $event['name']; ?></h5>
                            <p class="text-muted"><?php echo $event['branch']; ?> Branch</p>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Ticket Price:</span>
                                <strong>
                                    <?php if ($event['price'] > 0): ?>
                                        Rs. <?php echo number_format($event['price'], 2); ?>
                                    <?php else: ?>
                                        Free
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Attendee Information</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $_SESSION['name']; ?>" readonly>
                            <small class="text-muted">The name on your account will be used for this ticket.</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" readonly>
                            <small class="text-muted">Ticket confirmation will be sent to this email.</small>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Payment Information</h5>
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required <?php echo $event['price'] <= 0 ? 'disabled' : ''; ?>>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expiry" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry" name="expiry" placeholder="MM/YY" required <?php echo $event['price'] <= 0 ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required <?php echo $event['price'] <= 0 ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="card_name" class="form-label">Name on Card</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" placeholder="John Smith" required <?php echo $event['price'] <= 0 ? 'disabled' : ''; ?>>
                        </div>
                        
                        <?php if ($event['price'] <= 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> This is a free event. No payment information is required.
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="purchase_ticket" class="btn btn-primary btn-lg">
                                <?php if ($event['price'] > 0): ?>
                                    Pay Rs. <?php echo number_format($event['price'], 2); ?> and Confirm Ticket
                                <?php else: ?>
                                    Confirm Free Ticket
                                <?php endif; ?>
                            </button>
                            <a href="/events/event_details.php?id=<?php echo $event_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 