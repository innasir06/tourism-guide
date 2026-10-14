<?php
session_start();
$page_title = "Destination Details";
include 'includes/config.php';

// Validate destination ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: destinations.php");
    exit;
}

$destination_id = (int) $_GET['id'];

// Fetch destination
$query = "SELECT * FROM destinations WHERE destination_id = $destination_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: destinations.php");
    exit;
}

$destination = mysqli_fetch_assoc($result);

// Handle review submission
if(isset($_POST['submit_review']) && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $insertReview = "
        INSERT INTO reviews (user_id, destination_id, rating, title, comment, status) 
        VALUES ('$user_id', '$destination_id', '$rating', '$title', '$comment', 'approved')
    ";

    mysqli_query($conn, $insertReview);
    $_SESSION['review_success'] = "Review submitted successfully!";
    header("Location: destination-details.php?id=$destination_id");
    exit;
}

// Fetch reviews
$reviewsQuery = "
    SELECT r.*, u.full_name 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.destination_id = $destination_id AND r.status='approved' 
    ORDER BY r.review_date DESC
";
$reviewsResult = mysqli_query($conn, $reviewsQuery);

// Handle favorites
if(isset($_POST['add_favorite']) && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $checkFavorite = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id='$user_id' AND destination_id='$destination_id'");
    if(mysqli_num_rows($checkFavorite) == 0){
        mysqli_query($conn, "INSERT INTO favorites (user_id, destination_id) VALUES ('$user_id', '$destination_id')");
        $_SESSION['fav_success'] = "Added to favorites!";
    }
    header("Location: destination-details.php?id=$destination_id");
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <!-- IMAGE -->
        <div class="col-md-6 mb-4">
            <img src="images/<?php echo htmlspecialchars($destination['image_url']); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($destination['name']); ?>" onerror="this.src='https://via.placeholder.com/600x400?text=Destination'">
        </div>

        <!-- DETAILS -->
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($destination['name']); ?></h2>
            <p class="text-muted">
                <strong>Category:</strong> <?php echo htmlspecialchars($destination['category']); ?><br>
                <strong>Location:</strong> <?php echo htmlspecialchars($destination['location']); ?><br>
                <strong>Country:</strong> <?php echo htmlspecialchars($destination['country']); ?><br>
                <strong>Price Range:</strong> <?php echo htmlspecialchars($destination['price_range']); ?><br>
                <strong>Rating:</strong> <?php echo number_format($destination['rating'],1); ?> / 5 ⭐
            </p>
            <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>

            <div class="mb-3">
                <a href="packages.php?destination_id=<?php echo $destination_id; ?>" class="btn btn-primary">View Packages</a>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="post" class="mb-3">
                <button type="submit" name="add_favorite" class="btn btn-warning">Add to Favorites</button>
                <?php if(isset($_SESSION['fav_success'])) { echo "<span class='text-success'>".$_SESSION['fav_success']."</span>"; unset($_SESSION['fav_success']); } ?>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews -->
    <div class="mt-5">
        <h3>Reviews</h3>
        <?php
        if(isset($_SESSION['review_success'])){
            echo "<div class='alert alert-success'>".$_SESSION['review_success']."</div>";
            unset($_SESSION['review_success']);
        }
        if($reviewsResult && mysqli_num_rows($reviewsResult) > 0){
            while($review = mysqli_fetch_assoc($reviewsResult)){
                echo "<div class='border p-3 mb-2'>";
                echo "<strong>".htmlspecialchars($review['full_name'])."</strong> - ".$review['rating']."/5<br>";
                echo "<strong>".htmlspecialchars($review['title'])."</strong><br>";
                echo "<p>".nl2br(htmlspecialchars($review['comment']))."</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No reviews yet.</p>";
        }
        ?>

        <?php if(isset($_SESSION['user_id'])): ?>
        <h4 class="mt-4">Submit Your Review</h4>
        <form method="post">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Rating</label>
                <select name="rating" class="form-control" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Terrible</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Comment</label>
                <textarea name="comment" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" name="submit_review" class="btn btn-success">Submit Review</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
