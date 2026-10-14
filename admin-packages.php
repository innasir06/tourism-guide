<?php
require_once 'includes/config.php';
$page_title = "Manage Packages";

// Check if admin
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied! Admin only.');
    header("Location: index.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $package_id = (int)$_GET['delete'];
    
    // Get image path to delete file
    $result = mysqli_query($conn, "SELECT image_path FROM tour_packages WHERE package_id = $package_id");
    if ($row = mysqli_fetch_assoc($result)) {
        if (isset($row['image_path']) && !empty($row['image_path']) && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }
    
    mysqli_query($conn, "DELETE FROM tour_packages WHERE package_id = $package_id");
    set_flash_message('success', 'Package deleted successfully!');
    header("Location: admin-packages.php");
    exit;
}

// Handle toggle availability
if (isset($_GET['toggle'])) {
    $package_id = (int)$_GET['toggle'];
    mysqli_query($conn, "UPDATE tour_packages SET available = NOT available WHERE package_id = $package_id");
    set_flash_message('success', 'Package availability updated!');
    header("Location: admin-packages.php");
    exit;
}

// Get all packages
$packages = mysqli_query($conn, "
    SELECT p.*, d.name as destination_name 
    FROM tour_packages p
    LEFT JOIN destinations d ON p.destination_id = d.destination_id
    ORDER BY p.created_at DESC
");

include 'includes/header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-suitcase"></i> Manage Tour Packages</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active">Packages</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="admin-add-package.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus"></i> Add New Package
            </a>
        </div>
    </div>

    <?php echo display_flash_messages(); ?>

    <!-- Packages Table -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">All Tour Packages (<?php echo mysqli_num_rows($packages); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">ID</th>
                            <th width="100">Image</th>
                            <th>Package Name</th>
                            <th>Destination</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Max Group</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($packages) > 0): ?>
                            <?php while($package = mysqli_fetch_assoc($packages)): ?>
                            <tr>
                                <td><?php echo $package['package_id']; ?></td>
                                <td>
                                    <?php if (isset($package['image_path']) && !empty($package['image_path']) && file_exists($package['image_path'])): ?>
                                        <img src="<?php echo $package['image_path']; ?>" 
                                             alt="Package" 
                                             class="img-thumbnail" 
                                             style="width: 80px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light text-center p-2" style="width: 80px; height: 60px;">
                                            <small class="text-muted">No Image</small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($package['package_name'] ?? 'Untitled'); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo substr(htmlspecialchars($package['description'] ?? ''), 0, 80); ?>...
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($package['destination_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php echo $package['duration_days'] ?? 0; ?> Days / 
                                    <?php echo $package['duration_nights'] ?? 0; ?> Nights
                                </td>
                                <td>
                                    <strong>PKR <?php echo number_format($package['price_per_person'] ?? 0); ?></strong><br>
                                    <small class="text-muted">per person</small>
                                </td>
                                <td><?php echo $package['max_group_size'] ?? 0; ?> persons</td>
                                <td>
                                    <?php if (isset($package['available']) && $package['available']): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="package-details.php?id=<?php echo $package['package_id']; ?>" 
                                           class="btn btn-info" 
                                           title="View"
                                           target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="admin-edit-package.php?id=<?php echo $package['package_id']; ?>" 
                                           class="btn btn-primary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?toggle=<?php echo $package['package_id']; ?>" 
                                           class="btn btn-warning" 
                                           title="Toggle Availability"
                                           onclick="return confirm('Toggle availability for this package?')">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                        <a href="?delete=<?php echo $package['package_id']; ?>" 
                                           class="btn btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this package?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-suitcase fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No packages found.</p>
                                    <a href="admin-add-package.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add Your First Package
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>