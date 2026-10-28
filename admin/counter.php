<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

/*
|--------------------------------------------------------------------------
| ENSURE SETTINGS TABLE EXISTS (Self-healing migration)
|--------------------------------------------------------------------------
*/
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `counter_settings` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `bg_image` VARCHAR(255) NOT NULL DEFAULT 'counter.png',
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $exists = $pdo->query("SELECT COUNT(*) FROM `counter_settings` WHERE `id` = 1")->fetchColumn();
    if ($exists == 0) {
        $pdo->exec("INSERT INTO `counter_settings` (`id`, `bg_image`) VALUES (1, 'counter.png')");
    }
} catch (Exception $e) {
    // Continue gracefully
}

/*
|--------------------------------------------------------------------------
| HANDLE DRAG-AND-DROP REORDER AJAX
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder_counters') {
    header('Content-Type: application/json');
    $order = $_POST['order'] ?? [];

    if (!is_array($order) || empty($order)) {
        echo json_encode(['success' => false, 'error' => 'No order data received']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `counter` SET `display_order` = :ord WHERE `id` = :id");
        foreach ($order as $index => $counterId) {
            $stmt->execute([
                ':ord' => $index + 1,
                ':id'  => (int)$counterId
            ]);
        }
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| LIVE TEMPLATE QUERIES
|--------------------------------------------------------------------------
*/
$templateQueries = [
    'core_faculty'  => "SELECT COUNT(*) FROM `faculty` WHERE `status` = 1 AND `top_order` = 1",
    'total_faculty' => "SELECT COUNT(*) FROM `faculty` WHERE `status` = 1",
    'faculty'       => "SELECT COUNT(*) FROM `faculty` WHERE `status` = 1",
    'staff'         => "SELECT COUNT(*) FROM `staff`",
    'mtech'         => "SELECT COUNT(*) FROM `mtech_students`",
    'ms'            => "SELECT COUNT(*) FROM `ms`",
    'phd'           => "SELECT COUNT(*) FROM `phd_scholars`",
    'clusters'      => "SELECT COUNT(*) FROM `clusters` WHERE `status` = 1",
];

