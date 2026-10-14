<?php
session_start();
include 'includes/config.php';

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
   FETCH FAVORITES
========================= */
$favoritesQuery = "
    SELECT 
        f.favorite_id,
        f.added_date,
        d.destination_id,
        d.name AS destination_name,
        d.description
    FROM favorites f
    INNER JOIN destinations d 
        ON f.destination_id = d.destination_id
    WHERE f.user_id = '$user_id'
    ORDER BY f.added_date DESC
";

$favoritesResult = mysqli_query($conn, $favoritesQuery);

if (!$favoritesResult) {
    die("Favorites Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">❤️ My Favorite Destinations</h2>

    <?php if (mysqli_num_rows($favoritesResult) === 0): ?>
        <div class="alert alert-info text-center">
            You have not added any destinations to favorites yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($favoritesResult)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm p-3">

                        <h4><?php echo htmlspecialchars($row['destination_name']); ?></h4>

                        <p>
                            <?php 
                                echo nl2br(
                                    htmlspecialchars(
                                        substr($row['description'], 0, 150)
                                    )
                                ); 
                            ?>...
                        </p>

                        <small class="text-muted">
                            Added on: <?php echo date("d M Y", strtotime($row['added_date'])); ?>
                        </small>

                        <div class="mt-3">
                            <a href="destination-details.php?id=<?php echo $row['destination_id']; ?>" 
                               class="btn btn-primary btn-sm">
                                View Details
                            </a>

                            <a href="remove-favorite.php?id=<?php echo $row['favorite_id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Remove this destination from favorites?')">
                                Remove
                            </a>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
