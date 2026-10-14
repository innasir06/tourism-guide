<?php
require_once 'includes/config.php';
$page_title = "Add Tour Package";

// Check if admin
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied! Admin only.');
    header("Location: index.php");
    exit;
}

// Get all destinations for dropdown
$destinations = mysqli_query($conn, "SELECT destination_id, name FROM destinations ORDER BY name");

// Handle form submission
if (isset($_POST['add_package'])) {
    $destination_id = mysqli_real_escape_string($conn, $_POST['destination_id']);
    $package_name = mysqli_real_escape_string($conn, trim($_POST['package_name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $duration_days = (int)$_POST['duration_days'];
    $duration_nights = (int)$_POST['duration_nights'];
    $price_per_person = (float)$_POST['price_per_person'];
    $max_group_size = (int)$_POST['max_group_size'];
    $included_services = mysqli_real_escape_string($conn, trim($_POST['included_services']));
    $excluded_services = mysqli_real_escape_string($conn, trim($_POST['excluded_services']));
    $itinerary = mysqli_real_escape_string($conn, trim($_POST['itinerary']));
    $available = isset($_POST['available']) ? 1 : 0;
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] == 0) {
        $upload_dir = 'uploads/packages/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['package_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = 'package_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['package_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            }
        }
    }
    
    // Insert into database
    $query = "INSERT INTO tour_packages 
              (destination_id, package_name, description, duration_days, duration_nights, 
               price_per_person, max_group_size, included_services, excluded_services, 
               itinerary, image_path, available, created_at) 
              VALUES 
              ('$destination_id', '$package_name', '$description', $duration_days, $duration_nights,
               $price_per_person, $max_group_size, '$included_services', '$excluded_services',
               '$itinerary', " . ($image_path ? "'$image_path'" : "NULL") . ", $available, NOW())";
    
    if (mysqli_query($conn, $query)) {
        set_flash_message('success', 'Tour package added successfully!');
        header("Location: admin-packages.php");
        exit;
    } else {
        set_flash_message('error', 'Error adding package: ' . mysqli_error($conn));
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-plus-circle"></i> Add New Tour Package</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="admin-packages.php">Packages</a></li>
                            <li class="breadcrumb-item active">Add New</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="admin-packages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Packages
                    </a>
                </div>
            </div>

            <?php echo display_flash_messages(); ?>

            <!-- Form -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-suitcase"></i> Package Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Destination -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-map-marker-alt"></i> Destination *</label>
                                <select name="destination_id" class="form-select" required>
                                    <option value="">Select Destination</option>
                                    <?php while($dest = mysqli_fetch_assoc($destinations)): ?>
                                        <option value="<?php echo $dest['destination_id']; ?>">
                                            <?php echo htmlspecialchars($dest['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Package Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-tag"></i> Package Name *</label>
                                <input type="text" name="package_name" class="form-control" 
                                       placeholder="e.g., 5-Day Northern Pakistan Adventure" required>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><i class="fas fa-calendar-day"></i> Days *</label>
                                <input type="number" name="duration_days" class="form-control" 
                                       placeholder="5" min="1" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label"><i class="fas fa-moon"></i> Nights *</label>
                                <input type="number" name="duration_nights" class="form-control" 
                                       placeholder="4" min="0" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><i class="fas fa-money-bill-wave"></i> Price/Person (PKR) *</label>
                                <input type="number" name="price_per_person" class="form-control" 
                                       placeholder="25000" min="0" step="0.01" required>
                            </div>

                            <!-- Group Size -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><i class="fas fa-users"></i> Max Group Size *</label>
                                <input type="number" name="max_group_size" class="form-control" 
                                       placeholder="15" min="1" required>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
                                <textarea name="description" class="form-control" rows="4" 
                                          placeholder="Brief description of the tour package..." required></textarea>
                            </div>

                            <!-- Included Services -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-check-circle"></i> Included Services</label>
                                <textarea name="included_services" class="form-control" rows="5" 
                                          placeholder="e.g.,&#10;- Hotel Accommodation&#10;- Breakfast & Dinner&#10;- Transportation&#10;- Tour Guide"></textarea>
                                <small class="text-muted">One service per line</small>
                            </div>

                            <!-- Excluded Services -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-times-circle"></i> Excluded Services</label>
                                <textarea name="excluded_services" class="form-control" rows="5" 
                                          placeholder="e.g.,&#10;- Lunch&#10;- Personal Expenses&#10;- Travel Insurance"></textarea>
                                <small class="text-muted">One service per line</small>
                            </div>

                            <!-- Itinerary -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><i class="fas fa-route"></i> Itinerary</label>
                                <textarea name="itinerary" class="form-control" rows="8" 
                                          placeholder="Day 1: Arrival in Islamabad&#10;Day 2: Drive to Hunza&#10;Day 3: Hunza Sightseeing..."></textarea>
                                <small class="text-muted">Detailed day-by-day itinerary</small>
                            </div>

                            <!-- Package Image -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-image"></i> Package Image</label>
                                <input type="file" name="package_image" class="form-control" 
                                       accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">JPG, JPEG, PNG, or GIF (Max 5MB)</small>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>

                            <!-- Available Checkbox -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block"><i class="fas fa-toggle-on"></i> Availability</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="available" class="form-check-input" 
                                           id="available" checked>
                                    <label class="form-check-label" for="available">
                                        Make this package available for booking
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" name="add_package" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Add Package
                                </button>
                                <a href="admin-packages.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 300px;">';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
