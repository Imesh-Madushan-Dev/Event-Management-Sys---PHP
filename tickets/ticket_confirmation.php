<?php
// Confirmation and QR code display
require_once '../includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Please login to view ticket.";
    redirect('/user/login.php');
    exit();
}

// Check if ticket_id is provided
if (!isset($_GET['ticket_id']) || empty($_GET['ticket_id'])) {
    $_SESSION['error_message'] = "No ticket specified.";
    redirect('/user/profile.php');
    exit();
}

$ticket_id = (int)$_GET['ticket_id'];
$user_id = $_SESSION['user_id'];

// Get ticket details
$sql = "SELECT t.*, e.name as event_name, e.branch, e.description 
        FROM Tickets t
        JOIN Events e ON t.event_id = e.event_id
        WHERE t.ticket_id = ? AND t.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $ticket_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Ticket not found or does not belong to you.";
    redirect('/user/profile.php');
    exit();
}

$ticket = mysqli_fetch_assoc($result);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h2>Ticket Confirmed!</h2>
            <p class="lead text-success">Your ticket has been successfully purchased and confirmed.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="ticket-card">
                <div class="ticket-header text-center py-3">
                    <h3 class="mb-0">Event Ticket</h3>
                    <p class="mb-0"><?php echo $ticket['event_name']; ?></p>
                </div>
                <div class="ticket-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Ticket Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Attendee:</th>
                                    <td><?php echo $_SESSION['name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Event:</th>
                                    <td><?php echo $ticket['event_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Branch:</th>
                                    <td><?php echo $ticket['branch']; ?></td>
                                </tr>
                                <tr>
                                    <th>Purchase Date:</th>
                                    <td><?php echo date('M d, Y', strtotime($ticket['purchase_date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>
                                        <?php if ($ticket['price'] > 0): ?>
                                            Rs. <?php echo number_format($ticket['price'], 2); ?>
                                        <?php else: ?>
                                            Free
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 text-center">
                            <h5>Ticket Code</h5>
                            <div class="ticket-code"><?php echo $ticket['ticket_code']; ?></div>
                            <div id="qrcode" class="text-center mb-3"></div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    generateQRCode('qrcode', '<?php echo $ticket['ticket_code']; ?>');
                                });
                            </script>
                            <p class="text-muted small">Present this QR code at the event entrance</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center mt-3">
                        <button class="btn btn-primary me-2" onclick="window.print();">
                            <i class="fas fa-print"></i> Print Ticket
                        </button>
                        <a href="/user/profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user"></i> Go to My Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Important Information</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Keep your ticket code safe and don't share it with others.</li>
                        <li>You may be asked to show your ID along with this ticket at the event entrance.</li>
                        <li>This ticket is non-transferable and cannot be resold.</li>
                        <li>For any questions or issues, please contact the event organizer.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 