/*
|--------------------------------------------------------------------------
| HANDLE QUICK-ADD PREDEFINED TEMPLATE
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_add_template') {
    $currentCount = (int)$pdo->query("SELECT COUNT(*) FROM `counter`")->fetchColumn();
    if ($currentCount >= 10) {
        header('Location: counter.php?error=limit_reached');
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $tKey  = trim($_POST['template_key'] ?? '');

    $val = '0';
    if (!empty($tKey) && isset($templateQueries[$tKey])) {
        try {
            $val = (string)(int)$pdo->query($templateQueries[$tKey])->fetchColumn();
        } catch (Exception $e) {
            $val = '0';
        }
    } elseif (isset($_POST['value'])) {
        $val = trim($_POST['value']);
    }

    if (!empty($title)) {
        $maxOrder = (int)$pdo->query("SELECT MAX(display_order) FROM `counter`")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO `counter` (`cheading`, `cvalue`, `template_key`, `display_order`, `status`) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$title, $val, !empty($tKey) ? $tKey : null, $maxOrder + 1]);
        header('Location: counter.php?success=added');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| HANDLE BACKGROUND IMAGE UPLOAD
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_bg_image') {
    if (!isset($_FILES['bg_image']) || $_FILES['bg_image']['error'] === UPLOAD_ERR_NO_FILE) {
        header('Location: counter.php?error=no_file');
        exit;
    }

    if ($_FILES['bg_image']['error'] !== UPLOAD_ERR_OK) {
        header('Location: counter.php?error=upload_failed');
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($_FILES['bg_image']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        header('Location: counter.php?error=invalid_type');
        exit;
    }

    $imageInfo = @getimagesize($_FILES['bg_image']['tmp_name']);
    if ($imageInfo === false) {
        header('Location: counter.php?error=invalid_image');
        exit;
    }

    if ($_FILES['bg_image']['size'] > 8 * 1024 * 1024) {
        header('Location: counter.php?error=file_too_large');
        exit;
    }

    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    $extension = $extensionMap[$fileType] ?? 'png';
    $newImageName = 'counter_bg_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $uploadDir = __DIR__ . '/../images/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $newImageName;

    if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $uploadPath)) {
        // Fetch current image to remove old custom upload
        $curStmt = $pdo->query("SELECT bg_image FROM `counter_settings` WHERE `id` = 1");
        $oldImage = $curStmt ? $curStmt->fetchColumn() : null;

        if ($oldImage && $oldImage !== 'counter.png' && file_exists($uploadDir . $oldImage)) {
            @unlink($uploadDir . $oldImage);
        }

        $stmt = $pdo->prepare("
            INSERT INTO `counter_settings` (`id`, `bg_image`)
            VALUES (1, :img)
            ON DUPLICATE KEY UPDATE `bg_image` = :img
        ");
        $stmt->execute([':img' => $newImageName]);

        header('Location: counter.php?success=bg_updated');
        exit;
    } else {
        header('Location: counter.php?error=move_failed');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| HANDLE BACKGROUND IMAGE RESET TO DEFAULT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_bg_image') {
    $uploadDir = __DIR__ . '/../images/';
    $curStmt = $pdo->query("SELECT bg_image FROM `counter_settings` WHERE `id` = 1");
    $oldImage = $curStmt ? $curStmt->fetchColumn() : null;

    if ($oldImage && $oldImage !== 'counter.png' && file_exists($uploadDir . $oldImage)) {
        @unlink($uploadDir . $oldImage);
    }

    $pdo->query("UPDATE `counter_settings` SET `bg_image` = 'counter.png' WHERE `id` = 1");
    header('Location: counter.php?success=bg_reset');
    exit;
}

/*
|--------------------------------------------------------------------------
| HANDLE QUICK TOGGLE STATUS
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $targetStatus = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE `counter` SET `status` = :s WHERE `id` = :id");
        $stmt->execute([':s' => $targetStatus, ':id' => $id]);
        header('Location: counter.php?success=status_updated');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| FETCH COUNTER BACKGROUND IMAGE
|--------------------------------------------------------------------------
*/
$currentBgImage = 'counter.png';
try {
    $bgStmt = $pdo->query("SELECT bg_image FROM `counter_settings` WHERE `id` = 1 LIMIT 1");
    if ($bgStmt) {
        $row = $bgStmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['bg_image']) && file_exists(__DIR__ . '/../images/' . $row['bg_image'])) {
            $currentBgImage = $row['bg_image'];
        }
    }
} catch (Exception $e) {
    $currentBgImage = 'counter.png';
}
$isDefaultBg = ($currentBgImage === 'counter.png');

/*
|--------------------------------------------------------------------------
| FETCH ALL COUNTERS
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT
        id,
        cheading,
        cvalue,
        template_key,
        display_order,
        status,
        created_at
    FROM `counter`
    ORDER BY display_order ASC, id ASC
");
$stmt->execute();
$counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCount = count($counters);
$maxLimit = 10;
$canAdd = $totalCount < $maxLimit;

/*
|--------------------------------------------------------------------------
| PREDEFINED LIVE SYSTEM TEMPLATES
|--------------------------------------------------------------------------
*/
$predefinedTemplates = [
    [
        'key'        => 'core_faculty',
        'title'      => 'Core Faculty',
        'alt_titles' => ['core faculty'],
        'query'      => $templateQueries['core_faculty'],
    ],
    [
        'key'        => 'faculty',
        'title'      => 'Faculty',
        'alt_titles' => ['faculty', 'all faculty', 'total faculty'],
        'query'      => $templateQueries['faculty'],
    ],
    [
        'key'        => 'staff',
        'title'      => 'Staff',
        'alt_titles' => ['staff'],
        'query'      => $templateQueries['staff'],
    ],
    [
        'key'        => 'mtech',
        'title'      => 'MTech Students',
        'alt_titles' => ['mtech students', 'm.tech students', 'mtech'],
        'query'      => $templateQueries['mtech'],
    ],
    [
        'key'        => 'ms',
        'title'      => 'M.S. Research Scholars',
        'alt_titles' => ['m.s. research scholars', 'ms research scholars', 'ms students', 'm.s. students'],
        'query'      => $templateQueries['ms'],
    ],
    [
        'key'        => 'phd',
        'title'      => 'Ph.D. Research Scholars',
        'alt_titles' => ['ph.d. research scholars', 'phd research scholars', 'ph.d. scholars', 'phd scholars'],
        'query'      => $templateQueries['phd'],
    ],
    [
        'key'        => 'clusters',
        'title'      => 'Clusters',
        'alt_titles' => ['clusters', 'interdisciplinary clusters', 'research clusters'],
        'query'      => $templateQueries['clusters'],
    ],
];

