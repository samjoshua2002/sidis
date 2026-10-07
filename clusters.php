<?php
require_once __DIR__ . '/config.php';
include 'header.php';

$clusterSql = "
    SELECT
        id,
        cluster_name,
        card_image,
        page_type,
        website_url
    FROM clusters
    WHERE status = 1
    ORDER BY display_order ASC, id ASC
";
$clusterStmt = $pdo->prepare($clusterSql);
$clusterStmt->execute();
$clustersList = $clusterStmt->fetchAll(PDO::FETCH_ASSOC);

function resolveCardImage($path) {
    if (empty($path)) return 'images/frame 3.png';
    $path = trim($path);
    if (strpos($path, 'images/') === 0 || strpos($path, '/') === 0) {
        return $path;
    }
    if (file_exists(__DIR__ . '/images/clusters/' . $path)) {
        return 'images/clusters/' . $path;
    }
    if (file_exists(__DIR__ . '/images/' . $path)) {
        return 'images/' . $path;
    }
    return 'images/clusters/' . $path;
}
?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;" alt="Clusters Banner">

    <div class="container">
        <div class="hero-content about-section">
            <h2 style="color: #fff;">Clusters</h2>
        </div>
    </div>
</section>

<style>
    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    @media (max-width: 768px) {
        .hero-content h2 {
            font-size: 22px;
        }
        .hero-img {
            height: 150px !important;
        }
        .hero-content {
            top: 70% !important;
            background-color: #1F3C44;
            padding: 5px 20px;
            left: 0px;
        }
    }
</style>

<section class="about-section" style="padding-top: 0; padding-bottom: 60px;">
    <div class="container">
        <div class="row" style="padding-top: 50px;">
            <?php if (!empty($clustersList)): ?>
                <?php foreach ($clustersList as $cluster): ?>
                    <?php
                    $cardImg = resolveCardImage($cluster['card_image']);
                    $isExternal = ($cluster['page_type'] === 'website' && !empty($cluster['website_url']));
                    $linkUrl = $isExternal ? $cluster['website_url'] : 'innercluster.php?id=' . (int)$cluster['id'];
                    $targetAttr = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="cluster-card">
                            <img src="<?= htmlspecialchars($cardImg, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <p><?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <a href="<?= htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success"<?= $targetAttr ?>>READ MORE</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p>No clusters found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>