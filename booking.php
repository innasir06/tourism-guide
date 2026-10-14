<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

// Require login
require_login();

$page_title = "Book Package";

// Validate package ID
if (!isset($_GET['package_id']) || !is_numeric($_GET['package_id'])) {
    header("Location: packages.php");
    exit;
}

$user_id = get_user_id();
$package_id = (int) $_GET['package_id'];

/* =========================
   FETCH PACKAGE INFO
========================= */
$packageQuery = "
    SELECT p.*, d.destination_id, d.name AS destination_name 
    FROM tour_packages p
    LEFT JOIN destinations d ON p.destination_id = d.destination_id
    WHERE p.package_id = $package_id
";
$packageResult = mysqli_query($conn, $packageQuery);

if (!$packageResult || mysqli_num_rows($packageResult) === 0) {
    set_flash_message('error', 'Package not found!');
    header("Location: packages.php");
    exit;
}

$package = mysqli_fetch_assoc($packageResult);

/* =========================
   FETCH HOTELS
========================= */
$hotels = [];
if ($package['destination_id']) {
    $hotelsQuery = "
        SELECT * FROM hotels 
        WHERE destination_id = {$package['destination_id']} 
        AND available_rooms > 0
        ORDER BY star_rating DESC
    ";
    $hotelsResult = mysqli_query($conn, $hotelsQuery);
    if ($hotelsResult) {
        while ($hotel = mysqli_fetch_assoc($hotelsResult)) {
            $hotels[] = $hotel;
        }
    }
}

/* =========================
   HANDLE BOOKING
========================= */
if (isset($_POST['book_now'])) {

    $hotel_id = !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : NULL;
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
    $check_in = mysqli_real_escape_string($conn, $_POST['check_in'] ?? '');
    $check_out = mysqli_real_escape_string($conn, $_POST['check_out'] ?? '');
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_requests'] ?? '');

    $errors = [];

    // Validate dates
    if (empty($check_in)) $errors[] = "Check-in date is required.";
    if (empty($check_out)) $errors[] = "Check-out date is required.";

    $check_in_time = strtotime($check_in);
    $check_out_time = strtotime($check_out);
    $today = strtotime('today');

    if (!$check_in_time || !$check_out_time) {
        $errors[] = "Invalid date format.";
    } elseif ($check_in_time < $today) {
        $errors[] = "Check-in date cannot be in the past.";
    } elseif ($check_in_time >= $check_out_time) {
        $errors[] = "Check-out date must be after check-in date.";
    }

    // Validate guests
    if ($guests < 1 || $guests > $package['max_group_size']) {
        $errors[] = "Number of guests must be between 1 and {$package['max_group_size']}.";
    }

    // Insert booking
    if (empty($errors)) {

        $total_price = $package['price_per_person'] * $guests;

        $insertQuery = "
            INSERT INTO bookings 
            (user_id, destination_id, hotel_id, check_in_date, check_out_date, total_guests, total_price, status, payment_status, special_requests, booking_date)
            VALUES
            (
                '$user_id',
                '{$package['destination_id']}',
                " . ($hotel_id ? "'$hotel_id'" : "NULL") . ",
                '$check_in',
                '$check_out',
                '$guests',
                '$total_price',
                'pending',
                'pending',
                '$special_requests',
                NOW()
            )
        ";

        if (mysqli_query($conn, $insertQuery)) {
            set_flash_message('success', 'Booking submitted successfully! Our team will contact you soon.');
            header("Location: my-booking.php");
            exit;
        } else {
            $errors[] = "Booking failed: " . mysqli_error($conn);
        }
    }

    // Show errors
    foreach ($errors as $error) {
        set_flash_message('error', $error);
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <?php echo display_flash_messages(); ?>

    <div class="row">

        <!-- PACKAGE SUMMARY -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top:80px;">
                <img src="<?php echo SITE_URL; ?>images/<?php echo htmlspecialchars($package['image_url']); ?>"
                     class="card-img-top"
                     alt="<?php echo htmlspecialchars($package['package_name']); ?>">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($package['package_name']); ?></h5>
                    <p class="small text-muted">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($package['destination_name']); ?><br>
                        <i class="fas fa-clock"></i> <?php echo $package['duration_days']; ?> Days<br>
                        <i class="fas fa-users"></i> Max <?php echo $package['max_group_size']; ?> guests
                    </p>
                    <hr>
                    <h5 class="text-success">PKR <?php echo number_format($package['price_per_person']); ?> / person</h5>
                </div>
            </div>
        </div>

        <!-- BOOKING FORM -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-clipboard-list"></i> Complete Booking</h4>
                </div>
                <div class="card-body">
                    <form method="POST">

                        <div class="mb-3">
                            <label>Guests</label>
                            <input type="number" class="form-control" name="guests"
                                   min="1" max="<?php echo $package['max_group_size']; ?>"
                                   value="<?php echo $_POST['guests'] ?? 1; ?>"
                                   onchange="updateTotalPrice()">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Check-in</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Check-out</label>
                                <input type="date" class="form-control" id="check_out" name="check_out" required>
                            </div>
                        </div>

                        <?php if (!empty($hotels)): ?>
                        <div class="mb-3">
                            <label>Hotel (Optional)</label>
                            <select class="form-select" name="hotel_id">
                                <option value="">No Hotel</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['hotel_id']; ?>">
                                        <?php echo htmlspecialchars($hotel['name']); ?> (<?php echo $hotel['star_rating']; ?>★)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label>Special Requests</label>
                            <textarea class="form-control" name="special_requests"></textarea>
                        </div>

                        <hr>

                        <h5>Total: <span id="totalAmount" class="text-success"></span></h5>

                        <button type="submit" name="book_now" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-check-circle"></i> Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const pricePerPerson = <?php echo $package['price_per_person']; ?>;

function updateTotalPrice() {
    const guests = document.querySelector('[name="guests"]').value;
    document.getElementById('totalAmount').textContent =
        'PKR ' + (pricePerPerson * guests).toLocaleString();
}
updateTotalPrice();
</script>

<?php include 'includes/footer.php'; ?>
