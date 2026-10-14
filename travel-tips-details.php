<?php
session_start();
require_once 'includes/config.php';

/**
 * Detect the primary ID column in travel_tips table.
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
    return 'tip_id';
}

$idCol = detectTipIdColumn($conn);

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header("Location: travel-tips.php");
    exit;
}

/**
 * ✅ Prevent view spam:
 * Count view only once per session per article
 */
if (!isset($_SESSION['viewed_tips'])) {
    $_SESSION['viewed_tips'] = [];
}

$viewKey = $idCol . ':' . $article_id;

if (!in_array($viewKey, $_SESSION['viewed_tips'], true)) {
    // Increment view
    $upd = $conn->prepare("UPDATE travel_tips SET views = views + 1 WHERE {$idCol} = ?");
    if ($upd) {
        $upd->bind_param("i", $article_id);
        $upd->execute();
    }
    $_SESSION['viewed_tips'][] = $viewKey;
}

// Fetch the article
$stmt = $conn->prepare("SELECT * FROM travel_tips WHERE {$idCol} = ? LIMIT 1");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $article_id);
$stmt->execute();

$res = $stmt->get_result();
$article = $res->fetch_assoc();

if (!$article) {
    header("Location: travel-tips.php");
    exit;
}

$page_title = htmlspecialchars($article['title'] ?? 'Travel Tip');
include 'includes/header.php';
?>

<style>
.article-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    margin-bottom: 40px;
}
.article-meta {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    flex-wrap: wrap;
}
.article-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.2);
    padding: 8px 15px;
    border-radius: 20px;
}
.article-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.placeholder-image {
    width: 100%;
    height: 400px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 5rem;
    border-radius: 10px;
    margin-bottom: 30px;
}
.article-content {
    font-size: 18px;
    line-height: 1.8;
    color: #333;
}
</style>

<div class="article-header">
    <div class="container">
        <a href="travel-tips.php" class="btn btn-light mb-4">
            <i class="fas fa-arrow-left"></i> Back to Travel Tips
        </a>

        <h1 class="display-4 mb-3"><?php echo htmlspecialchars($article['title'] ?? ''); ?></h1>

        <div class="article-meta">
            <div class="article-meta-item">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars($article['author'] ?? 'Admin'); ?></span>
            </div>
            <div class="article-meta-item">
                <i class="fas fa-calendar"></i>
                <span><?php echo date('d M Y', strtotime($article['published_date'] ?? 'now')); ?></span>
            </div>
            <div class="article-meta-item">
                <i class="fas fa-eye"></i>
                <span><?php echo number_format((int)($article['views'] ?? 0)); ?> Views</span>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <?php
            $image = $article['image_path'] ?? $article['image_url'] ?? '';
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
                     alt="<?php echo htmlspecialchars($article['title'] ?? ''); ?>"
                     class="article-image">
            <?php else: ?>
                <div class="placeholder-image">
                    <i class="fas fa-newspaper"></i>
                </div>
            <?php endif; ?>

            <?php
            $summary = $article['summary'] ?? $article['excerpt'] ?? '';
            if (!empty($summary)):
            ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Summary</h5>
                    <p class="mb-0"><?php echo strip_tags($summary); ?></p>
                </div>
            <?php endif; ?>

            <div class="article-content">
                <?php
                $content = $article['content'] ?? $article['body'] ?? '';
                if (strip_tags($content) !== $content) {
                    echo $content; // contains HTML
                } else {
                    echo nl2br(htmlspecialchars($content));
                }
                ?>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
