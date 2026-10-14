<?php
session_start();
require_once 'includes/config.php';

$page_title = "Upload Profile Picture";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle profile picture upload
if (isset($_POST['upload'])) {
    $targetDir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Check if file was uploaded
    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == 0) {
        $filename = $_FILES["profile_pic"]["name"];
        $fileTmpName = $_FILES["profile_pic"]["tmp_name"];
        $fileSize = $_FILES["profile_pic"]["size"];
        $fileError = $_FILES["profile_pic"]["error"];
        
        // Get file extension
        $imageFileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Generate unique filename to avoid conflicts
        $newFilename = "profile_" . $user_id . "_" . time() . "." . $imageFileType;
        $targetFile = $targetDir . $newFilename;
        
        // Allowed file types
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        
        // Validate file
        $check = getimagesize($fileTmpName);
        
        if ($check === false) {
            $error_message = "File is not an image.";
        } elseif (!in_array($imageFileType, $allowedTypes)) {
            $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($fileSize > 5000000) { // 5MB max
            $error_message = "File is too large. Maximum size is 5MB.";
        } else {
            // Try to upload file
            if (move_uploaded_file($fileTmpName, $targetFile)) {
                // Delete old profile picture if exists
                $oldImageQuery = "SELECT profile_image FROM users WHERE user_id = $user_id";
                $oldImageResult = mysqli_query($conn, $oldImageQuery);
                if ($oldImageResult) {
                    $oldImageData = mysqli_fetch_assoc($oldImageResult);
                    if (!empty($oldImageData['profile_image']) && file_exists($targetDir . $oldImageData['profile_image'])) {
                        unlink($targetDir . $oldImageData['profile_image']);
                    }
                }
                
                // Update database with new filename
                $updateQuery = "UPDATE users SET profile_image = '$newFilename' WHERE user_id = $user_id";
                
                if (mysqli_query($conn, $updateQuery)) {
                    $success_message = "Profile picture uploaded successfully!";
                    $_SESSION['profile_pic'] = $targetFile; // Optional: for backward compatibility
                } else {
                    $error_message = "Database update failed: " . mysqli_error($conn);
                    // Delete uploaded file if database update fails
                    unlink($targetFile);
                }
            } else {
                $error_message = "Failed to upload file.";
            }
        }
    } else {
        $error_message = "Please select a file to upload.";
    }
}

// Fetch current profile picture
$query = "SELECT profile_image, username FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$userData = mysqli_fetch_assoc($result);
$currentImage = $userData['profile_image'] ?? '';

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-camera"></i> Upload Profile Picture</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Success Message -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Message -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Current Profile Picture -->
                    <div class="text-center mb-4">
                        <h5 class="mb-3">Current Profile Picture</h5>
                        <?php if (!empty($currentImage) && file_exists("uploads/" . $currentImage)): ?>
                            <img src="<?php echo SITE_URL; ?>uploads/<?php echo htmlspecialchars($currentImage); ?>" 
                                 alt="Current Profile" 
                                 class="img-thumbnail rounded-circle"
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                 style="width: 200px; height: 200px; font-size: 4rem;">
                                <?php echo strtoupper(substr($userData['username'], 0, 2)); ?>
                            </div>
                            <p class="text-muted mt-2">No profile picture uploaded yet</p>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <!-- Upload Form -->
                    <h5 class="mb-3"><i class="fas fa-upload"></i> Upload New Picture</h5>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label">Choose Image</label>
                            <input type="file" 
                                   name="profile_pic" 
                                   id="profile_pic"
                                   class="form-control" 
                                   accept="image/*"
                                   required
                                   onchange="previewImage(event)">
                            <small class="text-muted">
                                Allowed: JPG, JPEG, PNG, GIF | Max size: 5MB
                            </small>
                        </div>
                        
                        <!-- Image Preview -->
                        <div class="mb-3 text-center" id="preview-container" style="display: none;">
                            <p class="mb-2"><strong>Preview:</strong></p>
                            <img id="preview-image" 
                                 src="" 
                                 alt="Preview" 
                                 class="img-thumbnail rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="upload" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i> Upload Picture
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card mt-3">
                <div class="card-body bg-light">
                    <h6 class="card-title"><i class="fas fa-lightbulb"></i> Tips for Best Results:</h6>
                    <ul class="mb-0 small">
                        <li>Use a clear, front-facing photo</li>
                        <li>Square images work best (will be cropped to circle)</li>
                        <li>Ensure good lighting</li>
                        <li>File size should be under 5MB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview image before upload
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'includes/footer.php'; ?>