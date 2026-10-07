<?php
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? '';
if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: clusters.php');
    exit;
}
$id = (int)$id;

$stmt = $pdo->prepare("
    SELECT *
    FROM clusters
    WHERE id = ? AND status = 1
    LIMIT 1
");
$stmt->execute([$id]);
$cluster = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cluster) {
    header('Location: clusters.php');
    exit;
}

// If this cluster is set to external website only, redirect directly
if ($cluster['page_type'] === 'website' && !empty($cluster['website_url'])) {
    header('Location: ' . $cluster['website_url']);
    exit;
}

// Fetch coordinators for this cluster
$coordStmt = $pdo->prepare("
    SELECT id, name, image, department, designation, email, personal_page, coordinator_role, coordinator_programme, coordinator_order
    FROM faculty
    WHERE status = 1
      AND (
            FIND_IN_SET(?, cluster_id) > 0
            OR cluster_id = ?
          )
      AND coordinator_role IS NOT NULL
      AND TRIM(coordinator_role) != ''
    ORDER BY coordinator_order ASC, name ASC, id ASC
");
$coordStmt->execute([(string)$id, (string)$id]);
$coordinators = $coordStmt->fetchAll(PDO::FETCH_ASSOC);

function resolveClusterImage($path, $fallback = '') {
    if (empty($path)) return $fallback;
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

function resolveFacultyImage($path, $fallback = 'images/member.png') {
    if (empty($path)) return $fallback;
    $path = trim($path);
    if (strpos($path, 'images/') === 0 || strpos($path, '/') === 0) {
        return $path;
    }
    if (file_exists(__DIR__ . '/images/faculty/' . $path)) {
        return 'images/faculty/' . $path;
    }
    if (file_exists(__DIR__ . '/images/' . $path)) {
        return 'images/' . $path;
    }
    return 'images/faculty/' . $path;
}

function renderPointsList($text) {
    if (empty(trim($text ?? ''))) return '';
    if (stripos($text, '<ul') !== false || stripos($text, '<li') !== false) {
        return $text;
    }
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    if (empty($lines)) return '';
    $out = '<ul class="cluster-points">';
    foreach ($lines as $line) {
        $clean = preg_replace('/^[\x{2022}\-\*\d+\.]\s*/u', '', $line);
        $out .= '<li><p>' . htmlspecialchars($clean, ENT_QUOTES, 'UTF-8') . '</p></li>';
    }
    $out .= '</ul>';
    return $out;
}

// Decode dynamic sections
$sections = [];
if (!empty($cluster['sections'])) {
    $decoded = json_decode($cluster['sections'], true);
    if (is_array($decoded)) {
        $sections = $decoded;
    }
}

// Fallback to legacy fields if sections array is empty
if (empty($sections)) {
    if (!empty($cluster['academic_title']) || !empty($cluster['academic_points']) || !empty($cluster['academic_description']) || !empty($cluster['academic_image'])) {
        $sections[] = [
            'title' => $cluster['academic_title'] ?: 'Academic programmes',
            'subtitle' => $cluster['academic_subtitle'] ?? '',
            'description' => $cluster['academic_description'] ?? '',
            'points' => $cluster['academic_points'] ?? '',
            'image' => $cluster['academic_image'] ?? ''
        ];
    }
    if (!empty($cluster['research_title']) || !empty($cluster['research_points']) || !empty($cluster['research_description']) || !empty($cluster['research_image'])) {
        $sections[] = [
            'title' => $cluster['research_title'] ?? '',
            'subtitle' => $cluster['research_subtitle'] ?? '',
            'description' => $cluster['research_description'] ?? '',
            'points' => $cluster['research_points'] ?? '',
            'image' => $cluster['research_image'] ?? ''
        ];
    }
}

$bannerImg = resolveClusterImage($cluster['banner_image'] ?? '', 'images/about-banner.png');

include 'header.php';
?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="<?= htmlspecialchars($bannerImg, ENT_QUOTES, 'UTF-8') ?>" class="hero-img" style="height: auto;" alt="<?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?>">

    <div class="container">
        <div class="hero-content about-section">
            <h2><?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
    </div>
</section>

<style>
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

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
    }

    .clusters-side-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        padding-bottom: 10px;
    }

    .about-section p {
        font-family: 'Inter', sans-serif;
        margin: 5px 0;
        line-height: 1.6;
    }

    .cluster-points {
        padding-left: 20px;
        margin-top: 10px;
        margin-bottom: 25px;
    }

    .cluster-points li {
        margin-bottom: 8px;
    }

    .cluster-points li p {
        margin: 0;
    }

    /* Full-width section styling when image is present */
    .cluster-split-section {
        padding: 50px 0;
        width: 100%;
    }

    .cluster-split-section.bg-gray-section {
        background-color: #F5F5F5 !important;
    }

    .cluster-split-section.bg-white-section {
        background-color: #ffffff !important;
    }

    .cluster-standard-section {
        width: 100%;
        background-color: #ffffff;
    }

    .cluster-section-img-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 10px 0;
    }

    .cluster-section-img-wrapper img {
        width: 100%;
        max-height: 420px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .cluster-img-fullwidth img {
        width: auto;
        max-width: 100%;
        max-height: 550px;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    .faculty-card {
        padding-bottom: 25px;
        margin-bottom: 15px;
        border-bottom: 1px solid #dcdcdc;
    }

    .coordinator-avatar {
        flex-shrink: 0;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        overflow: hidden;
        background: #EAF2F8;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .coordinator-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .faculty-content {
        flex-grow: 1;
    }

    .faculty-content h3.clusters-side-heading {
        margin-bottom: 6px;
        padding-bottom: 0;
        font-size: 22px;
    }

    .faculty-content p {
        margin: 4px 0;
        color: #333;
    }

    .faculty-content a.coord-email {
        color: #184C74;
        text-decoration: none;
    }

    .faculty-content a.coord-email:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .faculty-card {
            flex-direction: column;
            align-items: flex-start !important;
            text-align: left !important;
        }
        .faculty-content.ms-4 {
            margin-left: 0 !important;
            margin-top: 15px;
            text-align: left !important;
        }
        .coordinator-avatar {
            margin: 0 !important;
            width: 170px;
            height: 170px;
        }
    }
</style>

<!-- OPTIONAL EXTERNAL WEBSITE LINK -->
<?php if (!empty($cluster['website_url'])): ?>
    <?php
    $rawWebsiteUrl = trim($cluster['website_url']);
    $websiteHref = preg_match('#^https?://#i', $rawWebsiteUrl) ? $rawWebsiteUrl : 'https://' . $rawWebsiteUrl;
    $websiteDisplay = preg_replace('#^https?://#i', '', $rawWebsiteUrl);
    $websiteDisplay = rtrim($websiteDisplay, '/');
    ?>
    <section class="about-section cluster-website-link-section" style="padding-top: 35px; padding-bottom: 5px; background-color: #ffffff;">
        <div class="container">
            <div class="row align-items-center flex-wrap my-2">
                <div class="col-auto">
                    <h3 class="clusters-side-heading mb-0" style="padding-bottom: 0;">
                        Click here to view the website
                    </h3>
                </div>
                <div class="col-auto" style="margin-bottom: 2px;">
                    <a href="<?= htmlspecialchars($websiteHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" style="color: #000; font-size: 18px; text-decoration: underline;">
                        <?= htmlspecialchars($websiteDisplay, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- DYNAMIC CONTENT SECTIONS -->
<?php
// Gather active content sections
$activeSections = [];
$totalSplitImages = 0;
foreach ($sections as $s) {
    $hasText = !empty($s['title']) || !empty($s['subtitle']) ||
               !empty($s['description']) || !empty($s['points']);
    $imgPath = !empty($s['image']) ? resolveClusterImage($s['image']) : '';
    $hasImg  = !empty($imgPath);

    if (!$hasText && !$hasImg) continue;

    $isSplit = ($hasImg && $hasText);
    if ($isSplit) {
        $totalSplitImages++;
    }

    $activeSections[] = [
        'data'     => $s,
        'image'    => $imgPath,
        'hasImage' => $hasImg,
        'hasText'  => $hasText,
        'isSplit'  => $isSplit
    ];
}

$firstIsSplit = !empty($activeSections[0]['isSplit']);
$splitCounter = 0;
$prevHadImage = false;

foreach ($activeSections as $secIndex => $secItem):
    $sec = $secItem['data'];
    $hasImage = $secItem['hasImage'];
    $hasText  = $secItem['hasText'];
    $isSplit  = $secItem['isSplit'];
    $secImgPath = $secItem['image'];

    if ($hasImage) {
        if ($isSplit) {
            $splitCounter++;
            if ($totalSplitImages >= 2) {
                // If 2 or more split sections: alternate White and #F5F5F5 (vice versa)
                if ($firstIsSplit) {
                    $bgClass = ($splitCounter % 2 === 1) ? 'bg-white-section' : 'bg-gray-section';
                } else {
                    $bgClass = ($splitCounter % 2 === 1) ? 'bg-gray-section' : 'bg-white-section';
                }
            } else {
                // Only 1 split image section: #F5F5F5 background
                $bgClass = 'bg-gray-section';
            }
        } else {
            // Full width image only: always white background
            $bgClass = 'bg-white-section';
        }
    } else {
        $bgClass = 'bg-white-section';
    }
?>
    <?php if ($hasImage): ?>
        <?php if ($hasText): ?>
            <!-- Left-Right section with alternating background (White / #F5F5F5) -->
            <section class="about-section cluster-split-section <?= $bgClass ?>">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?php if (!empty($sec['title'])): ?>
                                <h3 class="clusters-side-heading"><?= htmlspecialchars($sec['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php endif; ?>

                            <?php if (!empty($sec['subtitle'])): ?>
                                <p><strong><?= htmlspecialchars($sec['subtitle'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                            <?php endif; ?>

                            <?php if (!empty($sec['description'])): ?>
                                <p><?= nl2br(htmlspecialchars($sec['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>

                            <?= renderPointsList($sec['points'] ?? '') ?>
                        </div>
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="cluster-section-img-wrapper">
                                <img src="<?= htmlspecialchars($secImgPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($sec['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <!-- Full-width (col-md-12) image only section on clean white background -->
            <section class="about-section cluster-split-section bg-white-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-12 text-center">
                            <div class="cluster-section-img-wrapper cluster-img-fullwidth">
                                <img src="<?= htmlspecialchars($secImgPath, ENT_QUOTES, 'UTF-8') ?>" alt="Cluster image">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $padTop = ($secIndex === 0) ? (!empty($cluster['website_url']) ? '15px' : '40px') : ($prevHadImage ? '40px' : '20px');
        ?>
        <!-- Standard Content Section -->
        <section class="about-section cluster-standard-section" style="padding-top: <?= $padTop ?>; padding-bottom: 25px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <?php if (!empty($sec['title'])): ?>
                            <h3 class="clusters-side-heading"><?= htmlspecialchars($sec['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php endif; ?>

                        <?php if (!empty($sec['subtitle'])): ?>
                            <p><strong><?= htmlspecialchars($sec['subtitle'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php endif; ?>

                        <?php if (!empty($sec['description'])): ?>
                            <p><?= nl2br(htmlspecialchars($sec['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>

                        <?= renderPointsList($sec['points'] ?? '') ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php
    $prevHadImage = $hasImage;
endforeach;
?>

<!-- Coordinators Name Section -->
<?php if (!empty($coordinators)): ?>
    <section class="about-section cluster-coordinators-section" style="background-color: #ffffff; padding-top: <?= ($prevHadImage ?? false) ? '40px' : '25px' ?>; padding-bottom: 70px;">
        <div class="container">
            <h3 class="clusters-side-heading">Co-ordinators Name</h3>

            <div class="row mt-3 faculty-row">
                <?php foreach ($coordinators as $coord): ?>
                    <?php $coordImage = resolveFacultyImage($coord['image']); ?>
                    <div class="col-12 mb-4">
                        <div class="d-flex align-items-center faculty-card">
                            <div class="coordinator-avatar">
                                <img src="<?= htmlspecialchars($coordImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($coord['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            </div>

                            <div class="faculty-content ms-4">
                                <h3 class="clusters-side-heading"><?= htmlspecialchars($coord['name'], ENT_QUOTES, 'UTF-8') ?></h3>

                                <?php if (!empty($coord['coordinator_role'])): ?>
                                    <p><strong><?= htmlspecialchars($coord['coordinator_role'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <?php endif; ?>

                                <?php if (!empty($coord['coordinator_programme'])): ?>
                                    <p><?= htmlspecialchars($coord['coordinator_programme'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <?php if (!empty($coord['designation'])): ?>
                                    <p><strong><?= htmlspecialchars($coord['designation'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <?php endif; ?>

                                <?php if (!empty($coord['department'])): ?>
                                    <p><?= htmlspecialchars($coord['department'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <?php if (!empty($coord['email'])): ?>
                                    <p style="display:flex; align-items:center; gap:8px;">
                                        <img src="images/email.svg" alt="Email" style="width: 16px; height: 16px; object-fit: contain;">
                                        <a href="mailto:<?= htmlspecialchars($coord['email'], ENT_QUOTES, 'UTF-8') ?>" class="coord-email">
                                            <?= htmlspecialchars($coord['email'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'footer.php'; ?>
