<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

$page_title = "Tour Packages";
include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="section-title text-center mb-5">
        <h2><i class="fas fa-suitcase"></i> Our Tour Packages</h2>
        <p class="lead">Explore our carefully curated tour packages for an unforgettable Pakistan experience</p>
    </div>
    
    <!-- Filter Options -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <select name="province" class="form-select">
                                <option value="">All Provinces</option>
                                <?php
                                $provinces_query = "SELECT * FROM provinces ORDER BY name";
                                $provinces_result = mysqli_query($conn, $provinces_query);
                                while($prov = mysqli_fetch_assoc($provinces_result)) {
                                    $selected = (isset($_GET['province']) && $_GET['province'] == $prov['province_id']) ? 'selected' : '';
                                    echo "<option value='{$prov['province_id']}' $selected>{$prov['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Difficulty</label>
                            <select name="difficulty" class="form-select">
                                <option value="">All Levels</option>
                                <option value="easy" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                                <option value="moderate" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'moderate') ? 'selected' : ''; ?>>Moderate</option>
                                <option value="challenging" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'challenging') ? 'selected' : ''; ?>>Challenging</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Max Price (PKR)</label>
                            <input type="number" name="max_price" class="form-control" placeholder="e.g., 100000" value="<?php echo $_GET['max_price'] ?? ''; ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <?php
        // Build query with filters
        $where_conditions = ["p.available = 1"];
        
        if (isset($_GET['province']) && !empty($_GET['province'])) {
            $province_id = intval($_GET['province']);
            $where_conditions[] = "p.province_id = $province_id";
        }
        
        if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
            $difficulty = mysqli_real_escape_string($conn, $_GET['difficulty']);
            $where_conditions[] = "p.difficulty_level = '$difficulty'";
        }
        
        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            $max_price = intval($_GET['max_price']);
            $where_conditions[] = "p.price_per_person <= $max_price";
        }
        
        $where_clause = implode(" AND ", $where_conditions);
        
        // Fetch tour packages
        $query = "
            SELECT p.*, d.name AS destination_name, pr.name AS province_name
            FROM tour_packages p
            LEFT JOIN destinations d ON p.destination_id = d.destination_id
            LEFT JOIN provinces pr ON p.province_id = pr.province_id
            WHERE $where_clause
            ORDER BY p.featured DESC, p.created_at DESC
        ";
        $result = mysqli_query($conn, $query);

        if($result && mysqli_num_rows($result) > 0){
            while($package = mysqli_fetch_assoc($result)){
        ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="<?php echo SITE_URL; ?>images/<?php echo $package['image_url']; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($package['package_name']); ?>" 
                             style="height:220px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/400x220?text=<?php echo urlencode($package['package_name']); ?>'">
                        
                        <?php if($package['featured']): ?>
                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        <?php endif; ?>
                        
                        <span class="badge bg-<?php 
                            echo $package['difficulty_level'] == 'easy' ? 'success' : 
                                ($package['difficulty_level'] == 'moderate' ? 'warning' : 'danger'); 
                        ?> position-absolute top-0 end-0 m-2">
                            <?php echo ucfirst($package['difficulty_level']); ?>
                        </span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($package['package_name']); ?></h5>
                        
                        <p class="text-muted small mb-2">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <?php echo htmlspecialchars($package['destination_name'] ?? $package['province_name']); ?>
                        </p>
                        
                        <div class="mb-3">
                            <span class="badge bg-info me-1">
                                <i class="fas fa-clock"></i> <?php echo $package['duration_days']; ?> Days
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-users"></i> Max <?php echo $package['max_group_size']; ?>
                            </span>
                        </div>
                        
                        <p class="card-text small flex-grow-1">
                            <?php echo substr(htmlspecialchars($package['description']), 0, 100) . '...'; ?>
                        </p>
                        
                        <div class="mt-auto">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted">Starting from</small>
                                    <h4 class="text-success mb-0">PKR <?php echo number_format($package['price_per_person']); ?></h4>
                                    <small class="text-muted">per person</small>
                                </div>
                            </div>
                            
                            <a href="package-details.php?id=<?php echo $package['package_id']; ?>" 
                               class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-info-circle"></i> View Details
                            </a>
                            
                            <!-- Add to Cart -->
                            <button type="button" 
                                    class="btn btn-outline-success w-100"
                                    onclick="quickAddToCart(<?php echo $package['package_id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo '<div class="col-12">';
            echo '<div class="alert alert-info text-center">';
            echo '<i class="fas fa-info-circle"></i> No tour packages found matching your criteria.';
            echo '<br><a href="packages.php" class="btn btn-primary mt-2">Clear Filters</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script>
function quickAddToCart(packageId) {
    window.location.href = 'cart.php?action=add&package_id=' + packageId + '&guests=1';
}
</script>

<?php include 'includes/footer.php'; ?>