<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$error = '';

$id = $_GET['id'] ?? $_POST['id'] ?? '';
if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: clusters.php');
    exit;
}
$id = (int)$id;

$stmt = $pdo->prepare("SELECT * FROM clusters WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$cluster = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cluster) {
    header('Location: clusters.php');
    exit;
}

$uploadDir = __DIR__ . '/../images/clusters/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

function decodeInput($value) {
    if (empty($value)) return '';
    $decoded = base64_decode($value, true);
    if ($decoded !== false && $decoded !== '') {
        return trim($decoded);
    }
    return trim($value);
}

function reconstructChunks($fieldName) {
    $countField = $fieldName . '_chunk_count';
    if (!isset($_POST[$countField]) || (int)$_POST[$countField] === 0) {
        if (isset($_POST[$fieldName . '_encoded'])) {
            return decodeInput($_POST[$fieldName . '_encoded']);
        }
        if (isset($_POST[$fieldName])) {
            return trim($_POST[$fieldName]);
        }
        return '';
    }
    $chunkCount = (int)$_POST[$countField];
    $base64 = '';
    for ($i = 0; $i < $chunkCount; $i++) {
        $chunkName = $fieldName . '_chunk_' . $i;
        if (isset($_POST[$chunkName])) {
            $base64 .= $_POST[$chunkName];
        }
    }
    return decodeInput($base64);
}

// Preset options
$cardPresets = [
    'images/frame 3.png'  => 'Frame 3 (AMQI style)',
    'images/frame 4.png'  => 'Frame 4 (Blue Economy style)',
    'images/frame 5.png'  => 'Frame 5 (Computational Eng style)',
    'images/frame 6.png'  => 'Frame 6 (Management style)',
    'images/frame 7.png'  => 'Frame 7 (Power Conversion style)',
    'images/frame 8.png'  => 'Frame 8 (Robotics style)',
    'images/frame 9.png'  => 'Frame 9 (Sustainability style)',
    'images/frame 10.png' => 'Frame 10 (Innovation style)',
    'images/frame 11.png' => 'Frame 11 (Sports Science style)',
];

$bannerPresets = [
    'images/about-banner.png' => 'Default SIDIS Banner',
    'images/amqi-banner.png'  => 'Quantum & Materials Banner',
    'images/be-banner.png'    => 'Blue Economy Banner',
    'images/ce-banner.png'    => 'Computational Eng Banner',
    'images/cessa-banner.png' => 'Sports Science Banner',
    'images/mpp-banner.png'   => 'Management Banner',
    'images/pcs-banner.png'   => 'Power Conversion Banner',
    'images/rc-banner.png'    => 'Robotics Banner',
    'images/sie-banner.png'   => 'Innovation Banner',
    'images/ss-banner.png'    => 'Sustainability Banner',
];