/*
|--------------------------------------------------------------------------
| AUTO-SYNC: Automatically sync all template-linked counters with live DB
|--------------------------------------------------------------------------
*/
foreach ($counters as &$c) {
    $tKey = $c['template_key'] ?? '';

    // ONLY auto-sync if explicitly marked as template (never overwrite manual counters)
    if (!empty($tKey) && isset($templateQueries[$tKey])) {
        try {
            $liveVal = (string)(int)$pdo->query($templateQueries[$tKey])->fetchColumn();
            if ((string)$c['cvalue'] !== $liveVal) {
                $c['cvalue'] = $liveVal;
                $pdo->prepare("UPDATE `counter` SET `cvalue` = ? WHERE `id` = ?")->execute([$liveVal, $c['id']]);
            }
        } catch (Exception $e) {}
    }
}
unset($c);

foreach ($predefinedTemplates as &$tpl) {
    try {
        $tpl['live_count'] = (int)$pdo->query($tpl['query'])->fetchColumn();
    } catch (Exception $e) {
        $tpl['live_count'] = 0;
    }

    $tpl['matching_counter'] = null;
    foreach ($counters as $cnt) {
        // ONLY match if template_key is explicitly set (manual counters do not match)
        if (!empty($cnt['template_key']) && ($cnt['template_key'] === $tpl['key'] || ($tpl['key'] === 'faculty' && $cnt['template_key'] === 'total_faculty'))) {
            $tpl['matching_counter'] = $cnt;
            break;
        }
    }
}
unset($tpl);

include 'header.php';
?>

<style>
/* Drag and Drop styling */
.sortable-row {
    transition: background-color 0.15s ease;
}
.sortable-row.dragging {
    opacity: 0.45;
    background-color: #e0f2fe !important;
}
.sortable-row.drag-over {
    border-top: 3px solid #184C74 !important;
}
.drag-handle {
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
}
.drag-handle:active {
    cursor: grabbing;
}
.order-badge {
    display: inline-block;
    font-weight: 700;
    color: #184C74;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    transition: all 0.2s;
}

/* Toast alert notification */
#orderToast {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #0f172a;
    color: #ffffff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    transform: translateY(120px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border-left: 4px solid #10b981;
}
#orderToast.show {
    transform: translateY(0);
    opacity: 1;
}

/* Banner chip styling & drag */
.banner-chip {
    text-align: center;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(2px);
    border-radius: 6px;
    padding: 6px 10px 8px 10px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    min-width: 90px;
    max-width: 140px;
    flex: 1 1 calc(20% - 12px);
    box-sizing: border-box;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, opacity 0.2s ease, border-color 0.2s ease;
}
.banner-chip:hover {
    background: rgba(0, 0, 0, 0.55);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}
.banner-chip:active {
    cursor: grabbing;
}
.banner-chip.chip-dragging {
    opacity: 0.35;
    transform: scale(0.92);
    border: 1px dashed #38bdf8 !important;
}
.banner-chip.chip-drag-over {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.7) !important;
    transform: scale(1.06) translateY(-3px);
}
.banner-chip.inactive {
    opacity: 0.45;
    border-style: dashed;
}
</style>

