<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['package_id']) || !is_numeric($_GET['package_id'])){
    header("Location: packages.php");
    exit;
}

$package_id = (int) $_GET['package_id'];
$user_id = $_SESSION['user_id'];

// Fetch package & related destination
$packageQuery = "
    SELECT p.*, d.destination_id, d.name AS destination_name
    FROM tour_packages p
    LEFT JOIN destinations d ON p.destination_id = d.destination_id
    WHERE p.package_id = $package_id
";
$packageResult = mysqli_query($conn, $packageQuery);
if(!$packageResult || mysqli_num_rows($packageResult) === 0){
    header("Location: packages.php");
    exit;
}
$package = mysqli_fetch_assoc($packageResult);

// Fetch hotels for this destination
$hotelsQuery = "SELECT * FROM hotels WHERE destination_id={$package['destination_id']}";
$hotelsResult = mysqli_query($conn, $hotelsQuery);

// Handle booking submission
if(isset($_POST['book_now'])){
    $hotel_id = intval($_POST['hotel_id']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = intval($_POST['guests']);

    // Calculate total price
    $hotelPrice = 0;
    if($hotel_id){
        $hotelData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT price_per_night FROM hotels WHERE hotel_id=$hotel_id"));
        $hotelPrice = $hotelData['price_per_night'];
    }

    $duration = (strtotime($check_out) - strtotime($check_in)) / (60*60*24);
    $duration = $duration>0 ? $duration : 1;

    $total_price = ($package['price_per_person'] * $guests) + ($hotelPrice * $duration);

    mysqli_query($conn, "
        INSERT INTO bookings (user_id, destination_id, hotel_id, check_in_date, check_out_date, total_guests, total_price, status, payment_status) 
        VALUES ('$user_id', '{$package['destination_id']}', '$hotel_id', '$check_in', '$check_out', '$guests', '$total_price', 'pending', 'pending')
    ");

    $_SESSION['booking_success'] = "Booking successful!";
    header("Location: my-booking.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Book Package: <?php echo htmlspecialchars($package['package_name']); ?></h2>
    <?php if(isset($_SESSION['booking_success'])){ echo "<div class='alert alert-success'>".$_SESSION['booking_success']."</div>"; unset($_SESSION['booking_success']); } ?>
    <form method="post">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" class="form-control" value="<?php echo $_SESSION['full_name'] ?? ''; ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Select Hotel (Optional)</label>
            <select name="hotel_id" class="form-control">
                <option value="">No hotel</option>
                <?php while($hotel = mysqli_fetch_assoc($hotelsResult)): ?>
                    <option value="<?php echo $hotel['hotel_id']; ?>">
                        <?php echo $hotel['name']." - Rs ".$hotel['price_per_night']."/night"; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Number of Guests</label>
            <input type="number" name="guests" class="form-control" value="1" min="1" required>
        </div>
        <div class="mb-3">
            <label>Check-in Date</label>
            <input type="date" name="check_in" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Check-out Date</label>
            <input type="date" name="check_out" class="form-control" required>
        </div>
        <button type="submit" name="book_now" class="btn btn-success">Confirm Booking</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
