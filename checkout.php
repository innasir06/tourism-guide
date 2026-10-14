<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

require_login();

$page_title = "Checkout";
$user_id = get_user_id();

// Get cart items
$cart_items = get_cart_items();

// Redirect if cart empty
if (empty($cart_items)) {
    set_flash_message('warning', 'Your cart is empty!');
    header("Location: packages.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['guests'];
}
$tax = $subtotal * 0.05;
$total = $subtotal + $tax;

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <h2 class="mb-4"><i class="fas fa-credit-card"></i> Checkout</h2>

    <?= display_flash_messages(); ?>

    <div class="row">

        <!-- Checkout Form -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- MAIN FORM -->
                    <form method="POST" action="process-checkout.php">

                        <!-- Travel Dates -->
                        <h5 class="mb-3">Travel Dates</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Check-in Date <span class="text-danger">*</span></label>
                                <input type="date" name="check_in" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Check-out Date <span class="text-danger">*</span></label>
                                <input type="date" name="check_out" class="form-control" required>
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Method -->
                        <h5 class="mb-3">Payment Method</h5>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="jazzcash" required>
                            <label class="form-check-label">JazzCash</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="easypaisa">
                            <label class="form-check-label">EasyPaisa</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="bank">
                            <label class="form-check-label">Bank Transfer</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="cod">
                            <label class="form-check-label">Cash on Arrival</label>
                        </div>

                        <hr>

                        <!-- Special Requests -->
                        <h5 class="mb-2">Special Requests</h5>
                        <textarea class="form-control mb-3" name="special_requests"
                                  rows="3"
                                  placeholder="Any special requirements or notes..."></textarea>

                        <!-- Terms -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="terms-and-conditions.php" target="_blank">terms and conditions</a>
                            </label>
                        </div>

                        <!-- Hidden total -->
                        <input type="hidden" name="total_amount" value="<?= $total ?>">

                        <!-- Submit -->
                        <button type="submit"
                                name="complete_checkout"
                                class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check-circle"></i> Confirm & Pay
                        </button>

                        <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                            ← Back to Cart
                        </a>

                    </form>
                    <!-- END FORM -->

                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-5">
            <div class="card shadow-sm sticky-top" style="top: 80px;">
                <div class="card-body">
                    <h5 class="mb-3">Order Summary</h5>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($item['package_name']) ?></strong><br>
                                <small><?= $item['guests'] ?> guest(s)</small>
                            </div>
                            <strong>PKR <?= number_format($item['price'] * $item['guests']) ?></strong>
                        </div>
                        <hr>
                    <?php endforeach; ?>

                    <p>Subtotal: <strong>PKR <?= number_format($subtotal) ?></strong></p>
                    <p>Tax (5%): <strong>PKR <?= number_format($tax) ?></strong></p>
                    <h4 class="text-success">Total: PKR <?= number_format($total) ?></h4>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            You will receive booking confirmation via email within 24 hours.
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
