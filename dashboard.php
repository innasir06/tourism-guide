<?php
require_once 'includes/config.php';
$page_title = "Dashboard";

// Check if logged in
require_login();

// ✅ REDIRECT ADMINS TO ADMIN DASHBOARD
if (is_admin()) {
    header("Location: admin-dashboard.php");
    exit;
}

// If we reach here, user is a regular customer
$user_id = $_SESSION['user_id'];

include 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['full_name'] ?? 'User'); ?>!</h2>
    <p class="text-muted">This is your dashboard where you can see your activity summary.</p>

    <div class="row mt-4">
        <!-- Total Bookings -->
        <div class="col-md-3 mb-3">
            <?php
            $bookingQuery = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE user_id = '$user_id'";
            $bookingResult = mysqli_query($conn, $bookingQuery);
            $bookingData = mysqli_fetch_assoc($bookingResult);
            ?>
            <div class="card text-center bg-primary text-white h-100">
                <div class="card-body">
                    <h3><?php echo $bookingData['total_bookings']; ?></h3>
                    <p>Total Bookings</p>
                    <a href="my-booking.php" class="btn btn-light btn-sm">View Bookings</a>
                </div>
            </div>
        </div>

        <!-- Total Favorites -->
        <div class="col-md-3 mb-3">
            <?php
            $favQuery = "SELECT COUNT(*) AS total_favorites FROM favorites WHERE user_id = '$user_id'";
            $favResult = mysqli_query($conn, $favQuery);
            $favData = mysqli_fetch_assoc($favResult);
            ?>
            <div class="card text-center bg-success text-white h-100">
                <div class="card-body">
                    <h3><?php echo $favData['total_favorites']; ?></h3>
                    <p>Total Favorites</p>
                    <a href="favorites.php" class="btn btn-light btn-sm">View Favorites</a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    Recent Bookings
                </div>
                <div class="card-body">
                    <?php
                    $recentBookingsQuery = "
                        SELECT b.*, d.name AS destination_name
                        FROM bookings b
                        LEFT JOIN destinations d ON b.destination_id = d.destination_id
                        WHERE b.user_id = '$user_id'
                        ORDER BY b.booking_date DESC
                        LIMIT 5
                    ";
                    $recentBookingsResult = mysqli_query($conn, $recentBookingsQuery);

                    if($recentBookingsResult && mysqli_num_rows($recentBookingsResult) > 0){
                        echo "<ul class='list-group'>";
                        while($booking = mysqli_fetch_assoc($recentBookingsResult)){
                            echo "<li class='list-group-item'>";
                            echo "<strong>".htmlspecialchars($booking['destination_name'])."</strong> | ".$booking['check_in_date']." to ".$booking['check_out_date']." | Status: ".$booking['status'];
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No recent bookings found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    Your Recent Reviews
                </div>
                <div class="card-body">
                    <?php
                    $reviewQuery = "
                        SELECT r.*, d.name AS destination_name
                        FROM reviews r
                        LEFT JOIN destinations d ON r.destination_id = d.destination_id
                        WHERE r.user_id = '$user_id'
                        ORDER BY r.review_date DESC
                        LIMIT 5
                    ";
                    $reviewResult = mysqli_query($conn, $reviewQuery);

                    if($reviewResult && mysqli_num_rows($reviewResult) > 0){
                        echo "<ul class='list-group'>";
                        while($review = mysqli_fetch_assoc($reviewResult)){
                            echo "<li class='list-group-item'>";
                            echo "<strong>".htmlspecialchars($review['destination_name'])."</strong> | Rating: ".$review['rating']." ⭐<br>";
                            echo "<em>".htmlspecialchars($review['title'])."</em><br>";
                            echo substr(htmlspecialchars($review['comment']), 0, 100)."..." ;
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No reviews found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include 'includes/footer.php';
?>