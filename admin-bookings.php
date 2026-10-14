<?php
require_once 'includes/config.php';
$page_title = "Manage Bookings";

// Check if admin
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied! Admin only.');
    header("Location: index.php");
    exit;
}

require_once 'includes/booking-mailer.php'; // Include email functions

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    $stmt->execute();
    $stmt->close();
    
    set_flash_message('success', 'Booking status updated successfully!');
    header("Location: admin-bookings.php");
    exit;
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $booking_id = $_POST['booking_id'];
    $payment_status = $_POST['payment_status'];
    
    // Get booking details before updating
    $booking_query = "SELECT b.*, u.username, u.email, d.name as destination_name 
                      FROM bookings b
                      LEFT JOIN users u ON b.user_id = u.user_id
                      LEFT JOIN destinations d ON b.destination_id = d.destination_id
                      WHERE b.booking_id = ?";
    $stmt_booking = $conn->prepare($booking_query);
    $stmt_booking->bind_param("i", $booking_id);
    $stmt_booking->execute();
    $result_booking = $stmt_booking->get_result();
    $booking_data = $result_booking->fetch_assoc();
    $stmt_booking->close();
    
    // Update payment status
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $payment_status, $booking_id);
    $stmt->execute();
    $stmt->close();
    
    // Send payment confirmation email if status changed to "paid"
    if ($payment_status === 'paid' && $booking_data && !empty($booking_data['email'])) {
        $email_sent = sendPaymentConfirmationEmail(
            $booking_data['email'],
            $booking_data['username'],
            $booking_id,
            number_format($booking_data['total_price'], 0),
            $booking_data['destination_name']
        );
        
        if ($email_sent) {
            set_flash_message('success', 'Payment status updated and confirmation email sent!');
        } else {
            set_flash_message('warning', 'Payment status updated but email failed to send.');
        }
    } else {
        set_flash_message('success', 'Payment status updated successfully!');
    }
    
    header("Location: admin-bookings.php");
    exit;
}

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();
    
    set_flash_message('success', 'Booking deleted successfully!');
    header("Location: admin-bookings.php");
    exit;
}

// Fetch all bookings with user and destination details
$query = "
    SELECT 
        b.*,
        u.username,
        u.email,
        d.name as destination_name
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.user_id
    LEFT JOIN destinations d ON b.destination_id = d.destination_id
    ORDER BY b.booking_id DESC
";

$result = $conn->query($query);
$bookings = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Calculate statistics
$total_bookings = count($bookings);
$pending = 0;
$confirmed = 0;
$cancelled = 0;
$total_revenue = 0;

foreach ($bookings as $booking) {
    if ($booking['status'] === 'pending') $pending++;
    if ($booking['status'] === 'confirmed') $confirmed++;
    if ($booking['status'] === 'cancelled') $cancelled++;
    $total_revenue += $booking['total_price'];
}

include 'includes/header.php';
?>

