<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

$page_title = "Shopping Cart";

// Handle cart actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'add':
            if (isset($_GET['package_id'])) {
                $package_id = intval($_GET['package_id']);
                $guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;
                add_to_cart($package_id, 1, $guests);
                set_flash_message('success', 'Package added to cart!');
            }
            break;
            
        case 'remove':
            if (isset($_GET['package_id'])) {
                $package_id = intval($_GET['package_id']);
                remove_from_cart($package_id);
                set_flash_message('success', 'Package removed from cart!');
            }
            break;
            
        case 'update':
            if (isset($_POST['package_id'])) {
                $package_id = intval($_POST['package_id']);
                $quantity = intval($_POST['quantity']);
                $guests = intval($_POST['guests']);
                update_cart($package_id, $quantity, $guests);
                set_flash_message('success', 'Cart updated!');
            }
            break;
            
        case 'clear':
            clear_cart();
            set_flash_message('info', 'Cart cleared!');
            break;
    }
    
    // Redirect to cart page (remove action from URL)
    header("Location: cart.php");
    exit;
}

// Get cart items with package details
$cart_items = get_cart_items();
$total_price = 0;
$cart_details = array();

if (!empty($cart_items)) {
    foreach ($cart_items as $item) {
        $package_id = $item['package_id'];
        $query = "SELECT tp.*, d.name as destination_name 
                  FROM tour_packages tp
                  LEFT JOIN destinations d ON tp.destination_id = d.destination_id
                  WHERE tp.package_id = '$package_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $package = mysqli_fetch_assoc($result);
            $package['cart_quantity'] = $item['quantity'];
            $package['cart_guests'] = $item['guests'];
            $package['subtotal'] = $package['price_per_person'] * $item['guests'] * $item['quantity'];
            $total_price += $package['subtotal'];
            $cart_details[] = $package;
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-cart"></i> Shopping Cart
        <span class="badge bg-primary"><?php echo get_cart_count(); ?> items</span>
    </h2>
    
    <?php echo display_flash_messages(); ?>
    
    <?php if (empty($cart_details)): ?>
        <!-- Empty Cart -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Start exploring our tour packages!</p>
                <a href="packages.php" class="btn btn-primary">
                    <i class="fas fa-suitcase"></i> Browse Packages
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Cart Items -->
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cart_details as $package): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="<?php echo SITE_URL; ?>images/<?php echo $package['image_url']; ?>" 
                                     class="img-fluid h-100 w-100" 
                                     style="object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($package['package_name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/200x150?text=Package'">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><?php echo htmlspecialchars($package['package_name']); ?></h5>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="removeFromCart(<?php echo $package['package_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($package['destination_name'] ?? 'Multiple Locations'); ?>
                                    </p>
                                    
                                    <p class="text-muted small mb-3">
                                        <span class="badge bg-info">
                                            <i class="fas fa-clock"></i> <?php echo $package['duration_days']; ?> Days
                                        </span>
                                        <span class="badge bg-<?php 
                                            echo $package['difficulty_level'] == 'easy' ? 'success' : 
                                                ($package['difficulty_level'] == 'moderate' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($package['difficulty_level']); ?>
                                        </span>
                                    </p>
                                    
                                    <form method="POST" action="?action=update" class="row g-2 align-items-end">
                                        <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small">Quantity</label>
                                            <input type="number" 
                                                   name="quantity" 
                                                   class="form-control form-control-sm" 
                                                   min="1" 
                                                   value="<?php echo $package['cart_quantity']; ?>"
                                                   onchange="this.form.submit()">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small">Guests</label>
                                            <input type="number" 
                                                   name="guests" 
                                                   class="form-control form-control-sm" 
                                                   min="1" 
                                                   value="<?php echo $package['cart_guests']; ?>"
                                                   onchange="this.form.submit()">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small">Price/Person</label>
                                            <div class="text-primary fw-bold">
                                                PKR <?php echo number_format($package['price_per_person']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label small">Subtotal</label>
                                            <div class="text-success fw-bold">
                                                PKR <?php echo number_format($package['subtotal']); ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Clear Cart Button -->
                <div class="text-end">
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                    </button>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <strong><?php echo get_cart_count(); ?></strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Guests:</span>
                            <strong>
                                <?php 
                                $total_guests = 0;
                                foreach ($cart_details as $pkg) {
                                    $total_guests += $pkg['cart_guests'] * $pkg['cart_quantity'];
                                }
                                echo $total_guests;
                                ?>
                            </strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <strong class="text-primary">
                                PKR <?php echo number_format($total_price); ?>
                            </strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span><small>Tax (5%):</small></span>
                            <span class="text-muted">
                                <small>PKR <?php echo number_format($total_price * 0.05); ?></small>
                            </span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total:</h5>
                            <h4 class="text-success">
                                PKR <?php echo number_format($total_price * 1.05); ?>
                            </h4>
                        </div>
                        
                        <?php if (is_logged_in()): ?>
                            <a href="checkout.php" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning small mb-2">
                                <i class="fas fa-info-circle"></i> Please login to checkout
                            </div>
                            <a href="login.php" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                        
                        <a href="packages.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus"></i> Add More Packages
                        </a>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Secure Booking
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Code (Optional) -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Have a promo code?</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-primary">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromCart(packageId) {
    if (confirm('Are you sure you want to remove this package from cart?')) {
        window.location.href = 'cart.php?action=remove&package_id=' + packageId;
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        window.location.href = 'cart.php?action=clear';
    }
}

// Auto-save cart on quantity/guests change
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
