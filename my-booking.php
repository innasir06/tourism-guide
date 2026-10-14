<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

require_login();

$page_title = "My Bookings";
$user_id = get_user_id();

include 'includes/header.php';

/*
 IMPORTANT:
 Adjust column names below ONLY if your table uses different names
*/

$sql = "
    SELECT 
        b.booking_id,
        b.check_in_date,
        b.check_out_date,
        b.total_guests,
        b.total_price,
        b.status,
        b.payment_status,
        d.name AS destination_name
    FROM bookings b
    LEFT JOIN destinations d 
        ON b.destination_id = d.destination_id
    WHERE b.user_id = '$user_id'
    ORDER BY b.booking_id DESC
";

$result = mysqli_query($conn, $sql);

// 🔴 HARD FAIL CHECK (VERY IMPORTANT)
if (!$result) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger">';
    echo '<strong>Database Error:</strong><br>';
    echo mysqli_error($conn);
    echo '</div>';
    echo '</div>';
    include 'includes/footer.php';
    exit;
}
?>

<div class="container mt-4 mb-5">
    <h2 class="mb-4">My Bookings</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Destination</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['booking_id']; ?></td>
                            <td><?= htmlspecialchars($row['destination_name'] ?? 'N/A'); ?></td>
                            <td>N/A</td>
                            <td><?= $row['check_in_date']; ?></td>
                            <td><?= $row['check_out_date']; ?></td>
                            <td><?= $row['total_guests']; ?></td>
                            <td>PKR <?= number_format($row['total_price'], 2); ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?= ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['payment_status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have no bookings yet.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
