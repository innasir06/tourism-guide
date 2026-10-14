<?php
$page_title = "Destinations";
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Explore Destinations</h2>
    <div class="row">
        <?php
        // Fetch destinations with average rating
        $query = "
            SELECT d.*, IFNULL(AVG(r.rating), 0) AS avg_rating, COUNT(r.review_id) AS total_reviews
            FROM destinations d
            LEFT JOIN reviews r ON r.destination_id = d.destination_id
            GROUP BY d.destination_id
            ORDER BY d.created_at DESC
        ";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($destination = mysqli_fetch_assoc($result)) {
        ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="images/<?php echo $destination['image_url']; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>" style="height:200px; object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($destination['name']); ?></h5>
                        <p class="card-text">
                            Category: <?php echo htmlspecialchars($destination['category']); ?><br>
                            Rating: 
                            <?php
                            $fullStars = floor($destination['avg_rating']);
                            $halfStar = ($destination['avg_rating'] - $fullStars) >= 0.5 ? true : false;
                            for ($i = 0; $i < $fullStars; $i++) echo '⭐';
                            if ($halfStar) echo '✰';
                            ?>
                            (<?php echo $destination['total_reviews']; ?> reviews)<br>
                            Price Range: <?php echo $destination['price_range']; ?>
                        </p>
                        <a href="destination-details.php?id=<?php echo $destination['destination_id']; ?>" class="btn btn-primary mt-auto mb-2">Explore</a>
                        <a href="favorites.php?add=<?php echo $destination['destination_id']; ?>" class="btn btn-warning mt-auto">Add to Favorites</a>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo "<p>No destinations found.</p>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