<div class="dashboard-content">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2>Key Statistics / Counter</h2>
            <p>
                Manage statistics and background banner displayed on the homepage counter section. Drag rows to arrange order. Max 10 counters allowed.
            </p>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:13px; font-weight:600; padding:6px 14px; border-radius:20px; background:<?= $canAdd ? '#e0f2fe' : '#fef3c7' ?>; color:<?= $canAdd ? '#0369a1' : '#92400e' ?>; border:1px solid <?= $canAdd ? '#bae6fd' : '#fde68a' ?>;">
                <?= $totalCount ?> / <?= $maxLimit ?> Counters Added
            </span>

            <?php if ($canAdd): ?>
                <a href="addcounter.php" class="btn-add">
                    + Add Counter
                </a>
            <?php else: ?>
                <button type="button" class="btn-add" disabled style="opacity:0.6; cursor:not-allowed; background:#94a3b8;" title="Maximum limit of 10 counters reached. Delete or edit an existing counter.">
                    Limit Reached (Max 10)
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'added'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter added successfully.</div>
        <?php elseif ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter updated successfully.</div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter deleted successfully.</div>
        <?php elseif ($_GET['success'] === 'status_updated'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter status updated successfully.</div>
        <?php elseif ($_GET['success'] === 'bg_updated'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <strong>Success!</strong> Counter section background banner image updated successfully.</div>
        <?php elseif ($_GET['success'] === 'bg_reset'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter background banner image reset to default (counter.png).</div>
        <?php elseif ($_GET['success'] === 'synced'): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Counter value synced with live system database count successfully.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'limit_reached'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Maximum limit of 10 counters has already been reached. Please delete an existing counter to add a new one.</div>
        <?php elseif ($_GET['error'] === 'no_file'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Please select an image file to upload.</div>
        <?php elseif ($_GET['error'] === 'invalid_type'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Invalid file type! Only JPG, PNG, and WEBP images are allowed.</div>
        <?php elseif ($_GET['error'] === 'invalid_image'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> The uploaded file is not a valid image.</div>
        <?php elseif ($_GET['error'] === 'file_too_large'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> File is too large! Maximum image file size is 8MB.</div>
        <?php elseif ($_GET['error'] === 'upload_failed' || $_GET['error'] === 'move_failed'): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> An error occurred while uploading the image. Please verify folder permissions.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- BANNER IMAGE SETTINGS CARD -->
    <div class="dashboard-card" style="margin-bottom: 25px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div class="card-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="color: #184C74; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-image"></i> Counter Section Banner / Background Image
                </h3>
                <p style="margin: 4px 0 0 0; color: #64748b; font-size: 13px;">
                    Displaying all <?= $totalCount ?> counters in order over the background image. Drag rows in the table below to reorder.
                </p>
            </div>
            <div>
                <?php if ($isDefaultBg): ?>
                    <span style="display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; color:#475569; font-size:12px; font-weight:600; padding:5px 12px; border-radius:15px; border:1px solid #cbd5e1;">
                        <i class="fa-solid fa-circle" style="font-size:8px; color:#94a3b8;"></i> Default Image (counter.png)
                    </span>
                <?php else: ?>
                    <span style="display:inline-flex; align-items:center; gap:5px; background:#dcfce7; color:#15803d; font-size:12px; font-weight:600; padding:5px 12px; border-radius:15px; border:1px solid #bbf7d0;">
                        <i class="fa-solid fa-circle" style="font-size:8px; color:#22c55e;"></i> Custom Image Active
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(320px, 1.4fr) minmax(280px, 1fr); gap: 24px; align-items: start;">
            
            <!-- LEFT: LIVE IMAGE PREVIEW WITH ALL COUNTERS IN DB ORDER -->
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; margin:0;">
                        Live Banner Preview (Ordered by Database):
                    </label>
                    <span style="font-size: 11px; color: #0284c7; background: #e0f2fe; padding: 3px 10px; border-radius: 12px; font-weight: 600;">
                        <i class="fa-solid fa-hand-pointer"></i> Drag chips to reorder
                    </span>
                </div>
                
                <div style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; background: #0f172a; min-height: 240px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Background image container -->
                    <div id="banner-preview-box" style="
                        width: 100%;
                        min-height: 240px;
                        background-image: url('../images/<?= htmlspecialchars($currentBgImage, ENT_QUOTES, 'UTF-8') ?>');
                        background-size: cover;
                        background-position: center bottom;
                        background-repeat: no-repeat;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 24px 16px;
                        box-sizing: border-box;
                        transition: all 0.3s ease;
                    ">
                        <!-- All counters rendered according to DB display_order -->
                        <div id="banner-counters-grid" style="
                            display: flex;
                            gap: 10px;
                            justify-content: center;
                            width: 100%;
                            flex-wrap: wrap;
                        ">
                            <?php if (!empty($counters)): ?>
                                <?php foreach ($counters as $c): ?>
                                    <div class="banner-chip <?= (int)$c['status'] === 0 ? 'inactive' : '' ?>" draggable="true" data-id="<?= (int)$c['id'] ?>" title="Click and drag to rearrange order">
                                        <div style="font-size: 10px; color: rgba(255,255,255,0.45); margin-bottom: 2px;">
                                            <i class="fa-solid fa-grip-lines"></i>
                                        </div>
                                        <div class="chip-val" style="font-size: 20px; font-weight: 700; color: #fff; font-family: 'Lato', sans-serif; line-height: 1.1;">
                                            <?= htmlspecialchars($c['cvalue'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="chip-title" style="font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.25; margin-top: 3px;">
                                            <?= htmlspecialchars($c['cheading'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <?php if ((int)$c['status'] === 0): ?>
                                            <span style="font-size: 9px; color: #fecaca; background: rgba(185,28,28,0.75); padding: 1px 5px; border-radius: 3px; display: inline-block; margin-top: 3px; text-transform: uppercase;">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="color:#ffffff; font-size:13px; text-align:center; padding:20px;">
                                    No counters added yet. Click "+ Add Counter" to add items.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Watermark info -->
                    <div style="background: rgba(15, 23, 42, 0.88); color: #e2e8f0; padding: 7px 14px; font-size: 11px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-regular fa-file-image"></i> images/<?= htmlspecialchars($currentBgImage, ENT_QUOTES, 'UTF-8') ?></span>
                        <a href="../images/<?= htmlspecialchars($currentBgImage, ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="color: #38bdf8; text-decoration: none;">
                            Open Full Image &#8599;
                        </a>
                    </div>
                </div>

                <p style="margin: 6px 0 0 0; font-size: 12px; color: #64748b;">
                    <i class="fa-solid fa-circle-info"></i> All <?= $totalCount ?> counters from your database appear above over the banner in their exact live sequence.
                </p>
            </div>

            <!-- RIGHT: UPLOAD NEW IMAGE FORM -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px;">
                <form method="POST" action="counter.php" enctype="multipart/form-data" id="upload-bg-form">
                    <input type="hidden" name="action" value="update_bg_image">

                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                        Upload New Background Banner:
                    </label>

                    <div style="
                        border: 2px dashed #cbd5e1;
                        border-radius: 8px;
                        padding: 16px;
                        text-align: center;
                        background: #ffffff;
                        cursor: pointer;
                        margin-bottom: 12px;
                        transition: border-color 0.2s;
                    " onclick="document.getElementById('bg_image_input').click();">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 28px; color: #184C74; margin-bottom: 6px; display: block;"></i>
                        <span id="file-chosen-text" style="font-size: 13px; font-weight: 600; color: #334155; display: block;">
                            Click to select an image from your computer
                        </span>
                        <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 3px;">
                            PNG, JPG or WEBP (Max 8MB)
                        </span>
                        <input 
                            type="file" 
                            name="bg_image" 
                            id="bg_image_input" 
                            accept="image/png,image/jpeg,image/webp" 
                            style="display: none;" 
                            required
                            onchange="handleImageSelection(this)"
                        >
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <button type="submit" class="btn-add" style="background: #184C74; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-size: 13px;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Apply Image
                        </button>

                        <?php if (!$isDefaultBg): ?>
                            </form>
                            <form method="POST" action="counter.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to reset the background to the default counter.png?');">
                                <input type="hidden" name="action" value="reset_bg_image">
                                <button type="submit" class="btn-delete" style="background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; font-size: 13px;">
                                    <i class="fa-solid fa-rotate-left"></i> Reset to Default
                                </button>
                            </form>
                        <?php else: ?>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; line-height: 1.5;">
                        <strong>Recommendation:</strong> Use a high-resolution, wide panorama banner (e.g. 1920&times;500 px). Dark or vibrant nature/campus images work best with the white counter typography.
                    </div>
            </div>

        </div>
    </div>

    <!-- PREDEFINED LIVE SYSTEM TEMPLATES (CLEAN MINIMAL BAR) -->
    <div class="dashboard-card" style="margin-bottom: 25px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div class="card-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px;">
            <div>
                <h3 style="color: #184C74; font-size: 16px; margin: 0; font-weight: 700;">
                    Live System Templates
                </h3>
                <p style="margin: 3px 0 0 0; color: #64748b; font-size: 13px;">
                    Predefined counts from database. Added items auto-sync with live system data.
                </p>
            </div>
            <div>
                <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:<?= $canAdd ? '#ecfdf5' : '#fef2f2' ?>; color:<?= $canAdd ? '#047857' : '#b91c1c' ?>; border:1px solid <?= $canAdd ? '#a7f3d0' : '#fecaca' ?>;">
                    <?= $canAdd ? (10 - $totalCount) . ' Slots Available' : 'Max 10 Limit Reached' ?>
                </span>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <?php foreach ($predefinedTemplates as $tpl): ?>
                <?php
                $isAdded = ($tpl['matching_counter'] !== null);
                ?>
                <div style="background: #ffffff; border: 1px solid <?= $isAdded ? '#cbd5e1' : '#cbd5e1' ?>; border-radius: 6px; padding: 7px 12px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <span style="font-size: 13px; font-weight: 600; color: #1e293b;">
                        <?= htmlspecialchars($tpl['title']) ?>
                    </span>
                    <span style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 13px; padding: 2px 8px; border-radius: 4px; font-family: 'Lato', sans-serif;">
                        <?= (int)$tpl['live_count'] ?>
                    </span>

                    <?php if ($isAdded): ?>
                        <span style="font-size: 12px; color: #059669; font-weight: 600; display: inline-flex; align-items: center; gap: 3px;">
                            <i class="fa-solid fa-check" style="font-size: 11px;"></i> Added
                        </span>
                    <?php else: ?>
                        <?php if ($canAdd): ?>
                            <form method="POST" action="counter.php" style="margin: 0;">
                                <input type="hidden" name="action" value="quick_add_template">
                                <input type="hidden" name="template_key" value="<?= htmlspecialchars($tpl['key']) ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($tpl['title']) ?>">
                                <button type="submit" style="background: #184C74; color: #ffffff; border: none; border-radius: 4px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-plus" style="font-size: 10px;"></i> Add
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" disabled style="background: #e2e8f0; color: #94a3b8; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: not-allowed;">
                                Limit
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- TABLE CARD WITH DRAG-AND-DROP REORDER -->
    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3 style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-list-ol" style="color:#184C74;"></i> All Counters (<?= $totalCount ?>)
                </h3>
                <p>
                    <i class="fa-solid fa-hand-pointer"></i> <strong>Drag and drop rows</strong> using the grip icon to arrange order. Changes save automatically!
                </p>
            </div>
       
        </div>

        <?php if (!empty($counters)): ?>

            <div class="table-wrapper">
                <table class="data-table" id="countersTable">
                    <thead>
                        <tr>
                            <th style="width: 48px; text-align: center;" title="Drag to reorder">
                                <i class="fa-solid fa-grip-vertical" style="color:#94a3b8;"></i>
                            </th>
                            <th style="width: 80px;">Order</th>
                            <th>Counter Value (Count)</th>
                            <th>Counter Name (Title)</th>
                            <th style="width: 130px;">Type</th>
                            <th style="width: 130px; text-align: center;">Status</th>
                            <th style="width: 170px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="countersTbody">
                        <?php foreach ($counters as $c): ?>
                            <tr class="sortable-row" draggable="true" data-id="<?= (int)$c['id'] ?>">
                                
                                <!-- DRAG HANDLE -->
                                <td class="drag-handle" style="text-align: center; padding: 12px 6px;" title="Drag row to rearrange order">
                                    <i class="fa-solid fa-grip-vertical" style="color:#94a3b8; font-size:16px;"></i>
                                </td>

                                <!-- ORDER BADGE -->
                                <td>
                                    <span class="order-badge">
                                        #<?= (int)$c['display_order'] ?>
                                    </span>
                                </td>

                                <!-- VALUE -->
                                <td>
                                    <strong style="font-size: 20px; color: #184C74; font-family: 'Lato', sans-serif;">
                                        <?= htmlspecialchars($c['cvalue'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                </td>

                                <!-- NAME -->
                                <td>
                                    <span style="font-size: 15px; font-weight: 600; color: #334155;">
                                        <?= htmlspecialchars($c['cheading'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <!-- TYPE (TEMPLATE OR MANUAL) -->
                                <td>
                                    <?php if (!empty($c['template_key'])): ?>
                                        <span style="display:inline-flex; align-items:center; gap:5px; background:#e0f2fe; color:#0369a1; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; border:1px solid #bae6fd;" title="Auto-synced with live database">
                                            Template
                                        </span>
                                    <?php else: ?>
                                        <span style="display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; border:1px solid #e2e8f0;" title="Manually entered counter">
                                            Manual
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- STATUS TOGGLE -->
                                <td style="text-align: center;">
                                    <form method="POST" action="counter.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <input type="hidden" name="status" value="<?= (int)$c['status'] === 1 ? 0 : 1 ?>">
                                        <?php if ((int)$c['status'] === 1): ?>
                                            <button type="submit" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; padding:5px 12px; border-radius:4px; font-size:12px; font-weight:600; cursor:pointer;" title="Click to hide this counter">
                                                <i class="fa-solid fa-check"></i> Active
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; padding:5px 12px; border-radius:4px; font-size:12px; font-weight:600; cursor:pointer;" title="Click to make active on homepage">
                                                <i class="fa-solid fa-eye-slash"></i> Inactive
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>

                                <!-- ACTIONS -->
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="editcounter.php?id=<?= (int)$c['id'] ?>" class="btn-edit" title="Edit value and label">
                                            Edit
                                        </a>

                                        <form method="POST" action="deletecounter.php" onsubmit="return confirm('Are you sure you want to delete this counter?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                            <button type="submit" class="btn-delete">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="table-empty">
                <p>No counters found. Add your first counter to display on the homepage.</p>
                <a href="addcounter.php" class="btn-add">Add Counter</a>
            </div>

        <?php endif; ?>

    </div>

</div>

<!-- TOAST ALERT -->
<div id="orderToast">
    <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 18px;"></i>
    <span id="orderToastMsg">Order updated and saved successfully!</span>
</div>

<script>
// Image file select preview
function handleImageSelection(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileName = file.name;
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        document.getElementById('file-chosen-text').innerHTML = 
            '<strong style="color:#184C74;">' + fileName + '</strong> (' + fileSizeMB + ' MB)';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewBox = document.getElementById('banner-preview-box');
            if (previewBox) {
                previewBox.style.backgroundImage = 'url(' + e.target.result + ')';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Toast helper
function showToast(message, isError) {
    const toast = document.getElementById('orderToast');
    const msg = document.getElementById('orderToastMsg');
    if (!toast || !msg) return;

    msg.textContent = message;
    toast.style.borderLeftColor = isError ? '#ef4444' : '#10b981';
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2800);
}

// Drag and drop reordering
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('countersTbody');
    const previewGrid = document.getElementById('banner-counters-grid');

    // Central function to apply order, sync UI, and persist to DB
    function applyOrderAndSave(orderedIds, source) {
        // 1. Sync table if triggered from banner
        if (source === 'banner' && tbody) {
            orderedIds.forEach((id, idx) => {
                const row = tbody.querySelector(`.sortable-row[data-id="${id}"]`);
                if (row) {
                    tbody.appendChild(row);
                    const badge = row.querySelector('.order-badge');
                    if (badge) badge.textContent = '#' + (idx + 1);
                }
            });
        }

        // 2. Sync banner if triggered from table
        if (source === 'table' && previewGrid) {
            orderedIds.forEach(id => {
                const chip = previewGrid.querySelector(`.banner-chip[data-id="${id}"]`);
                if (chip) {
                    previewGrid.appendChild(chip);
                }
            });
        }

        // 3. Ensure table badges are strictly #1..#N
        if (tbody) {
            tbody.querySelectorAll('.sortable-row').forEach((r, idx) => {
                const badge = r.querySelector('.order-badge');
                if (badge) badge.textContent = '#' + (idx + 1);
            });
        }

        // 4. Send AJAX request
        const formData = new FormData();
        formData.append('action', 'reorder_counters');
        newOrder = orderedIds;
        newOrder.forEach(id => formData.append('order[]', id));

        fetch('counter.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Counter order updated and saved successfully!', false);
            } else {
                showToast('Error saving order: ' + (data.error || 'Unknown error'), true);
            }
        })
        .catch(err => {
            showToast('Failed to save order: ' + err.message, true);
        });
    }

    // --- TABLE DRAG & DROP ---
    if (tbody) {
        let draggedRow = null;

        tbody.addEventListener('dragstart', function(e) {
            const row = e.target.closest('.sortable-row');
            if (!row) return;
            draggedRow = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.id);
        });

        tbody.addEventListener('dragend', function() {
            if (draggedRow) draggedRow.classList.remove('dragging');
            tbody.querySelectorAll('.sortable-row').forEach(r => r.classList.remove('drag-over'));
            draggedRow = null;
        });

        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const targetRow = e.target.closest('.sortable-row');
            if (targetRow && draggedRow && draggedRow !== targetRow) {
                const bounding = targetRow.getBoundingClientRect();
                const offset = e.clientY - bounding.top;
                if (offset > bounding.height / 2) {
                    targetRow.after(draggedRow);
                } else {
                    targetRow.before(draggedRow);
                }
            }
        });

        tbody.addEventListener('dragenter', function(e) {
            const targetRow = e.target.closest('.sortable-row');
            if (targetRow && draggedRow && draggedRow !== targetRow) {
                targetRow.classList.add('drag-over');
            }
        });

        tbody.addEventListener('dragleave', function(e) {
            const targetRow = e.target.closest('.sortable-row');
            if (targetRow) {
                targetRow.classList.remove('drag-over');
            }
        });

        tbody.addEventListener('drop', function(e) {
            e.preventDefault();
            tbody.querySelectorAll('.sortable-row').forEach(r => r.classList.remove('drag-over'));
            if (draggedRow) {
                const orderedIds = Array.from(tbody.querySelectorAll('.sortable-row')).map(r => r.dataset.id);
                applyOrderAndSave(orderedIds, 'table');
            }
        });
    }

    // --- BANNER PREVIEW CHIPS DRAG & DROP ---
    if (previewGrid) {
        let draggedChip = null;

        previewGrid.addEventListener('dragstart', function(e) {
            const chip = e.target.closest('.banner-chip');
            if (!chip) return;
            draggedChip = chip;
            chip.classList.add('chip-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', chip.dataset.id);
        });

        previewGrid.addEventListener('dragend', function() {
            if (draggedChip) draggedChip.classList.remove('chip-dragging');
            previewGrid.querySelectorAll('.banner-chip').forEach(c => c.classList.remove('chip-drag-over'));
            draggedChip = null;
        });

        previewGrid.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const targetChip = e.target.closest('.banner-chip');
            if (targetChip && draggedChip && draggedChip !== targetChip) {
                const rect = targetChip.getBoundingClientRect();
                const midX = rect.left + rect.width / 2;
                if (e.clientX > midX) {
                    targetChip.after(draggedChip);
                } else {
                    targetChip.before(draggedChip);
                }
            }
        });

        previewGrid.addEventListener('dragenter', function(e) {
            const targetChip = e.target.closest('.banner-chip');
            if (targetChip && draggedChip !== targetChip) {
                targetChip.classList.add('chip-drag-over');
            }
        });

        previewGrid.addEventListener('dragleave', function(e) {
            const targetChip = e.target.closest('.banner-chip');
            if (targetChip) {
                targetChip.classList.remove('chip-drag-over');
            }
        });

        previewGrid.addEventListener('drop', function(e) {
            e.preventDefault();
            previewGrid.querySelectorAll('.banner-chip').forEach(c => c.classList.remove('chip-drag-over'));
            if (draggedChip) {
                const orderedIds = Array.from(previewGrid.querySelectorAll('.banner-chip')).map(c => c.dataset.id);
                applyOrderAndSave(orderedIds, 'banner');
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
