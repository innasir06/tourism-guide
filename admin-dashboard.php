<?php
require_once 'includes/config.php';
$page_title = "Admin Dashboard";

// Check if admin
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied! Admin only.');
    header("Location: index.php");
    exit;
}

// Get statistics
$stats = [];

// Total destinations
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM destinations");
$stats['destinations'] = mysqli_fetch_assoc($result)['count'];

// Total packages
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tour_packages");
$stats['packages'] = mysqli_fetch_assoc($result)['count'];

// Total bookings
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings");
$stats['bookings'] = mysqli_fetch_assoc($result)['count'];

// Total users (customers)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE user_type='customer'");
$stats['users'] = mysqli_fetch_assoc($result)['count'];

// Pending bookings
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='pending'");
$stats['pending_bookings'] = mysqli_fetch_assoc($result)['count'];

// Confirmed bookings
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='confirmed'");
$stats['confirmed_bookings'] = mysqli_fetch_assoc($result)['count'];

// Total revenue (sum of all confirmed bookings)
$result = mysqli_query($conn, "SELECT SUM(total_price) as revenue FROM bookings WHERE status='confirmed'");
$revenue = mysqli_fetch_assoc($result)['revenue'] ?? 0;

// Recent bookings
$recentBookings = mysqli_query($conn, "
    SELECT b.*, d.name as destination_name, u.full_name as user_name 
    FROM bookings b
    LEFT JOIN destinations d ON b.destination_id = d.destination_id
    LEFT JOIN users u ON b.user_id = u.user_id
    ORDER BY b.booking_date DESC
    LIMIT 5
");

include 'includes/header.php';
?>

<style>
.stat-card {
    border-left: 4px solid;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}
.quick-action-card {
    transition: transform 0.2s;
    height: 100%;
}
.quick-action-card:hover {
    transform: translateY(-3px);
}
</style>

<div class="container-fluid mt-4 mb-5">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-user-shield"></i> Admin Dashboard</h2>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>! Here's what's happening with your tourism business.</p>
                </div>
                <div>
                    <span class="badge bg-danger" style="font-size: 14px; padding: 8px 15px;">
                        <i class="fas fa-shield-alt"></i> ADMIN MODE
                    </span>
                </div>
            </div>
        </div>
    </div>

    <?php echo display_flash_messages(); ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Packages -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card border-left-success h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-success text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Tour Packages
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['packages']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-suitcase stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Destinations -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card border-left-primary h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-primary text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Destinations
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['destinations']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marked-alt stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Bookings -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card border-left-info h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-info text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Total Bookings
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check stat-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card border-left-warning h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-warning text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Total Revenue
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                PKR <?php echo number_format($revenue); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="row mb-4">
        <!-- Pending Bookings -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card border-left-danger h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-danger text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Pending Bookings
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['pending_bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmed Bookings -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card border-left-success h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-success text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Confirmed Bookings
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['confirmed_bookings']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card border-left-secondary h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary text-uppercase mb-1" style="font-size: 0.75rem; font-weight: bold;">
                                Total Customers
                            </div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['users']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users stat-icon text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card quick-action-card text-white bg-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-plus-circle fa-3x mb-3"></i>
                                    <h5 class="card-title">Add Package</h5>
                                    <p class="card-text">Create a new tour package</p>
                                    <a href="admin-add-package.php" class="btn btn-light">
                                        <i class="fas fa-plus"></i> Add Now
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card quick-action-card text-white bg-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                    <h5 class="card-title">Add Destination</h5>
                                    <p class="card-text">Add a new tourist destination</p>
                                    <a href="admin-add-destination.php" class="btn btn-light">
                                        <i class="fas fa-plus"></i> Add Now
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card quick-action-card text-white bg-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-tasks fa-3x mb-3"></i>
                                    <h5 class="card-title">Manage Bookings</h5>
                                    <p class="card-text">View and update bookings</p>
                                    <a href="admin-bookings.php" class="btn btn-light">
                                        <i class="fas fa-calendar"></i> View All
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card quick-action-card text-white bg-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-suitcase fa-3x mb-3"></i>
                                    <h5 class="card-title">View Packages</h5>
                                    <p class="card-text">Manage all tour packages</p>
                                    <a href="admin-packages.php" class="btn btn-light">
                                        <i class="fas fa-list"></i> View All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recentBookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Destination</th>
                                        <th>Check-in</th>
                                        <th>Guests</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = mysqli_fetch_assoc($recentBookings)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['booking_id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($booking['destination_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                        <td><?php echo $booking['total_guests']; ?></td>
                                        <td><strong>PKR <?php echo number_format($booking['total_price']); ?></strong></td>
                                        <td>
                                            <?php
                                            $status = $booking['status'];
                                            $badge_class = 'secondary';
                                            if ($status == 'confirmed') $badge_class = 'success';
                                            elseif ($status == 'pending') $badge_class = 'warning';
                                            elseif ($status == 'cancelled') $badge_class = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin-bookings.php?id=<?php echo $booking['booking_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="admin-bookings.php" class="btn btn-info">
                                <i class="fas fa-list"></i> View All Bookings
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent bookings</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>