<style>
    .page-header {
        background: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .page-header h2 {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #718096;
        margin-top: 10px;
    }

    .breadcrumb-nav a {
        color: #4299e1;
        text-decoration: none;
    }

    .print-btn {
        float: right;
        background: #718096;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .print-btn:hover {
        background: #4a5568;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }

    .stat-card:nth-child(1) .stat-number { color: #4299e1; }
    .stat-card:nth-child(2) .stat-number { color: #f6ad55; }
    .stat-card:nth-child(3) .stat-number { color: #48bb78; }
    .stat-card:nth-child(4) .stat-number { color: #fc8181; }

    .filter-tabs {
        background: white;
        padding: 15px 20px;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: flex;
        gap: 15px;
        margin-bottom: 0;
    }

    .filter-tab {
        padding: 8px 20px;
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }

    .filter-tab.active {
        background: #fbbf24;
        color: white;
    }

    .filter-tab:not(.active) {
        color: #4299e1;
    }

    .filter-tab:hover {
        background: rgba(251, 191, 36, 0.1);
    }

    .filter-tab.active:hover {
        background: #f59e0b;
    }

    .bookings-container {
        background: white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .section-header {
        background: #fbbf24;
        color: white;
        padding: 15px 20px;
        font-weight: 700;
        font-size: 16px;
    }

    .booking-card {
        border-bottom: 1px solid #e2e8f0;
        padding: 20px;
        transition: background 0.2s;
    }

    .booking-card:hover {
        background: #f7fafc;
    }

    .booking-header {
        display: grid;
        grid-template-columns: 60px 200px 180px 120px 120px 80px 150px 120px 150px 200px;
        gap: 15px;
        align-items: center;
        font-size: 14px;
    }

    .table-header {
        display: grid;
        grid-template-columns: 60px 200px 180px 120px 120px 80px 150px 120px 150px 200px;
        gap: 15px;
        padding: 15px 20px;
        background: #f7fafc;
        font-weight: 600;
        font-size: 13px;
        color: #4a5568;
        border-bottom: 2px solid #e2e8f0;
    }

    .booking-id {
        font-weight: 700;
        color: #2d3748;
    }

    .customer-info strong {
        display: block;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .customer-info small {
        color: #718096;
        font-size: 12px;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-confirmed {
        background-color: #d1fae5;
        color: #059669;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .status-completed {
        background-color: #dbeafe;
        color: #2563eb;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.2s;
    }

    .btn-view {
        background: #3b82f6;
        color: white;
    }

    .btn-edit {
        background: #fbbf24;
        color: white;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .action-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .booking-details-expanded {
        display: none;
        background: #f7fafc;
        padding: 20px;
        margin-top: 15px;
        border-radius: 8px;
        border-left: 4px solid #4299e1;
    }

    .booking-details-expanded.show {
        display: block;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-label {
        font-weight: 600;
        color: #4a5568;
    }

    .detail-value {
        color: #2d3748;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
    }

    .modal-header-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #4a5568;
    }

    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .modal-buttons button {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-save {
        background: #3b82f6;
        color: white;
    }

    .btn-save:hover {
        background: #2563eb;
    }

    .btn-cancel {
        background: #e2e8f0;
        color: #4a5568;
    }

    .btn-cancel:hover {
        background: #cbd5e0;
    }

    @media print {
        .print-btn, .action-buttons, .filter-tabs, .breadcrumb-nav {
            display: none !important;
        }
    }
</style>

<div class="container-fluid mt-4 mb-5">
    <!-- Page Header -->
    <div class="page-header">
        <button class="print-btn" onclick="window.print()">
            🖨️ Print
        </button>
        <h2>📋 Manage Bookings</h2>
        <div class="breadcrumb-nav">
            <a href="admin-dashboard.php">Admin Dashboard</a> / Bookings
        </div>
    </div>

    <?php echo display_flash_messages(); ?>

    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_bookings; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $pending; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $confirmed; ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $cancelled; ?></div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterBookings('all')">
            All (<?php echo $total_bookings; ?>)
        </button>
        <button class="filter-tab" onclick="filterBookings('pending')">
            Pending (<?php echo $pending; ?>)
        </button>
        <button class="filter-tab" onclick="filterBookings('confirmed')">
            Confirmed (<?php echo $confirmed; ?>)
        </button>
        <button class="filter-tab" onclick="filterBookings('cancelled')">
            Cancelled (<?php echo $cancelled; ?>)
        </button>
    </div>

    <!-- Bookings Container -->
    <div class="bookings-container">
        <div class="section-header">All Bookings</div>
        
        <?php if (empty($bookings)): ?>
            <div class="booking-card" style="text-align: center; padding: 40px;">
                <p style="color: #718096; margin: 0;">No bookings found. Bookings will appear here once customers make reservations.</p>
            </div>
        <?php else: ?>
            <div class="table-header">
                <div>ID</div>
                <div>Customer</div>
                <div>Destination</div>
                <div>Check-in</div>
                <div>Check-out</div>
                <div>Guests</div>
                <div>Total Price</div>
                <div>Status</div>
                <div>Booking Date</div>
                <div>Actions</div>
            </div>
            
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-booking-id="<?php echo $booking['booking_id']; ?>">
                    <div class="booking-header">
                        <div class="booking-id">#<?php echo $booking['booking_id']; ?></div>
                        <div class="customer-info">
                            <strong><?php echo htmlspecialchars($booking['username'] ?? 'N/A'); ?></strong>
                            <small><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></small>
                        </div>
                        <div><?php echo htmlspecialchars($booking['destination_name'] ?? 'N/A'); ?></div>
                        <div><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></div>
                        <div><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></div>
                        <div><?php echo $booking['total_guests']; ?></div>
                        <div style="font-weight: 700; color: #059669;">PKR <?php echo number_format($booking['total_price'], 0); ?></div>
                        <div>
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo strtoupper($booking['status']); ?>
                            </span>
                        </div>
                        <div><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                        <div class="action-buttons">
                            <button class="action-btn btn-view" onclick="toggleDetails(<?php echo $booking['booking_id']; ?>)" title="View Details">
                                👁️
                            </button>
                            <button class="action-btn btn-edit" onclick="openEditModal(<?php echo $booking['booking_id']; ?>, '<?php echo $booking['status']; ?>', '<?php echo $booking['payment_status']; ?>')" title="Edit">
                                ✏️
                            </button>
                            <button class="action-btn btn-delete" onclick="deleteBooking(<?php echo $booking['booking_id']; ?>)" title="Delete">
                                🗑️
                            </button>
                        </div>
                    </div>
                    
                    <!-- Expanded Details -->
                    <div class="booking-details-expanded" id="details-<?php echo $booking['booking_id']; ?>">
                        <h4 style="margin-bottom: 15px; color: #2d3748;">Booking Information</h4>
                        <div class="detail-row">
                            <div class="detail-label">Name:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['username'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Destination:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['destination_name'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Check-in Date:</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Check-out Date:</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Total Guests:</div>
                            <div class="detail-value"><?php echo $booking['total_guests']; ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Total Price:</div>
                            <div class="detail-value" style="font-weight: 700; color: #059669;">PKR <?php echo number_format($booking['total_price'], 0); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Payment Status:</div>
                            <div class="detail-value">
                                <span class="status-badge status-<?php echo $booking['payment_status']; ?>">
                                    <?php echo strtoupper($booking['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Booking Status:</div>
                            <div class="detail-value">
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo strtoupper($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Booking Date:</div>
                            <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3 class="modal-header-title">Update Booking Status</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="booking_id" id="edit_booking_id">
            
            <div class="form-group">
                <label>Booking Status:</label>
                <select name="status" id="edit_status">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Payment Status:</label>
                <select name="payment_status" id="edit_payment_status">
                    <option value="pending">Payment Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            
            <div class="modal-buttons">
                <button type="submit" name="update_status" class="btn-save" onclick="return confirmUpdate()">Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form method="POST" id="deleteForm" style="display: none;">
    <input type="hidden" name="booking_id" id="delete_booking_id">
    <input type="hidden" name="delete_booking" value="1">
</form>

<script>
    function filterBookings(status) {
        const cards = document.querySelectorAll('.booking-card');
        const tabs = document.querySelectorAll('.filter-tab');
        const header = document.querySelector('.section-header');
        
        // Update active tab
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        
        // Update header
        header.textContent = status === 'all' ? 'All Bookings' : 
                            status.charAt(0).toUpperCase() + status.slice(1) + ' Bookings';
        
        // Filter cards
        cards.forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    function toggleDetails(bookingId) {
        const details = document.getElementById('details-' + bookingId);
        details.classList.toggle('show');
    }
    
    function openEditModal(bookingId, status, paymentStatus) {
        document.getElementById('edit_booking_id').value = bookingId;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_payment_status').value = paymentStatus;
        document.getElementById('editModal').classList.add('show');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }
    
    function confirmUpdate() {
        const paymentStatus = document.getElementById('edit_payment_status').value;
        if (paymentStatus === 'paid') {
            return confirm('Marking as PAID will send a confirmation email to the customer. Continue?');
        }
        return true;
    }
    
    function deleteBooking(bookingId) {
        if (confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
            document.getElementById('delete_booking_id').value = bookingId;
            document.getElementById('deleteForm').submit();
        }
    }
    
    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>