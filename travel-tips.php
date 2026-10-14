<?php
require_once 'includes/config.php';
$page_title = "Travel Tips";

/**
 * Detect the primary ID column in travel_tips table.
 * It tries tip_id, article_id, id in that order.
 */
function detectTipIdColumn(mysqli $conn): string {
    $candidates = ['tip_id', 'article_id', 'id'];
    foreach ($candidates as $col) {
        $safe = $conn->real_escape_string($col);
        $check = $conn->query("SHOW COLUMNS FROM travel_tips LIKE '{$safe}'");
        if ($check && $check->num_rows > 0) {
            return $col;
        }
    }
    // fallback (if your table uses something else)
    return 'tip_id';
}

$idCol = detectTipIdColumn($conn);

// Get all travel tips (NO VIEW INCREMENT HERE!)
$query = "SELECT * FROM travel_tips ORDER BY published_date DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<style>
.tips-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}
.tip-card {
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.tip-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.tip-card img {
    height: 250px;
    object-fit: cover;
    width: 100%;
}
.tip-card .placeholder-image {
    height: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}
.tip-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 10px;
}
.card-text { min-height: 60px; }
</style>

<div class="tips-header">
    <div class="container">
        <h1 class="display-4"><i class="fas fa-lightbulb"></i> Travel Tips & Guides</h1>
        <p class="lead">Expert advice and insider tips for traveling in Pakistan</p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($tip = mysqli_fetch_assoc($result)): ?>

                <?php
                // ✅ Use detected ID column so it never breaks
                $article_id = isset($tip[$idCol]) ? (int)$tip[$idCol] : 0;
                ?>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card tip-card">
                        <?php
                        $image = $tip['image_path'] ?? $tip['image_url'] ?? '';
                        if (!empty($image)) {
                            $possible_paths = [
                                $image,
                                'images/' . $image,
                                'images/' . basename($image),
                                'uploads/tips/' . basename($image),
                            ];
                            $image_found = false;
                            foreach ($possible_paths as $path) {
                                if (file_exists($path)) {
                                    $image = $path;
                                    $image_found = true;
                                    break;
                                }
                            }
                        }
                        ?>

                        <?php if (!empty($image) && !empty($image_found)): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($tip['title']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($tip['title']); ?></h5>

                            <p class="card-text text-muted">
                                <?php
                                $text = $tip['summary'] ?? $tip['excerpt'] ?? $tip['content'] ?? '';
                                $text = strip_tags($text);
                                $text = mb_substr($text, 0, 150);
                                echo htmlspecialchars($text) . '...';
                                ?>
                            </p>

                            <div class="tip-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($tip['author'] ?? 'Admin'); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($tip['published_date'] ?? 'now')); ?></span>
                            </div>

                            <div class="tip-meta">
                                <span><i class="fas fa-eye"></i> <?php echo number_format((int)($tip['views'] ?? 0)); ?> Views</span>
                            </div>

                            <div class="mt-auto pt-3">
                                <?php if ($article_id > 0): ?>
                                    <!-- ✅ Correct redirect -->
                                    <a href="travel-tips-details.php?id=<?php echo $article_id; ?>"
                                       class="btn btn-primary w-100">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        Missing ID (fix DB column)
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>No travel tips available yet</h4>
                    <p>Check back soon for helpful travel tips and guides!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
