<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';
require_once 'includes/booking-mailer.php'; // Include your PHPMailer function
require_once 'includes/booking-mailer.php';
require_login();

$user_id = get_user_id();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

// Collect inputs
$check_in        = $_POST['check_in'] ?? '';
$check_out       = $_POST['check_out'] ?? '';
$payment_method  = $_POST['payment_method'] ?? '';
$special_requests = $_POST['special_requests'] ?? '';
$total_amount    = $_POST['total_amount'] ?? 0;

// Basic validation
if (!$check_in || !$check_out || !$payment_method) {
    set_flash_message('error', 'All required fields must be filled.');
    header("Location: checkout.php");
    exit;
}

// Get cart items
$cart_items = get_cart_items();

if (empty($cart_items)) {
    set_flash_message('error', 'Your cart is empty.');
    header("Location: packages.php");
    exit;
}

// Get user details for email
$user_query = "SELECT username, email FROM users WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

$customer_name = $user_data['username'] ?? 'Guest';
$customer_email = $user_data['email'] ?? '';

/**
 * =========================
 * SAVE BOOKINGS
 * =========================
 */
$booking_success = false;
$booking_ids = []; // Store all created booking IDs

foreach ($cart_items as $item) {
    $package_id = (int)$item['package_id'];
    $guests     = (int)$item['guests'];
    $quantity   = isset($item['quantity']) ? (int)$item['quantity'] : 1;
    
    // Get package details including destination_id
    $pkg_query = "SELECT destination_id, price_per_person 
                  FROM tour_packages 
                  WHERE package_id = $package_id";
    $pkg_result = mysqli_query($conn, $pkg_query);
    $package = mysqli_fetch_assoc($pkg_result);
    
    if (!$package) continue;
    
    $destination_id = (int)$package['destination_id'];
    $price_per_person = $package['price_per_person'];
    
    // Calculate total price for this booking
    $total_price = $price_per_person * $guests * $quantity;
    
    // Set hotel_id to NULL for now
    $hotel_id = 'NULL';
    
    // Escape special characters
    $special_requests_escaped = mysqli_real_escape_string($conn, $special_requests);
    
    // Insert booking
    $insert_query = "
        INSERT INTO bookings (
            user_id,
            destination_id,
            hotel_id,
            check_in_date,
            check_out_date,
            total_guests,
            total_price,
            status,
            payment_status,
            special_requests
        ) VALUES (
            '$user_id',
            '$destination_id',
            $hotel_id,
            '$check_in',
            '$check_out',
            '$guests',
            '$total_price',
            'pending',
            'pending',
            '$special_requests_escaped'
        )
    ";
    
    $result = mysqli_query($conn, $insert_query);
    
    if ($result) {
        $booking_success = true;
        $booking_id = mysqli_insert_id($conn); // Get the inserted booking ID
        $booking_ids[] = $booking_id;
    } else {
        error_log("Booking insertion failed: " . mysqli_error($conn));
        set_flash_message('error', 'Failed to create booking: ' . mysqli_error($conn));
        header("Location: checkout.php");
        exit;
    }
}

if (!$booking_success) {
    set_flash_message('error', 'No bookings were created. Please try again.');
    header("Location: checkout.php");
    exit;
}

/**
 * =========================
 * SEND EMAIL CONFIRMATION
 * =========================
 */
if (!empty($customer_email) && !empty($booking_ids)) {
    // Get the first booking ID for the email
    $primary_booking_id = $booking_ids[0];
    
    // Send booking confirmation email using your PHPMailer function
    $email_sent = sendBookingConfirmationEmail(
        $customer_email,           // To email
        $customer_name,            // To name
        $primary_booking_id,       // Booking ID
        $payment_method,           // Payment method
        number_format($total_amount, 0) // Amount
    );
    
    if ($email_sent) {
        error_log("Booking confirmation email sent to: $customer_email");
    } else {
        error_log("Failed to send booking confirmation email to: $customer_email");
    }
}

/**
 * =========================
 * CLEAR CART
 * =========================
 */
clear_cart();

// Set success message
set_flash_message('success', 'Your booking has been confirmed! Check your email for details.');

/**
 * =========================
 * REDIRECT BASED ON PAYMENT
 * =========================
 */
switch ($payment_method) {
    case 'jazzcash':
        header("Location: jazzcash.php");
        break;

    case 'easypaisa':
        header("Location: easypaisa.php");
        break;

    case 'bank':
        header("Location: bank-transfer.php");
        break;

    case 'cod':
        header("Location: booking-success.php");
        break;

    default:
        header("Location: my-bookings.php");
        break;
}

exit;
?>