function processImageUpload($fileItem, $prefix, $uploadDir, &$error) {
    if (!isset($fileItem) || $fileItem['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($fileItem['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error uploading image.';
        return null;
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($fileItem['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        $error = 'Only JPG, PNG and WEBP images are allowed.';
        return null;
    }
    if ($fileItem['size'] > 5 * 1024 * 1024) {
        $error = 'Image size must not exceed 5 MB.';
        return null;
    }
    if (@getimagesize($fileItem['tmp_name']) === false) {
        $error = 'Uploaded file is not a valid image.';
        return null;
    }

    $ext = $allowedTypes[$mimeType];
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($fileItem['tmp_name'], $targetPath)) {
        $error = 'Failed to save uploaded image.';
        return null;
    }

    return $filename;
}

function resolveAdminThumb($path) {
    if (empty($path)) return '../images/frame 3.png';
    $path = trim($path);
    if (strpos($path, 'images/') === 0) {
        return '../' . $path;
    }
    if (strpos($path, '/') === 0) {
        return $path;
    }
    if (file_exists(__DIR__ . '/../images/clusters/' . $path)) {
        return '../images/clusters/' . $path;
    }
    if (file_exists(__DIR__ . '/../images/' . $path)) {
        return '../images/' . $path;
    }
    return '../images/clusters/' . $path;
}

function resolveFacultyThumb($path) {
    if (empty($path)) return '../images/member.png';
    $path = trim($path);
    if (strpos($path, 'images/') === 0) {
        return '../' . $path;
    }
    if (file_exists(__DIR__ . '/../images/faculty/' . $path)) {
        return '../images/faculty/' . $path;
    }
    if (file_exists(__DIR__ . '/../images/' . $path)) {
        return '../images/' . $path;
    }
    return '../images/faculty/' . $path;
}

// Pre-fill
$cluster_name  = $cluster['cluster_name'];
$page_type     = $cluster['page_type'] ?? 'inner';
$website_url   = $cluster['website_url'] ?? '';
$display_order = $cluster['display_order'] ?? 0;
$card_image    = $cluster['card_image'] ?? 'images/frame 3.png';
$banner_image  = $cluster['banner_image'] ?? 'images/about-banner.png';

// Fetch coordinators assigned to this cluster
$coordinatorStmt = $pdo->prepare("
    SELECT id, name, image, department, designation, email, coordinator_role, coordinator_programme, coordinator_order
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
$coordinatorStmt->execute([(string)$id, (string)$id]);
$clusterCoordinators = $coordinatorStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active faculty members for the modal dropdown
$allFacultyStmt = $pdo->query("SELECT id, name, department, designation, image FROM faculty WHERE status = 1 ORDER BY name ASC");
$allFacultyList = $allFacultyStmt->fetchAll(PDO::FETCH_ASSOC);

// Decode sections
$sections = [];
if (!empty($cluster['sections'])) {
    $decoded = json_decode($cluster['sections'], true);
    if (is_array($decoded)) {
        $sections = $decoded;
    }
}
if (empty($sections)) {
    if (!empty($cluster['academic_title']) || !empty($cluster['academic_points']) || !empty($cluster['academic_description']) || !empty($cluster['academic_image'])) {
        $sections[] = [
            'title'       => $cluster['academic_title'] ?: 'Academic programmes',
            'subtitle'    => $cluster['academic_subtitle'] ?? '',
            'description' => $cluster['academic_description'] ?? '',
            'points'      => $cluster['academic_points'] ?? '',
            'image'       => $cluster['academic_image'] ?? ''
        ];
    }
    if (!empty($cluster['research_title']) || !empty($cluster['research_points']) || !empty($cluster['research_description']) || !empty($cluster['research_image'])) {
        $sections[] = [
            'title'       => $cluster['research_title'] ?? '',
            'subtitle'    => $cluster['research_subtitle'] ?? '',
            'description' => $cluster['research_description'] ?? '',
            'points'      => $cluster['research_points'] ?? '',
            'image'       => $cluster['research_image'] ?? ''
        ];
    }
}
if (empty($sections)) {
    $sections = [
        [
            'title'       => 'Academic programmes',
            'subtitle'    => '',
            'description' => '',
            'points'      => '',
            'image'       => ''
        ]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cluster_name  = reconstructChunks('cluster_name');
    if ($cluster_name === '') {
        $cluster_name = trim($_POST['cluster_name'] ?? '');
    }

    $page_type = ($_POST['page_type'] ?? 'inner') === 'website' ? 'website' : 'inner';

    $innerUrl = reconstructChunks('inner_website_url');
    if ($innerUrl === '') {
        $innerUrl = trim($_POST['inner_website_url'] ?? '');
    }

    $extUrl = reconstructChunks('external_website_url');
    if ($extUrl === '') {
        $extUrl = trim($_POST['external_website_url'] ?? '');
    }

    if ($page_type === 'website') {
        $website_url = $extUrl !== '' ? $extUrl : trim($_POST['website_url'] ?? '');
    } else {
        $website_url = $innerUrl !== '' ? $innerUrl : trim($_POST['website_url'] ?? '');
    }

    $display_order = (int)($_POST['display_order'] ?? 0);

    if ($cluster_name === '') {
        $error = 'Please enter the cluster name.';
    }

    if ($page_type === 'website' && $website_url === '') {
        $error = 'Please enter the Website URL for external redirect.';
    }

    if ($error === '') {
        $stmt = $pdo->prepare("SELECT id FROM clusters WHERE cluster_name = ? AND id != ? LIMIT 1");
        $stmt->execute([$cluster_name, $id]);
        if ($stmt->fetch()) {
            $error = 'This cluster already exists.';
        }
    }

    $uploadedFiles = [];
    $finalCardImage = $card_image;
    $finalBannerImage = $banner_image;

    // Preset selection or custom upload for card
    if (!empty($_POST['card_preset'])) {
        $finalCardImage = trim($_POST['card_preset']);
    }
    if (isset($_FILES['card_image']) && $_FILES['card_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newCard = processImageUpload($_FILES['card_image'], 'card', $uploadDir, $error);
        if ($newCard) {
            $finalCardImage = $newCard;
            $uploadedFiles[] = $uploadDir . $newCard;
        }
    }

    $processedSections = [];
    if ($page_type === 'inner') {
        // Preset selection or custom upload for banner
        if (!empty($_POST['banner_preset'])) {
            $finalBannerImage = trim($_POST['banner_preset']);
        }
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newBanner = processImageUpload($_FILES['banner_image'], 'banner', $uploadDir, $error);
            if ($newBanner) {
                $finalBannerImage = $newBanner;
                $uploadedFiles[] = $uploadDir . $newBanner;
            }
        }

        // Process dynamic sections from chunked JSON or fallback
        $postSections = [];
        $sectionsRaw = reconstructChunks('sections');
        if (!empty($sectionsRaw)) {
            $decoded = json_decode($sectionsRaw, true);
            if (is_array($decoded)) {
                $postSections = $decoded;
            }
        }
        if (empty($postSections) && isset($_POST['sections']) && is_array($_POST['sections'])) {
            $postSections = $_POST['sections'];
        }

        if (is_array($postSections)) {
            foreach ($postSections as $idx => $sec) {
                $secTitle    = trim($sec['title'] ?? '');
                $secSubtitle = trim($sec['subtitle'] ?? '');
                $secDesc     = trim($sec['description'] ?? '');
                $secPoints   = trim($sec['points'] ?? '');
                $existingImg = trim($sec['existing_image'] ?? '');

                if (!empty($sec['remove_image'])) {
                    $existingImg = '';
                }

                if (isset($_FILES['section_images']['name'][$idx]) && $_FILES['section_images']['error'][$idx] !== UPLOAD_ERR_NO_FILE) {
                    $fileItem = [
                        'name'     => $_FILES['section_images']['name'][$idx],
                        'type'     => $_FILES['section_images']['type'][$idx],
                        'tmp_name' => $_FILES['section_images']['tmp_name'][$idx],
                        'error'    => $_FILES['section_images']['error'][$idx],
                        'size'     => $_FILES['section_images']['size'][$idx]
                    ];
                    $secUploaded = processImageUpload($fileItem, 'sec_' . $idx, $uploadDir, $error);
                    if ($secUploaded) {
                        $existingImg = $secUploaded;
                        $uploadedFiles[] = $uploadDir . $secUploaded;
                    }
                }

                if ($secTitle !== '' || $secDesc !== '' || $secPoints !== '' || $existingImg !== '') {
                    $processedSections[] = [
                        'title'       => $secTitle,
                        'subtitle'    => $secSubtitle,
                        'description' => $secDesc,
                        'points'      => $secPoints,
                        'image'       => $existingImg
                    ];
                }
            }
        }
    }

    if ($error === '') {
        try {
            $sectionsJson = ($page_type === 'inner' && !empty($processedSections))
                ? json_encode($processedSections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;

            $stmt = $pdo->prepare("
                UPDATE clusters
                SET
                    cluster_name = ?, card_image = ?, banner_image = ?, page_type = ?,
                    website_url = ?, sections = ?, display_order = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $cluster_name,
                $finalCardImage,
                $finalBannerImage,
                $page_type,
                $website_url,
                $sectionsJson,
                $display_order,
                $id
            ]);

            header('Location: clusters.php?success=updated');
            exit;
        } catch (PDOException $e) {
            foreach ($uploadedFiles as $filePath) {
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $error = 'Unable to update the cluster. Please try again.';
        }
    } else {
        foreach ($uploadedFiles as $filePath) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}

include 'header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <div>
            <h2>Edit Cluster</h2>
            <p>Update cluster details, destination link, and dynamic content sections.</p>
        </div>
        <div>
            <?php if ($page_type === 'website' && !empty($website_url)): ?>
                <a href="<?= htmlspecialchars($website_url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-secondary" style="background:#047857; color:#fff;">
                    Visit Website &#8599;
                </a>
            <?php else: ?>
                <a href="../innercluster.php?id=<?= $id ?>" target="_blank" class="btn btn-secondary" style="background:#184C74; color:#fff;">
                    View Live Page &#8599;
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <form method="POST" action="" enctype="multipart/form-data" id="clusterForm" onsubmit="return prepareClusterSubmit();">
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- BASIC INFO -->
            <div class="card-header" style="padding-left:0; padding-right:0;">
                <div>
                    <h3>Basic Details</h3>
                    <p>Enter the cluster name, order, and card image.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="cluster_name">Cluster Name <span style="color:red;">*</span></label>
                <input
                    type="text"
                    id="cluster_name"
                    name="cluster_name"
                    class="form-control"
                    value="<?= htmlspecialchars($cluster_name, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
                <input type="hidden" id="cluster_name_chunk_count" name="cluster_name_chunk_count" value="0">
                <div id="cluster_name_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="display_order">Display Order</label>
                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    class="form-control"
                    value="<?= htmlspecialchars((string)$display_order, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <!-- CARD IMAGE WITH PRESETS & CURRENT PREVIEW -->
            <div class="form-group" style="background:#f8fafc; padding:18px 20px; border-radius:8px; border:1px solid #e2e8f0;">
                <label style="font-weight:600; color:#184C74; margin-bottom:8px; display:block;">
                    Card Image (Listing on clusters.php)
                </label>

                <?php if (!empty($card_image)): ?>
                    <div style="margin-bottom:12px; display:flex; align-items:center; gap:12px;">
                        <img src="<?= htmlspecialchars(resolveAdminThumb($card_image), ENT_QUOTES, 'UTF-8') ?>" style="height:65px; border-radius:6px; border:1px solid #cbd5e1; display:block;">
                        <span style="font-size:13px; color:#475569;">Current image: <code><?= htmlspecialchars($card_image) ?></code></span>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom:12px;">
                    <label style="font-size:14px; font-weight:500;">Select Preset Suggestion:</label>
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:10px; margin-top:8px;">
                        <?php foreach ($cardPresets as $presetPath => $presetLabel): ?>
                            <label style="border:2px solid <?= $card_image === $presetPath ? '#184C74' : '#e2e8f0' ?>; border-radius:6px; padding:6px; cursor:pointer; text-align:center; background:#fff; display:block;" class="preset-label">
                                <input
                                    type="radio"
                                    name="card_preset"
                                    value="<?= $presetPath ?>"
                                    <?= $card_image === $presetPath ? 'checked' : '' ?>
                                    style="margin-bottom:4px;"
                                >
                                <img src="../<?= $presetPath ?>" alt="" style="width:100%; height:55px; object-fit:cover; border-radius:4px; display:block; margin:4px 0;">
                                <span style="font-size:11px; color:#475569; display:block; line-height:1.2;"><?= htmlspecialchars($presetLabel) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-top:14px;">
                    <label for="card_image" style="font-size:14px; font-weight:500;">Or Upload New Custom Card Image:</label>
                    <input type="file" id="card_image" name="card_image" class="form-control" accept="image/*">
                </div>
            </div>

            <!-- DESTINATION TYPE: INNER PAGE vs EXTERNAL WEBSITE LINK -->
            <div class="form-group" style="background:#f1f5f9; padding:18px 20px; border-radius:8px; border:1px solid #cbd5e1; margin-top:25px;">
                <label style="font-weight:600; font-size:16px; margin-bottom:12px; display:block; color:#184C74;">
                    Cluster Link Type <span style="color:red;">*</span>
                </label>

                <div style="display:flex; gap:30px; align-items:center;">
                    <label style="font-size:15px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input
                            type="radio"
                            name="page_type"
                            value="inner"
                            id="typeInner"
                            <?= $page_type === 'inner' ? 'checked' : '' ?>
                            onchange="togglePageType()"
                        >
                        <strong>Inner Page</strong> (Displays dynamic inner cluster page with banner & content sections)
                    </label>

                    <label style="font-size:15px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input
                            type="radio"
                            name="page_type"
                            value="website"
                            id="typeWebsite"
                            <?= $page_type === 'website' ? 'checked' : '' ?>
                            onchange="togglePageType()"
                        >
                        <strong>External Website Link</strong> (Direct redirect to external website, no inner page needed)
                    </label>
                </div>
            </div>

            <!-- EXTERNAL WEBSITE URL SECTION -->
            <div id="websiteSection" style="<?= $page_type === 'website' ? '' : 'display:none;' ?> margin-top:20px;">
                <div class="form-group" style="background:#f8fafc; padding:18px 20px; border-radius:8px; border:1px solid #cbd5e1;">
                    <label for="external_website_url" style="font-weight:600; color:#184C74;">Website URL <span style="color:red;">*</span></label>
                    <input
                        type="url"
                        id="external_website_url"
                        name="external_website_url"
                        class="form-control"
                        value="<?= htmlspecialchars($website_url, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="https://sustainability.iitm.ac.in"
                    >
                    <input type="hidden" id="external_website_url_chunk_count" name="external_website_url_chunk_count" value="0">
                    <div id="external_website_url_chunks_container"></div>
                    <small class="form-text text-muted" style="color:#666; display:block; margin-top:4px;">
                        Visitors will be redirected directly to this link when clicking "READ MORE" from the clusters page.
                    </small>
                </div>
            </div>

            <!-- INNER PAGE SECTIONS -->
            <div id="innerPageSection" style="<?= $page_type === 'inner' ? '' : 'display:none;' ?> margin-top:30px;">
                <div class="card-header" style="padding-left:0; padding-right:0;">
                    <div>
                        <h3>Inner Page Configuration</h3>
                        <p>Configure the banner, optional website link, and dynamic content sections.</p>
                    </div>
                </div>

                <!-- OPTIONAL CLUSTER WEBSITE LINK FOR INNER PAGE -->
                <div class="form-group" style="background:#f8fafc; padding:16px 20px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
                    <label for="inner_website_url" style="font-weight:600; color:#184C74; margin-bottom:6px; display:block;">
                        Cluster Website Link (Optional)
                    </label>
                    <input
                        type="url"
                        id="inner_website_url"
                        name="inner_website_url"
                        class="form-control"
                        value="<?= htmlspecialchars($website_url, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="e.g. https://sustainability.iitm.ac.in"
                    >
                    <input type="hidden" id="inner_website_url_chunk_count" name="inner_website_url_chunk_count" value="0">
                    <div id="inner_website_url_chunks_container"></div>
                    <small class="form-text text-muted" style="color:#666; display:block; margin-top:5px;">
                        Optional: If provided, a <strong>"Click here to view the website"</strong> link will be displayed at the top of the inner cluster page.
                    </small>
                </div>

                <!-- BANNER WITH PRESET SUGGESTIONS -->
                <div class="form-group" style="background:#f8fafc; padding:18px 20px; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="font-weight:600; color:#184C74; margin-bottom:8px; display:block;">
                        Hero Banner Image (Top of inner page)
                    </label>

                    <?php if (!empty($banner_image)): ?>
                        <div style="margin-bottom:12px; display:flex; align-items:center; gap:12px;">
                            <img src="<?= htmlspecialchars(resolveAdminThumb($banner_image), ENT_QUOTES, 'UTF-8') ?>" style="height:55px; max-width:200px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1; display:block;">
                            <span style="font-size:13px; color:#475569;">Current banner: <code><?= htmlspecialchars($banner_image) ?></code></span>
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom:12px;">
                        <label style="font-size:14px; font-weight:500;">Select Preset Banner Suggestion:</label>
                        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap:10px; margin-top:8px;">
                            <?php foreach ($bannerPresets as $presetPath => $presetLabel): ?>
                                <label style="border:2px solid <?= $banner_image === $presetPath ? '#184C74' : '#e2e8f0' ?>; border-radius:6px; padding:6px; cursor:pointer; text-align:center; background:#fff; display:block;" class="preset-label">
                                    <input
                                        type="radio"
                                        name="banner_preset"
                                        value="<?= $presetPath ?>"
                                        <?= $banner_image === $presetPath ? 'checked' : '' ?>
                                        style="margin-bottom:4px;"
                                    >
                                    <img src="../<?= $presetPath ?>" alt="" style="width:100%; height:45px; object-fit:cover; border-radius:4px; display:block; margin:4px 0;">
                                    <span style="font-size:11px; color:#475569; display:block; line-height:1.2;"><?= htmlspecialchars($presetLabel) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-top:14px;">
                        <label for="banner_image" style="font-size:14px; font-weight:500;">Or Upload New Custom Banner Image:</label>
                        <input type="file" id="banner_image" name="banner_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid #e2e8f0; margin:30px 0;">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <div>
                        <h3 style="margin:0; font-size:20px; color:#184C74;">Content Sections</h3>
                        <p style="margin:4px 0 0; color:#666; font-size:14px;">
                            Add sections (e.g. Academic programmes, Research topics, Thrust areas). If an image is attached, it renders in a <strong>Left Content, Right Image</strong> layout!
                        </p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSection()" style="background:#184C74; border-color:#184C74;">
                        + Add Section
                    </button>
                </div>

                <!-- Chunked Hidden Storage for Sections -->
                <input type="hidden" id="sections_chunk_count" name="sections_chunk_count" value="0">
                <div id="sections_chunks_container"></div>

                <div id="sectionsContainer">
                    <?php foreach ($sections as $i => $sec): ?>
                        <div class="section-card" data-index="<?= $i ?>" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:20px; margin-bottom:20px; position:relative;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                                <h4 style="margin:0; font-size:16px; color:#1e293b;">Section #<span class="sec-num"><?= $i + 1 ?></span></h4>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeSection(this)" style="padding:4px 10px; font-size:12px;">Remove</button>
                            </div>

                            <input type="hidden" name="sections[<?= $i ?>][existing_image]" class="sec-existing-img" value="<?= htmlspecialchars($sec['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <div class="form-group">
                                <label>Section Title</label>
                                <input type="text" name="sections[<?= $i ?>][title]" class="form-control sec-title" value="<?= htmlspecialchars($sec['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Academic programmes or Research topics:">
                            </div>

                            <div class="form-group">
                                <label>Section Sub-Title (Optional)</label>
                                <input type="text" name="sections[<?= $i ?>][subtitle]" class="form-control sec-subtitle" value="<?= htmlspecialchars($sec['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Interdisciplinary Dual Degree (IDDD) & International Interdisciplinary Masters Programme (I2MP)">
                            </div>

                            <div class="form-group">
                                <label>Description / Paragraph</label>
                                <textarea name="sections[<?= $i ?>][description]" class="form-control sec-desc" rows="3" placeholder="Enter paragraph text or overview..."><?= htmlspecialchars($sec['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Bullet Points (One per line)</label>
                                <textarea name="sections[<?= $i ?>][points]" class="form-control sec-points" rows="3" placeholder="Point 1&#10;Point 2&#10;Point 3"><?= htmlspecialchars($sec['points'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label>Section Image (Optional)</label>
                                <?php if (!empty($sec['image'])): ?>
                                    <div style="margin-bottom: 10px; display:flex; align-items:center; gap:12px;">
                                        <img src="<?= htmlspecialchars(resolveAdminThumb($sec['image']), ENT_QUOTES, 'UTF-8') ?>" style="max-height: 80px; border-radius:6px; border:1px solid #ddd; display:block;">
                                        <label style="font-size:13px; color:#555;">
                                            <input type="checkbox" name="sections[<?= $i ?>][remove_image]" class="sec-remove-img" value="1"> Remove current section image
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="section_images[<?= $i ?>]" class="form-control sec-file-img" accept="image/*">
                                <small class="form-text text-muted" style="color:#666; display:block; margin-top:4px;">
                                    If an image is attached, this section renders as a <strong>Left Content, Right Image</strong> layout. If no image, it spans full width.
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align:right; margin-top:10px;">
                    <button type="button" class="btn btn-secondary" onclick="addSection()" style="background:#e2e8f0; color:#184C74; border-color:#cbd5e1; font-weight:600;">
                        + Add Another Section
                    </button>
                </div>

                <!-- ASSIGNED COORDINATORS DIRECT MANAGEMENT -->
                <hr style="border:0; border-top:1px solid #e2e8f0; margin:35px 0;">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="margin:0; font-size:20px; color:#184C74;">Assigned Coordinators for this Cluster</h3>
                        <p style="margin:4px 0 0; color:#666; font-size:14px;">
                            Manage coordinators displayed on the cluster inner page directly without leaving this page.
                        </p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="openAddCoordinatorModal()" style="background:#184C74; border-color:#184C74; font-weight:600; display:flex; align-items:center; gap:6px;">
                        + Assign Coordinator
                    </button>
                </div>

                <div id="coordinatorsListContainer" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:18px;">
                    <?php if (!empty($clusterCoordinators)): ?>
                        <?php foreach ($clusterCoordinators as $f): ?>
                            <?php
                            $fImg = resolveFacultyThumb($f['image']);
                            $fJson = htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="coordinator-admin-card" style="display:flex; align-items:center; justify-content:space-between; gap:14px; padding:12px 16px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                                <div style="display:flex; align-items:center; gap:14px; min-width:0; flex-grow:1;">
                                    <img src="<?= htmlspecialchars($fImg, ENT_QUOTES, 'UTF-8') ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid #cbd5e1; flex-shrink:0;">
                                    <div style="min-width:0;">
                                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                            <strong style="font-size:15px; color:#184C74;"><?= htmlspecialchars($f['name']) ?></strong>
                                            <span style="font-size:11px; padding:2px 8px; border-radius:12px; background:#e0f2fe; color:#0369a1; font-weight:600;">
                                                <?= htmlspecialchars($f['coordinator_role']) ?>
                                            </span>
                                            <?php if (!empty($f['coordinator_order'])): ?>
                                                <span style="font-size:11px; padding:2px 6px; border-radius:4px; background:#f1f5f9; color:#475569;">
                                                    Order: <?= (int)$f['coordinator_order'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($f['coordinator_programme'])): ?>
                                            <div style="font-size:13px; color:#0f766e; font-weight:500; margin-top:2px;">
                                                <?= htmlspecialchars($f['coordinator_programme']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="font-size:12px; color:#64748b; margin-top:1px;">
                                            <?= htmlspecialchars($f['designation'] ?: $f['department']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditCoordinatorModal(<?= $fJson ?>)" style="padding:5px 12px; font-size:12px; background:#f1f5f9; border-color:#cbd5e1; color:#184C74; font-weight:600;">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCoordinator(<?= (int)$f['id'] ?>, '<?= addslashes(htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8')) ?>')" style="padding:5px 12px; font-size:12px;">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:30px 20px; background:#fff; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b;">
                            <p style="margin:0 0 10px; font-size:14px;">No coordinators assigned to this cluster yet.</p>
                            <button type="button" class="btn btn-sm btn-primary" onclick="openAddCoordinatorModal()" style="background:#184C74; border-color:#184C74;">
                                + Assign Coordinator
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="form-actions" style="margin-top: 35px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
                    Update Cluster
                </button>
                <a href="clusters.php" class="btn btn-secondary" style="padding:10px 20px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- COORDINATOR POPUP MODAL -->
<div id="coordinatorModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.6); backdrop-filter:blur(3px); align-items:center; justify-content:center; padding:15px; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; max-width:540px; width:100%; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1); overflow:hidden; margin:auto;">
        
        <!-- Modal Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 22px; background:#184C74; color:#fff;">
            <h4 id="coordModalTitle" style="margin:0; font-size:18px; color:#fff; font-weight:600;">Assign Coordinator</h4>
            <button type="button" onclick="closeCoordinatorModal()" style="background:transparent; border:none; color:#fff; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
        </div>

        <!-- Modal Body -->
        <form id="coordinatorModalForm" onsubmit="handleCoordinatorSubmit(event)" style="padding:22px; margin:0;">
            <input type="hidden" id="coord_faculty_id" value="">

            <!-- FACULTY SELECTION WITH IMAGE PREVIEW -->
            <div class="form-group" style="margin-bottom:16px; position:relative;">
                <label style="font-weight:600; color:#1e293b; display:block; margin-bottom:6px;">
                    Select Faculty Member <span style="color:#dc2626;">*</span>
                </label>

                <!-- Selected Faculty Preview Card (Shown once picked) -->
                <div id="selectedFacultyPreview" style="display:none; align-items:center; justify-content:space-between; padding:12px 14px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; margin-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                        <img id="previewFacultyImg" src="../images/member.png" style="width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid #22c55e; flex-shrink:0;">
                        <div style="min-width:0;">
                            <strong id="previewFacultyName" style="display:block; font-size:15px; color:#166534; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Prof. Name</strong>
                            <span id="previewFacultyDept" style="display:block; font-size:12px; color:#15803d; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Department</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="openFacultyDropdown()" style="padding:5px 12px; font-size:12px; background:#fff; border:1px solid #86efac; color:#166534; font-weight:600; border-radius:5px; flex-shrink:0;">
                        Change
                    </button>
                </div>

                <!-- Dropdown Trigger Button -->
                <button type="button" id="facultyDropdownTrigger" class="form-control" onclick="toggleFacultyDropdown()" style="display:flex; align-items:center; justify-content:space-between; text-align:left; background:#fff; height:auto; padding:10px 14px; cursor:pointer;">
                    <span id="triggerText" style="color:#64748b;">-- Click to Choose Faculty Member --</span>
                    <span style="font-size:12px; color:#64748b;">▼</span>
                </button>

                <!-- Dropdown Menu List -->
                <div id="facultyDropdownMenu" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1000; background:#fff; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 12px 28px rgba(0,0,0,0.18); margin-top:4px; max-height:280px; overflow-y:auto;">
                    <div style="padding:8px; position:sticky; top:0; background:#fff; border-bottom:1px solid #e2e8f0; z-index:2;">
                        <input type="text" id="filterFacultyInput" class="form-control" placeholder="Search faculty name or dept..." oninput="filterFacultyItems(this.value)" style="font-size:13px; border-radius:6px;">
                    </div>
                    <div id="facultyItemsList" style="padding:4px 0;">
                        <?php foreach ($allFacultyList as $fac): ?>
                            <?php $facImg = resolveFacultyThumb($fac['image']); ?>
                            <div class="faculty-picker-item" onclick="pickFaculty(<?= (int)$fac['id'] ?>, '<?= addslashes(htmlspecialchars($fac['name'])) ?>', '<?= addslashes(htmlspecialchars($fac['designation'] ?: $fac['department'])) ?>', '<?= addslashes(htmlspecialchars($facImg)) ?>')" style="display:flex; align-items:center; gap:12px; padding:8px 14px; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <img src="<?= htmlspecialchars($facImg) ?>" style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:1px solid #cbd5e1; flex-shrink:0;">
                                <div style="min-width:0; flex-grow:1;">
                                    <strong style="display:block; font-size:14px; color:#184C74; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($fac['name']) ?>
                                    </strong>
                                    <span style="display:block; font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($fac['designation'] ?: $fac['department']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- COORDINATOR ROLE & SETTINGS -->
            <div class="form-group" style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label style="font-weight:600; color:#1e293b; margin:0;">Coordinator Role <span style="color:#dc2626;">*</span></label>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="modalEnableCustomRole()" style="padding:2px 8px; font-size:11px; background:#e2e8f0; border-color:#cbd5e1; color:#184C74; font-weight:600;">
                        + Add Custom
                    </button>
                </div>
                <select id="modal_coordinator_role" class="form-control" onchange="modalRoleChange(this.value)">
                    <option value="IDDD Coordinator">IDDD Coordinator</option>
                    <option value="I2MP Coordinator">I2MP Coordinator</option>
                    <option value="Cluster Coordinator">Cluster Coordinator</option>
                    <option value="Coordinator">Coordinator</option>
                    <option value="Lead Coordinator">Lead Coordinator</option>
                    <option value="__custom__">+ Add Custom Role...</option>
                </select>
                <div id="modalCustomRoleWrap" style="display:none; margin-top:8px;">
                    <input type="text" id="modal_custom_role" class="form-control" placeholder="Type custom coordinator role (e.g. Joint Coordinator)...">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:600; color:#1e293b; display:block; margin-bottom:4px;">Coordinator Programme / Subtitle</label>
                <input type="text" id="modal_coordinator_programme" class="form-control" placeholder="e.g. <?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?>">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:3px;">Displayed under coordinator's role on the cluster inner page.</small>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="font-weight:600; color:#1e293b; display:block; margin-bottom:4px;">Display Order</label>
                <input type="number" id="modal_coordinator_order" class="form-control" value="1" min="0" style="max-width:120px;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:3px;">Controls order on cluster inner page (1, 2, 3...).</small>
            </div>

            <div id="modalAlert" style="display:none; padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:13px;"></div>

            <!-- Modal Actions -->
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeCoordinatorModal()" style="padding:8px 18px;">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn" class="btn btn-primary" style="padding:8px 22px; background:#184C74; border-color:#184C74;">
                    Save Coordinator
                </button>
            </div>
        </form>

    </div>
</div>

<script>
let sectionCounter = <?= count($sections) ?>;
const clusterId = <?= (int)$id ?>;
let clusterCoordinators = <?= json_encode($clusterCoordinators) ?>;

function togglePageType() {
    const isWebsite = document.getElementById('typeWebsite').checked;
    const websiteSec = document.getElementById('websiteSection');
    const innerSec = document.getElementById('innerPageSection');
    const extInput = document.getElementById('website_url');
    const innerInput = document.getElementById('inner_website_url');

    if (isWebsite) {
        websiteSec.style.display = 'block';
        innerSec.style.display = 'none';
        if (extInput && innerInput && !extInput.value && innerInput.value) {
            extInput.value = innerInput.value;
        }
        if (extInput) extInput.required = true;
    } else {
        websiteSec.style.display = 'none';
        innerSec.style.display = 'block';
        if (innerInput && extInput && !innerInput.value && extInput.value) {
            innerInput.value = extInput.value;
        }
        if (extInput) extInput.required = false;
    }
}

function addSection() {
    const container = document.getElementById('sectionsContainer');
    const idx = sectionCounter++;
    const card = document.createElement('div');
    card.className = 'section-card';
    card.setAttribute('data-index', idx);
    card.style = 'background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:20px; margin-bottom:20px; position:relative;';

    card.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h4 style="margin:0; font-size:16px; color:#1e293b;">Section #<span class="sec-num">${container.children.length + 1}</span></h4>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeSection(this)" style="padding:4px 10px; font-size:12px;">Remove</button>
        </div>

        <input type="hidden" name="sections[${idx}][existing_image]" class="sec-existing-img" value="">

        <div class="form-group">
            <label>Section Title</label>
            <input type="text" name="sections[${idx}][title]" class="form-control sec-title" placeholder="e.g. Research topics: or Thrust areas">
        </div>

        <div class="form-group">
            <label>Section Sub-Title (Optional)</label>
            <input type="text" name="sections[${idx}][subtitle]" class="form-control sec-subtitle" placeholder="Optional subtitle">
        </div>

        <div class="form-group">
            <label>Description / Paragraph</label>
            <textarea name="sections[${idx}][description]" class="form-control sec-desc" rows="3" placeholder="Enter paragraph text or overview..."></textarea>
        </div>

        <div class="form-group">
            <label>Bullet Points (One per line)</label>
            <textarea name="sections[${idx}][points]" class="form-control sec-points" rows="3" placeholder="Point 1&#10;Point 2&#10;Point 3"></textarea>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label>Section Image (Optional)</label>
            <input type="file" name="section_images[${idx}]" class="form-control sec-file-img" accept="image/*">
            <small class="form-text text-muted" style="color:#666; display:block; margin-top:4px;">
                If an image is attached, this section renders as a <strong>Left Content, Right Image</strong> layout with a <strong>#F5F5F5 background</strong>. If no image, it spans full width.
            </small>
        </div>
    `;

    container.appendChild(card);
    updateSectionNumbers();
}

function removeSection(button) {
    const card = button.closest('.section-card');
    const container = document.getElementById('sectionsContainer');
    if (container.children.length <= 1) {
        alert('At least one section is recommended for inner pages.');
        return;
    }
    card.remove();
    updateSectionNumbers();
}

function updateSectionNumbers() {
    const container = document.getElementById('sectionsContainer');
    Array.from(container.children).forEach((card, index) => {
        const numSpan = card.querySelector('.sec-num');
        if (numSpan) numSpan.textContent = index + 1;
    });
}

// DROPDOWN PICKER FUNCTIONS
function toggleFacultyDropdown() {
    const menu = document.getElementById('facultyDropdownMenu');
    if (menu.style.display === 'block') {
        closeFacultyDropdown();
    } else {
        openFacultyDropdown();
    }
}

function openFacultyDropdown() {
    const menu = document.getElementById('facultyDropdownMenu');
    menu.style.display = 'block';
    const filterInput = document.getElementById('filterFacultyInput');
    filterInput.value = '';
    filterFacultyItems('');
    setTimeout(() => filterInput.focus(), 50);
}

function closeFacultyDropdown() {
    const menu = document.getElementById('facultyDropdownMenu');
    if (menu) menu.style.display = 'none';
}

function filterFacultyItems(query) {
    const q = query.toLowerCase().trim();
    const items = document.querySelectorAll('.faculty-picker-item');
    items.forEach(el => {
        const text = el.textContent.toLowerCase();
        el.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function pickFaculty(id, name, dept, img) {
    document.getElementById('coord_faculty_id').value = id;
    
    // Show image preview & name
    document.getElementById('previewFacultyImg').src = img;
    document.getElementById('previewFacultyName').textContent = name;
    document.getElementById('previewFacultyDept').textContent = dept;
    document.getElementById('selectedFacultyPreview').style.display = 'flex';
    document.getElementById('facultyDropdownTrigger').style.display = 'none';

    // Dropdown closes immediately once picked!
    closeFacultyDropdown();
}

// COORDINATOR MODAL MANAGEMENT
function openAddCoordinatorModal() {
    document.getElementById('coordModalTitle').textContent = 'Assign Coordinator';
    document.getElementById('coord_faculty_id').value = '';
    
    // Reset picker
    document.getElementById('selectedFacultyPreview').style.display = 'none';
    document.getElementById('facultyDropdownTrigger').style.display = 'flex';
    closeFacultyDropdown();

    // Default coordinator fields
    document.getElementById('modal_coordinator_role').value = 'IDDD Coordinator';
    document.getElementById('modalCustomRoleWrap').style.display = 'none';
    document.getElementById('modal_custom_role').value = '';
    document.getElementById('modal_coordinator_programme').value = '<?= addslashes($cluster['cluster_name']) ?>';
    document.getElementById('modal_coordinator_order').value = clusterCoordinators.length + 1;
    
    hideModalAlert();
    document.getElementById('coordinatorModal').style.display = 'flex';
}

function openEditCoordinatorModal(coord) {
    document.getElementById('coordModalTitle').textContent = 'Edit Coordinator';
    document.getElementById('coord_faculty_id').value = coord.id;

    // Show image preview with name
    const fImg = resolveFacultyThumbJs(coord.image);
    document.getElementById('previewFacultyImg').src = fImg;
    document.getElementById('previewFacultyName').textContent = coord.name;
    document.getElementById('previewFacultyDept').textContent = coord.designation || coord.department || '';
    document.getElementById('selectedFacultyPreview').style.display = 'flex';
    document.getElementById('facultyDropdownTrigger').style.display = 'none';
    closeFacultyDropdown();

    // Set role
    const presetRoles = ['IDDD Coordinator', 'I2MP Coordinator', 'Cluster Coordinator', 'Coordinator', 'Lead Coordinator'];
    if (presetRoles.includes(coord.coordinator_role)) {
        document.getElementById('modal_coordinator_role').value = coord.coordinator_role;
        document.getElementById('modalCustomRoleWrap').style.display = 'none';
    } else {
        document.getElementById('modal_coordinator_role').value = '__custom__';
        document.getElementById('modalCustomRoleWrap').style.display = 'block';
        document.getElementById('modal_custom_role').value = coord.coordinator_role || '';
    }

    document.getElementById('modal_coordinator_programme').value = coord.coordinator_programme || '';
    document.getElementById('modal_coordinator_order').value = coord.coordinator_order || 1;

    hideModalAlert();
    document.getElementById('coordinatorModal').style.display = 'flex';
}

function closeCoordinatorModal() {
    document.getElementById('coordinatorModal').style.display = 'none';
    closeFacultyDropdown();
}

function modalRoleChange(val) {
    document.getElementById('modalCustomRoleWrap').style.display = (val === '__custom__') ? 'block' : 'none';
    if (val === '__custom__') {
        document.getElementById('modal_custom_role').focus();
    }
}

function modalEnableCustomRole() {
    document.getElementById('modal_coordinator_role').value = '__custom__';
    modalRoleChange('__custom__');
}

function showModalAlert(msg, isError = true) {
    const el = document.getElementById('modalAlert');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = isError ? '#fee2e2' : '#dcfce7';
    el.style.color = isError ? '#991b1b' : '#166534';
    el.style.border = isError ? '1px solid #f87171' : '1px solid #86efac';
}

function hideModalAlert() {
    document.getElementById('modalAlert').style.display = 'none';
}

async function handleCoordinatorSubmit(e) {
    e.preventDefault();
    hideModalAlert();

    const facId = document.getElementById('coord_faculty_id').value;
    if (!facId) {
        showModalAlert('Please select a faculty member.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'save_coordinator');
    formData.append('cluster_id', clusterId);
    formData.append('mode', 'existing');
    formData.append('faculty_id', facId);

    let role = document.getElementById('modal_coordinator_role').value;
    formData.append('coordinator_role', role);
    if (role === '__custom__') {
        formData.append('custom_coordinator_role', document.getElementById('modal_custom_role').value);
    }
    formData.append('coordinator_programme', document.getElementById('modal_coordinator_programme').value);
    formData.append('coordinator_order', document.getElementById('modal_coordinator_order').value);

    const btn = document.getElementById('modalSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    try {
        const resp = await fetch('ajax_coordinator.php', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();
        btn.disabled = false;
        btn.textContent = 'Save Coordinator';

        if (data.status === 'success') {
            clusterCoordinators = data.coordinators;
            renderCoordinatorsList(data.coordinators);
            closeCoordinatorModal();
        } else {
            showModalAlert(data.message || 'Error saving coordinator.');
        }
    } catch (err) {
        btn.disabled = false;
        btn.textContent = 'Save Coordinator';
        showModalAlert('Network or server error occurred.');
    }
}

async function removeCoordinator(facultyId, name) {
    if (!confirm('Remove "' + name + '" as coordinator for this cluster?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'remove_coordinator');
    formData.append('cluster_id', clusterId);
    formData.append('faculty_id', facultyId);

    try {
        const resp = await fetch('ajax_coordinator.php', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();
        if (data.status === 'success') {
            clusterCoordinators = data.coordinators;
            renderCoordinatorsList(data.coordinators);
        } else {
            alert(data.message || 'Error removing coordinator.');
        }
    } catch (err) {
        alert('Network or server error.');
    }
}

function resolveFacultyThumbJs(path) {
    if (!path) return '../images/member.png';
    path = path.trim();
    if (path.startsWith('images/')) return '../' + path;
    return '../images/faculty/' + path;
}

function renderCoordinatorsList(coords) {
    const container = document.getElementById('coordinatorsListContainer');
    if (!coords || coords.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:30px 20px; background:#fff; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b;">
                <p style="margin:0 0 10px; font-size:14px;">No coordinators assigned to this cluster yet.</p>
                <button type="button" class="btn btn-sm btn-primary" onclick="openAddCoordinatorModal()" style="background:#184C74; border-color:#184C74;">
                    + Assign Coordinator
                </button>
            </div>
        `;
        return;
    }

    let html = '';
    coords.forEach(f => {
        const fImg = resolveFacultyThumbJs(f.image);
        const fJson = JSON.stringify(f).replace(/"/g, '&quot;');
        const safeName = (f.name || '').replace(/'/g, "\\'");
        html += `
            <div class="coordinator-admin-card" style="display:flex; align-items:center; justify-content:space-between; gap:14px; padding:12px 16px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="display:flex; align-items:center; gap:14px; min-width:0; flex-grow:1;">
                    <img src="${fImg}" style="width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid #cbd5e1; flex-shrink:0;">
                    <div style="min-width:0;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <strong style="font-size:15px; color:#184C74;">${escapeHtml(f.name)}</strong>
                            <span style="font-size:11px; padding:2px 8px; border-radius:12px; background:#e0f2fe; color:#0369a1; font-weight:600;">
                                ${escapeHtml(f.coordinator_role || '')}
                            </span>
                            ${f.coordinator_order ? `<span style="font-size:11px; padding:2px 6px; border-radius:4px; background:#f1f5f9; color:#475569;">Order: ${f.coordinator_order}</span>` : ''}
                        </div>
                        ${f.coordinator_programme ? `<div style="font-size:13px; color:#0f766e; font-weight:500; margin-top:2px;">${escapeHtml(f.coordinator_programme)}</div>` : ''}
                        <div style="font-size:12px; color:#64748b; margin-top:1px;">
                            ${escapeHtml(f.designation || f.department || '')}
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="openEditCoordinatorModal(${fJson})" style="padding:5px 12px; font-size:12px; background:#f1f5f9; border-color:#cbd5e1; color:#184C74; font-weight:600;">
                        Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCoordinator(${f.id}, '${safeName}')" style="padding:5px 12px; font-size:12px;">
                        Remove
                    </button>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// Close dropdown if clicked outside
document.addEventListener('click', function(e) {
    const trigger = document.getElementById('facultyDropdownTrigger');
    const menu = document.getElementById('facultyDropdownMenu');
    const preview = document.getElementById('selectedFacultyPreview');
    if (menu && menu.style.display === 'block') {
        if (!menu.contains(e.target) && (!trigger || !trigger.contains(e.target)) && (!preview || !preview.contains(e.target))) {
            menu.style.display = 'none';
        }
    }
});

/* UTF-8-safe Base64 encoder */
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

/* Split into fixed-size chunks */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Sync a text/url/textarea field by ID into chunked hidden inputs */
function syncChunked(fieldId) {
    const visible    = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container  = document.getElementById(fieldId + '_chunks_container');

    if (!visible || !countInput || !container) return;

    const base64 = b64EncodeUtf8(visible.value || '');
    const chunks = splitIntoChunks(base64, 200);

    countInput.value = chunks.length;

    container.innerHTML = '';
    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = fieldId + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }
}

/* Sync arbitrary string into chunked hidden inputs */
function syncChunkedString(fieldName, str) {
    const countInput = document.getElementById(fieldName + '_chunk_count');
    const container  = document.getElementById(fieldName + '_chunks_container');

    if (!countInput || !container) return;

    const base64 = b64EncodeUtf8(str || '');
    const chunks = splitIntoChunks(base64, 200);

    countInput.value = chunks.length;

    container.innerHTML = '';
    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = fieldName + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }
}

/* Prepare chunked submit for clusters */
function prepareClusterSubmit() {
    const nameInput = document.getElementById('cluster_name');
    if (!nameInput || !nameInput.value.trim()) {
        alert('Please enter the cluster name.');
        if (nameInput) nameInput.focus();
        return false;
    }

    const isWebsite = document.getElementById('typeWebsite') && document.getElementById('typeWebsite').checked;
    const extInput = document.getElementById('external_website_url');
    if (isWebsite && extInput && !extInput.value.trim()) {
        alert('Please enter the Website URL for external redirect.');
        extInput.focus();
        return false;
    }

    // 1. Chunk cluster name
    syncChunked('cluster_name');
    nameInput.removeAttribute('name');

    // 2. Chunk URLs
    if (extInput) {
        syncChunked('external_website_url');
        extInput.removeAttribute('name');
    }
    const innerInput = document.getElementById('inner_website_url');
    if (innerInput) {
        syncChunked('inner_website_url');
        innerInput.removeAttribute('name');
    }

    // 3. Chunk sections (textareas, points, and text)
    const cards = document.querySelectorAll('#sectionsContainer .section-card');
    const sectionsData = [];
    cards.forEach((card, idx) => {
        const titleEl = card.querySelector('.sec-title');
        const subEl   = card.querySelector('.sec-subtitle');
        const descEl  = card.querySelector('.sec-desc');
        const ptsEl   = card.querySelector('.sec-points');
        const extEl   = card.querySelector('.sec-existing-img');
        const remEl   = card.querySelector('.sec-remove-img');
        const fileInp = card.querySelector('.sec-file-img');

        sectionsData.push({
            title:          titleEl ? titleEl.value : '',
            subtitle:       subEl   ? subEl.value   : '',
            description:    descEl  ? descEl.value  : '',
            points:         ptsEl   ? ptsEl.value   : '',
            existing_image: extEl   ? extEl.value   : '',
            remove_image:   (remEl && remEl.checked) ? '1' : ''
        });

        // Strip name attributes so raw big text is NOT sent in POST
        if (titleEl) titleEl.removeAttribute('name');
        if (subEl)   subEl.removeAttribute('name');
        if (descEl)  descEl.removeAttribute('name');
        if (ptsEl)   ptsEl.removeAttribute('name');
        if (extEl)   extEl.removeAttribute('name');
        if (remEl)   remEl.removeAttribute('name');

        // Ensure file upload indices align with sectionsData
        if (fileInp) {
            fileInp.name = 'section_images[' + idx + ']';
        }
    });

    syncChunkedString('sections', JSON.stringify(sectionsData));

    return true;
}
</script>

<?php include 'footer.php'; ?>