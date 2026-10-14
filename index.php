<?php
session_start();
require_once 'includes/config.php';
$page_title = 'Home';

/* =========================
   FETCH DATA
========================= */

// Featured destinations
$featured_destinations = mysqli_query(
    $conn,
    "SELECT * FROM destinations WHERE featured = 1 ORDER BY rating DESC LIMIT 6"
);

// Featured tour packages
$featured_packages = mysqli_query(
    $conn,
    "SELECT tp.*, d.name AS destination_name, p.name AS province_name
     FROM tour_packages tp
     LEFT JOIN destinations d ON tp.destination_id = d.destination_id
     LEFT JOIN provinces p ON tp.province_id = p.province_id
     WHERE tp.featured = 1 AND tp.available = 1
     ORDER BY tp.package_id DESC LIMIT 4"
);

// Travel tips
$tips = mysqli_query(
    $conn,
    "SELECT * FROM travel_tips
     WHERE featured = 1 AND published = 1
     ORDER BY published_date DESC LIMIT 3"
);

include 'includes/header.php';
?>

<!-- =========================
     REGISTRATION SUCCESS ALERT
========================= -->
<div class="container mt-4">
    <?php
    if(isset($_SESSION['register_success'])){
        echo '<div id="register-success-alert" class="alert alert-success">'.$_SESSION['register_success'].'</div>';
        unset($_SESSION['register_success']);
    }
    ?>
</div>

<script>
    // Hide the success alert after 1 second (1000 milliseconds)
    setTimeout(function() {
        var alertBox = document.getElementById('register-success-alert');
        if(alertBox) {
            alertBox.style.display = 'none';
        }
    }, 1000);
</script>

<!-- =========================
     HERO SECTION
========================= -->
<section class="hero-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center text-center">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-3 fw-bold mb-4" data-aos="fade-up">
                    Discover the Beauty of Pakistan
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Explore breathtaking mountains, rich cultural heritage, and warm hospitality. Your adventure of a lifetime awaits!
                </p>
                <div data-aos="fade-up" data-aos-delay="200">
                    <a href="destinations.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-map-marked-alt"></i> Explore Destinations
                    </a>
                    <a href="packages.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-suitcase"></i> View Packages
                    </a>
                </div>

                <!-- Quick Stats -->
                <div class="row mt-5 text-center text-white">
                    <div class="col-md-3 col-6 mb-3">
                        <h3 class="display-6 fw-bold">50+</h3>
                        <p>Destinations</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <h3 class="display-6 fw-bold">100+</h3>
                        <p>Tour Packages</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <h3 class="display-6 fw-bold">5000+</h3>
                        <p>Happy Travelers</p>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <h3 class="display-6 fw-bold">4.9★</h3>
                        <p>Average Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     FEATURED DESTINATIONS
========================= -->
<section class="py-5">
    <div class="container">
        <div class="section-title mb-4">
            <h2>Featured Destinations</h2>
        </div>
        <div class="row g-4">
            <?php while ($destination = mysqli_fetch_assoc($featured_destinations)) : ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="images/<?php echo htmlspecialchars($destination['image_url']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($destination['name']); ?>"
                             style="height:200px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/400x300?text=<?php echo urlencode($destination['name']); ?>'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($destination['name']); ?></h5>
                            <p class="card-text">
                                <?php echo substr(htmlspecialchars($destination['description']), 0, 120); ?>...
                            </p>
                            <p class="small mb-2">
                                Rating: <?php echo $destination['rating']; ?>/5<br>
                                Price Range: <?php echo htmlspecialchars($destination['price_range']); ?>
                            </p>
                            <a href="destination-details.php?id=<?php echo $destination['destination_id']; ?>"
                               class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- =========================
     FEATURED PACKAGES
========================= -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-title mb-4">
            <h2>Popular Tour Packages</h2>
        </div>
        <div class="row g-4">
            <?php while ($package = mysqli_fetch_assoc($featured_packages)) : ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <img src="images/<?php echo htmlspecialchars($package['image_url']); ?>"
                                     class="img-fluid h-100 w-100"
                                     style="object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($package['package_name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x400?text=<?php echo urlencode($package['package_name']); ?>'">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column h-100">
                                    <h5 class="card-title"><?php echo htmlspecialchars($package['package_name']); ?></h5>
                                    <p class="card-text flex-grow-1">
                                        <?php echo substr(htmlspecialchars($package['description']), 0, 120); ?>...
                                    </p>
                                    <p><strong>PKR <?php echo number_format($package['price_per_person']); ?></strong></p>
                                    <a href="package-details.php?id=<?php echo $package['package_id']; ?>"
                                       class="btn btn-primary mt-auto">View Package</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- =========================
     TRAVEL TIPS
========================= -->
<section class="py-5">
    <div class="container">
        <div class="section-title mb-4">
            <h2>Travel Tips</h2>
        </div>
        <div class="row g-4">
            <?php while ($tip = mysqli_fetch_assoc($tips)) : ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="images/<?php echo htmlspecialchars($tip['image_url']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($tip['title']); ?>"
                             style="height:200px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/400x250?text=Travel+Tips'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tip['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($tip['summary']),0,120); ?>...</p>
                            <p class="small text-muted mb-2">
                                Author: <?php echo htmlspecialchars($tip['author']); ?><br>
                                Published: <?php echo date("d M Y", strtotime($tip['published_date'])); ?><br>
                                Views: <?php echo $tip['views']; ?>
                            </p>
                            <a href="travel-tips-details.php?id=<?php echo $tip['article_id']; ?>"
                               class="btn btn-outline-primary w-100">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
