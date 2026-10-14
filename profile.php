<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';
require_once 'includes/file_upload.php';

require_login(); // Require user to be logged in

$page_title = "My Profile";
$user_id = get_user_id();

// Fetch user data
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle profile picture upload
if (isset($_POST['upload_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = upload_profile_picture($_FILES['profile_picture'], $user_id);
        
        if ($upload_result['success']) {
            set_flash_message('success', 'Profile picture updated successfully!');
            // Refresh page to show new picture
            header("Location: profile.php");
            exit;
        } else {
            foreach ($upload_result['errors'] as $error) {
                set_flash_message('error', $error);
            }
        }
    } else {
        set_flash_message('error', 'Please select a file to upload.');
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $update_query = "UPDATE users SET 
                     full_name = '$full_name',
                     phone = '$phone',
                     address = '$address'
                     WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        set_flash_message('success', 'Profile updated successfully!');
        $_SESSION['full_name'] = $full_name; // Update session
        header("Location: profile.php");
        exit;
    } else {
        set_flash_message('error', 'Failed to update profile.');
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
                
                if (mysqli_query($conn, $update_query)) {
                    set_flash_message('success', 'Password changed successfully!');
                    header("Location: profile.php");
                    exit;
                } else {
                    set_flash_message('error', 'Failed to change password.');
                }
            } else {
                set_flash_message('error', 'Password must be at least 6 characters long.');
            }
        } else {
            set_flash_message('error', 'New passwords do not match.');
        }
    } else {
        set_flash_message('error', 'Current password is incorrect.');
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <?php echo display_flash_messages(); ?>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Profile Picture -->
                    <div class="mb-3">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo SITE_URL; ?>uploads/profiles/<?php echo $user['profile_picture']; ?>" 
                                 class="img-fluid rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 alt="Profile Picture">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; font-size: 3rem;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-<?php echo $user['user_type'] == 'admin' ? 'danger' : 'primary'; ?>">
                        <?php echo ucfirst($user['user_type']); ?>
                    </span>
                    
                    <hr>
                    
                    <!-- Upload Picture Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <input type="file" 
                                   name="profile_picture" 
                                   id="profile_picture"
                                   class="form-control form-control-sm" 
                                   accept="image/*">
                            <small class="text-muted">Max 5MB (JPG, PNG, GIF)</small>
                        </div>
                        <button type="submit" name="upload_picture" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-upload"></i> Upload Picture
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Activity</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php
                    // Count bookings
                    $bookings_count = mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id'"))['count'];
                    
                    // Count reviews
                    $reviews_count = mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT COUNT(*) as count FROM reviews WHERE user_id = '$user_id'"))['count'];
                    
                    // Count favorites
                    $favorites_count = mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT COUNT(*) as count FROM favorites WHERE user_id = '$user_id'"))['count'];
                    ?>
                    <a href="my-bookings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-ticket-alt text-primary"></i> Bookings
                        <span class="badge bg-primary float-end"><?php echo $bookings_count; ?></span>
                    </a>
                    <a href="my-reviews.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-star text-warning"></i> Reviews
                        <span class="badge bg-warning float-end"><?php echo $reviews_count; ?></span>
                    </a>
                    <a href="favorites.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart text-danger"></i> Favorites
                        <span class="badge bg-danger float-end"><?php echo $favorites_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" 
                                       name="full_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" 
                                       name="phone" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>"
                                       disabled>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" 
                                          class="form-control" 
                                          rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" 
                                       name="current_password" 
                                       class="form-control" 
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" 
                                       name="new_password" 
                                       class="form-control" 
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Account Info -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Member Since:</strong><br>
                               <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Login:</strong><br>
                               <?php echo $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'N/A'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Account Type:</strong><br>
                               <span class="badge bg-<?php echo $user['user_type'] == 'admin' ? 'danger' : 'primary'; ?>">
                                   <?php echo ucfirst($user['user_type']); ?>
                               </span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Session:</strong><br>
                               Active since <?php echo isset($_SESSION['login_time']) ? date('g:i A', $_SESSION['login_time']) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add preview functionality here
            console.log('File selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
