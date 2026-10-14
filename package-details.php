<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

$page_title = "Package Details";

// Validate package ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: packages.php");
    exit;
}
$package_id = (int) $_GET['id'];

// Fetch package details
$query = "
    SELECT p.*, d.name AS destination_name, d.destination_id, pr.name AS province_name
    FROM tour_packages p
    LEFT JOIN destinations d ON p.destination_id = d.destination_id
    LEFT JOIN provinces pr ON p.province_id = pr.province_id
    WHERE p.package_id = $package_id
";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) === 0){
    header("Location: packages.php");
    exit;
}

$package = mysqli_fetch_assoc($result);
include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <?php echo display_flash_messages(); ?>
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="packages.php">Packages</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($package['package_name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Package Image -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <img src="<?php echo SITE_URL; ?>images/<?php echo htmlspecialchars($package['image_url']); ?>" 
                     class="card-img-top rounded" 
                     alt="<?php echo htmlspecialchars($package['package_name']); ?>" 
                     style="max-height: 500px; object-fit: cover;"
                     onerror="this.src='https://via.placeholder.com/600x400?text=<?php echo urlencode($package['package_name']); ?>'">
            </div>
        </div>

        <!-- Package Details -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="card-title mb-3">
                        <?php echo htmlspecialchars($package['package_name']); ?>
                    </h2>
                    
                    <!-- Badges -->
                    <div class="mb-3">
                        <?php if($package['featured']): ?>
                            <span class="badge bg-warning text-dark me-1">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        <?php endif; ?>
                        
                        <span class="badge bg-info me-1">
                            <i class="fas fa-clock"></i> <?php echo $package['duration_days']; ?> Days
                        </span>
                        
                        <span class="badge bg-<?php 
                            echo $package['difficulty_level'] == 'easy' ? 'success' : 
                                ($package['difficulty_level'] == 'moderate' ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo ucfirst($package['difficulty_level']); ?>
                        </span>
                    </div>
                    
                    <!-- Location Info -->
                    <div class="mb-3">
                        <p class="mb-1">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <strong>Destination:</strong> 
                            <?php echo htmlspecialchars($package['destination_name'] ?? 'Multiple Locations'); ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-mountain text-primary"></i>
                            <strong>Province:</strong> 
                            <?php echo htmlspecialchars($package['province_name'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-users text-secondary"></i>
                            <strong>Max Group Size:</strong> 
                            <?php echo $package['max_group_size']; ?> people
                        </p>
                    </div>
                    
                    <hr>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <h4 class="text-success mb-1">
                            PKR <?php echo number_format($package['price_per_person']); ?>
                        </h4>
                        <small class="text-muted">per person</small>
                    </div>
                    
                    <!-- Booking Actions -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-check"></i> Book This Package
                            </h5>
                            
                            <?php if(is_logged_in()): ?>
                                <!-- Guest Selector -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-users"></i> Number of Guests
                                    </label>
                                    <input type="number" 
                                           id="guests"
                                           class="form-control" 
                                           min="1" 
                                           max="<?php echo $package['max_group_size']; ?>"
                                           value="1" 
                                           onchange="updateTotalPrice()">
                                    <small class="text-muted">Max: <?php echo $package['max_group_size']; ?> guests</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Price:</strong>
                                        <h5 class="text-success mb-0" id="totalPrice">
                                            PKR <?php echo number_format($package['price_per_person']); ?>
                                        </h5>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button type="button" 
                                            class="btn btn-success btn-lg"
                                            onclick="addToCartWithGuests()">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    
                                    <a href="booking.php?package_id=<?php echo $package['package_id']; ?>" 
                                       class="btn btn-primary btn-lg">
                                        <i class="fas fa-check-circle"></i> Book Now Directly
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-info-circle"></i> Please login to book this package
                                </div>
                                <a href="login.php?redirect=package-details.php?id=<?php echo $package_id; ?>" 
                                   class="btn btn-warning w-100 btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="d-grid gap-2">
                        <a href="packages.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Packages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Package Information (No Tabs) -->
    <div class="row mt-5">
        <!-- Description -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle"></i> About This Package</h4>
                </div>
                <div class="card-body">
                    <p class="lead"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                    
                    <?php if(!empty($package['highlights'])): ?>
                        <hr>
                        <h5 class="text-primary"><i class="fas fa-star"></i> Highlights</h5>
                        <div class="alert alert-info">
                            <?php echo nl2br(htmlspecialchars($package['highlights'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Itinerary -->
        <?php if(!empty($package['itinerary'])): ?>
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-route"></i> Day-by-Day Itinerary</h4>
                </div>
                <div class="card-body">
                    <div class="itinerary-content">
                        <?php echo nl2br(htmlspecialchars($package['itinerary'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- What's Included & Excluded -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> What's Included</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($package['includes'])): ?>
                        <?php 
                        $includes = explode("\n", $package['includes']);
                        foreach($includes as $item) {
                            $item = trim($item);
                            if(!empty($item)) {
                                // Remove bullet points or checkmarks if they exist
                                $item = preg_replace('/^[•✓✔-]\s*/', '', $item);
                                echo "<p class='mb-2'><i class='fas fa-check-circle text-success me-2'></i>" . htmlspecialchars($item) . "</p>";
                            }
                        }
                        ?>
                    <?php else: ?>
                        <p class="text-muted">Details will be provided upon inquiry.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-times-circle"></i> What's Not Included</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($package['excludes'])): ?>
                        <?php 
                        $excludes = explode("\n", $package['excludes']);
                        foreach($excludes as $item) {
                            $item = trim($item);
                            if(!empty($item)) {
                                // Remove bullet points or X marks if they exist
                                $item = preg_replace('/^[•✗✘×-]\s*/', '', $item);
                                echo "<p class='mb-2'><i class='fas fa-times-circle text-warning me-2'></i>" . htmlspecialchars($item) . "</p>";
                            }
                        }
                        ?>
                    <?php else: ?>
                        <p class="text-muted">All exclusions will be clearly communicated.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center py-4">
                    <h4>Ready to Book This Amazing Tour?</h4>
                    <p class="mb-3">Don't miss out on this incredible experience!</p>
                    <?php if(is_logged_in()): ?>
                        <a href="booking.php?package_id=<?php echo $package['package_id']; ?>" 
                           class="btn btn-light btn-lg me-2">
                            <i class="fas fa-check-circle"></i> Book Now
                        </a>
                        <button type="button" 
                                class="btn btn-outline-light btn-lg"
                                onclick="addToCartWithGuests()">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-light btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate total price based on guests
const pricePerPerson = <?php echo $package['price_per_person']; ?>;
const packageId = <?php echo $package_id; ?>;

function updateTotalPrice() {
    const guests = document.getElementById('guests').value;
    const total = pricePerPerson * guests;
    document.getElementById('totalPrice').textContent = 'PKR ' + total.toLocaleString();
}

function addToCartWithGuests() {
    const guests = document.getElementById('guests').value;
    window.location.href = 'cart.php?action=add&package_id=' + packageId + '&guests=' + guests;
}

// Initialize on page load
updateTotalPrice();
</script>

<?php include 'includes/footer.php'; ?>
