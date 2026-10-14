<?php
require_once 'includes/config.php';
$page_title = "Add Destination";

// Check if admin
require_login();
if (!is_admin()) {
    set_flash_message('error', 'Access denied! Admin only.');
    header("Location: index.php");
    exit;
}

// Handle form submission
if (isset($_POST['add_destination'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $best_time_to_visit = mysqli_real_escape_string($conn, trim($_POST['best_time_to_visit']));
    $attractions = mysqli_real_escape_string($conn, trim($_POST['attractions']));
    $popular = isset($_POST['popular']) ? 1 : 0;
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['destination_image']) && $_FILES['destination_image']['error'] == 0) {
        $upload_dir = 'uploads/destinations/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['destination_image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = 'dest_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['destination_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            }
        }
    }
    
    // Insert into database
    $query = "INSERT INTO destinations 
              (name, description, location, best_time_to_visit, attractions, image_path, popular, created_at) 
              VALUES 
              ('$name', '$description', '$location', '$best_time_to_visit', '$attractions', 
               " . ($image_path ? "'$image_path'" : "NULL") . ", $popular, NOW())";
    
    if (mysqli_query($conn, $query)) {
        set_flash_message('success', 'Destination added successfully!');
        header("Location: admin-destinations.php");
        exit;
    } else {
        set_flash_message('error', 'Error adding destination: ' . mysqli_error($conn));
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
                    <h2><i class="fas fa-plus-circle"></i> Add New Destination</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="admin-destinations.php">Destinations</a></li>
                            <li class="breadcrumb-item active">Add New</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="admin-dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <?php echo display_flash_messages(); ?>

            <!-- Form -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt"></i> Destination Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Destination Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-tag"></i> Destination Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       placeholder="e.g., Hunza Valley" required>
                            </div>

                            <!-- Location -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-map-marker-alt"></i> Location *</label>
                                <input type="text" name="location" class="form-control" 
                                       placeholder="e.g., Gilgit-Baltistan, Pakistan" required>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
                                <textarea name="description" class="form-control" rows="5" 
                                          placeholder="Detailed description of the destination..." required></textarea>
                            </div>

                            <!-- Best Time to Visit -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-calendar-alt"></i> Best Time to Visit</label>
                                <input type="text" name="best_time_to_visit" class="form-control" 
                                       placeholder="e.g., April to October">
                            </div>

                            <!-- Destination Image -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="fas fa-image"></i> Destination Image</label>
                                <input type="file" name="destination_image" class="form-control" 
                                       accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">JPG, JPEG, PNG, or GIF (Max 5MB)</small>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>

                            <!-- Attractions -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label"><i class="fas fa-star"></i> Major Attractions</label>
                                <textarea name="attractions" class="form-control" rows="4" 
                                          placeholder="e.g.,&#10;- Baltit Fort&#10;- Attabad Lake&#10;- Passu Glacier"></textarea>
                                <small class="text-muted">One attraction per line</small>
                            </div>

                            <!-- Popular Checkbox -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="popular" class="form-check-input" 
                                           id="popular">
                                    <label class="form-check-label" for="popular">
                                        <i class="fas fa-fire"></i> Mark as Popular Destination
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" name="add_destination" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Add Destination
                                </button>
                                <a href="admin-destinations.php" class="btn btn-secondary btn-lg">
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
