<?php
/*
|--------------------------------------------------------------------------
| Admin > Facilities > Central Facilities > Add / Edit equipment
|--------------------------------------------------------------------------
| Text fields: chunked Base64. Image: normal file upload (JPG/PNG/WEBP, 5 MB).
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/account_helpers.php';

$imgDir = __DIR__ . '/../images/facilities/';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error  = '';

$docDir = __DIR__ . '/../images/facilities/docs/';

$form = ['name' => '', 'description' => '', 'image' => '', 'status' => 1, 'link_type' => 'none', 'link_url' => '', 'pdf_file' => ''];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id, name, description, image, status, link_type, link_url, pdf_file FROM facilities WHERE id = ? AND category = 'central' LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: central_facilities.php');
        exit;
    }
    $form = $row;
}


/*
|--------------------------------------------------------------------------
| SAVE
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['name']        = reconstructChunks('name');
    $form['description'] = reconstructChunks('description');
    $form['status']      = (($_POST['status'] ?? '1') === '0') ? 0 : 1;
    $oldPdf              = (string)$form['pdf_file'];
    $form['link_type']   = in_array($_POST['link_type'] ?? 'none', ['none', 'url', 'pdf'], true) ? $_POST['link_type'] : 'none';
    $form['link_url']    = $form['link_type'] === 'url' ? reconstructChunks('link_url') : '';

    if (!accountCsrfValid()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($form['name'] === '') {
        $error = 'Please enter the equipment name.';
    } elseif (mb_strlen($form['name']) > 255) {
        $error = 'Equipment name must be 255 characters or fewer.';
    }

    /* Optional new image */
    $newImage = '';
    $hasUpload = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($error === '' && $hasUpload) {
        $file = $_FILES['image'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed. Please try again.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (!isset($allowed[$mime])) {
                $error = 'Only JPG, PNG and WEBP images are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Image size must not exceed 5 MB.';
            } elseif (@getimagesize($file['tmp_name']) === false) {
                $error = 'The uploaded file is not a valid image.';
            } else {
                if (!is_dir($imgDir)) @mkdir($imgDir, 0755, true);
                $newImage = 'facility_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($file['tmp_name'], $imgDir . $newImage)) {
                    $error = 'Unable to save the image. Check folder permissions.';
                    $newImage = '';
                }
            }
        }
    }

    if ($error === '' && $id === 0 && $newImage === '') {
        $error = 'Please choose an image.';
    }

    /* Title link: external URL */
    if ($error === '' && $form['link_type'] === 'url') {
        if ($form['link_url'] === '' || !preg_match('#^https?://#i', $form['link_url']) || !filter_var($form['link_url'], FILTER_VALIDATE_URL)) {
            $error = 'Please enter a valid link starting with http:// or https://';
        } elseif (mb_strlen($form['link_url']) > 500) {
            $error = 'The link must be 500 characters or fewer.';
        }
    }

    /* Title link: PDF (new upload, or keep the existing one) */
    $newPdf = '';
    $hasPdfUpload = isset($_FILES['pdf']) && $_FILES['pdf']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($error === '' && $form['link_type'] === 'pdf') {
        if ($hasPdfUpload) {
            $pf = $_FILES['pdf'];
            $head = $pf['error'] === UPLOAD_ERR_OK ? (string)file_get_contents($pf['tmp_name'], false, null, 0, 5) : '';
            $pmime = $pf['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($pf['tmp_name']) : '';

            if ($pf['error'] !== UPLOAD_ERR_OK) {
                $error = 'PDF upload failed. Please try again.';
            } elseif ($pmime !== 'application/pdf' || $head !== '%PDF-') {
                $error = 'Only PDF files are allowed for the document.';
            } elseif ($pf['size'] > 10 * 1024 * 1024) {
                $error = 'The PDF must not exceed 10 MB.';
            } else {
                if (!is_dir($docDir)) @mkdir($docDir, 0755, true);
                $newPdf = 'facility_doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
                if (!move_uploaded_file($pf['tmp_name'], $docDir . $newPdf)) {
                    $error = 'Unable to save the PDF. Check folder permissions.';
                    $newPdf = '';
                }
            }
        } elseif ($oldPdf === '' || !is_file($docDir . basename($oldPdf))) {
            $error = 'Please choose a PDF file.';
        }
    }

    $form['pdf_file'] = $form['link_type'] === 'pdf' ? ($newPdf !== '' ? $newPdf : $oldPdf) : '';

    /* Remove a PDF that is no longer used (replaced or link type changed) */
    $cleanupOldPdf = function () use ($pdo, $docDir, $oldPdf, $form) {
        if ($oldPdf === '' || $oldPdf === $form['pdf_file']) return;
        $chk = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE pdf_file = ?");
        $chk->execute([$oldPdf]);
        if ((int)$chk->fetchColumn() === 0 && is_file($docDir . basename($oldPdf))) {
            @unlink($docDir . basename($oldPdf));
        }
    };

    if ($error === '') {
        try {
            if ($id > 0) {
                $oldImage = $form['image'];
                $image = $newImage !== '' ? $newImage : $oldImage;

                $pdo->prepare("UPDATE facilities SET name = ?, description = ?, image = ?, status = ?, link_type = ?, link_url = ?, pdf_file = ? WHERE id = ?")
                    ->execute([$form['name'], $form['description'], $image, $form['status'], $form['link_type'], $form['link_url'], $form['pdf_file'], $id]);
                $cleanupOldPdf();

                // remove the replaced image if nothing else uses it
                if ($newImage !== '' && $oldImage !== '' && $oldImage !== $newImage) {
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE image = ?");
                    $chk->execute([$oldImage]);
                    if ((int)$chk->fetchColumn() === 0 && is_file($imgDir . basename($oldImage))) {
                        @unlink($imgDir . basename($oldImage));
                    }
                }
                header('Location: central_facilities.php?success=updated');
            } else {
                $next = (int)$pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 FROM facilities WHERE category = 'central'")->fetchColumn();
                $pdo->prepare("INSERT INTO facilities (category, name, description, image, link_type, link_url, pdf_file, display_order, status) VALUES ('central', ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$form['name'], $form['description'], $newImage, $form['link_type'], $form['link_url'], $form['pdf_file'], $next, $form['status']]);
                header('Location: central_facilities.php?success=added');
            }
            exit;
        } catch (PDOException $e) {
            error_log('Facility save error: ' . $e->getMessage());
            $error = 'Unable to save. Please try again.';
            if ($newImage !== '' && is_file($imgDir . $newImage)) @unlink($imgDir . $newImage);
            if ($newPdf !== '' && is_file($docDir . $newPdf)) @unlink($docDir . $newPdf);
        }
    } else {
        // validation failed after an upload: drop the orphan files
        if ($newImage !== '' && is_file($imgDir . $newImage)) @unlink($imgDir . $newImage);
        if (!empty($newPdf) && is_file($docDir . $newPdf)) @unlink($docDir . $newPdf);
    }
    if ($error !== '') {
        $form['pdf_file'] = $oldPdf;   // show what is really saved
    }
}

$csrf = accountCsrfToken();
$h = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
$navActivePage = 'central_facilities.php';
$currentImg = trim((string)$form['image']);
$hasCurrentImg = $currentImg !== '' && is_file($imgDir . $currentImg);

include 'header.php';
?>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h1><?= $id ? 'Edit Equipment' : 'Add Equipment' ?></h1>
            <p>Shown on the Facilities &gt; Central Facilities page.</p>
        </div>
        <a href="central_facilities.php" class="btn-add"><i class="fas fa-arrow-left"></i> Back to Central Facilities</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= $h($error) ?></div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-body">
            <form method="POST" action="facility_edit.php<?= $id ? '?id=' . $id : '' ?>" enctype="multipart/form-data" onsubmit="return prepareSubmit()">
                <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">

                <div class="form-group">
                    <label for="name">Equipment Name <span class="required">*</span></label>
                    <input type="text" id="name" class="form-control" maxlength="255" required
                        value="<?= $h($form['name']) ?>" placeholder="e.g. Scanning Electron Microscope">
                    <input type="hidden" id="name_chunk_count" name="name_chunk_count" value="0">
                    <div id="name_chunks_container"></div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" class="form-control" rows="7"
                        placeholder="What it is used for, key specifications, booking / contact details. Leave a blank line between paragraphs."><?= $h($form['description']) ?></textarea>
                    <input type="hidden" id="description_chunk_count" name="description_chunk_count" value="0">
                    <div id="description_chunks_container"></div>
                </div>

                <div class="form-group">
                    <label for="image">Image <?= $id ? '' : '<span class="required">*</span>' ?></label>
                    <?php if ($hasCurrentImg): ?>
                        <div style="margin-bottom:10px;">
                            <img src="../images/facilities/<?= $h($currentImg) ?>" alt="" style="width:220px; aspect-ratio:16/10; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" <?= $id ? '' : 'required' ?>>
                    <small style="color:#64748b;">JPG, PNG or WEBP, max 5 MB. Landscape works best (16:10).<?= $id ? ' Leave empty to keep the current image.' : '' ?></small>
                </div>

                <div class="form-group">
                    <label>When the title is clicked</label>
                    <div class="fl-choice">
                        <label><input type="radio" name="link_type" value="none" <?= $form['link_type'] === 'none' ? 'checked' : '' ?> onchange="toggleLink()"> <span>Nothing (plain title)</span></label>
                        <label><input type="radio" name="link_type" value="url" <?= $form['link_type'] === 'url' ? 'checked' : '' ?> onchange="toggleLink()"> <span><i class="fas fa-arrow-up-right-from-square"></i> Open external link</span></label>
                        <label><input type="radio" name="link_type" value="pdf" <?= $form['link_type'] === 'pdf' ? 'checked' : '' ?> onchange="toggleLink()"> <span><i class="fas fa-file-pdf"></i> Open PDF</span></label>
                    </div>
                </div>

                <div class="form-group" id="linkUrlGroup">
                    <label for="link_url">External link <span class="required">*</span></label>
                    <input type="text" id="link_url" class="form-control" maxlength="500"
                        value="<?= $h($form['link_url']) ?>" placeholder="https://...">
                    <input type="hidden" id="link_url_chunk_count" name="link_url_chunk_count" value="0">
                    <div id="link_url_chunks_container"></div>
                    <small style="color:#64748b;">Opens in a new tab.</small>
                </div>

                <div class="form-group" id="linkPdfGroup">
                    <label for="pdf">PDF document <span class="required">*</span></label>
                    <?php $hasPdf = $form['pdf_file'] !== '' && is_file($docDir . basename($form['pdf_file'])); ?>
                    <?php if ($hasPdf): ?>
                        <div style="margin-bottom:8px; font-size:13px;">
                            <i class="fas fa-file-pdf" style="color:#dc2626;"></i>
                            Current: <a href="../images/facilities/docs/<?= $h($form['pdf_file']) ?>" target="_blank" rel="noopener">view PDF</a>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="pdf" name="pdf" class="form-control" accept="application/pdf,.pdf">
                    <small style="color:#64748b;">PDF only, max 10 MB. Opens in a new tab.<?= $hasPdf ? ' Leave empty to keep the current PDF.' : '' ?></small>
                </div>

                <style>
                    .fl-choice { display: flex; gap: 10px; flex-wrap: wrap; }
                    .fl-choice label {
                        display: flex; align-items: center; gap: 8px; margin: 0;
                        border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 14px;
                        cursor: pointer; font-weight: 500; font-size: 14px;
                    }
                    .fl-choice label:has(input:checked) { border-color: #184C74; background: #eff5fb; color: #184C74; }
                </style>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" <?= (int)$form['status'] === 1 ? 'selected' : '' ?>>Active (shown on website)</option>
                        <option value="0" <?= (int)$form['status'] === 0 ? 'selected' : '' ?>>Inactive (hidden)</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-confirm-delete" style="background:#184C74;">
                        <i class="fas fa-save"></i> <?= $id ? 'Update Equipment' : 'Save Equipment' ?>
                    </button>
                    <a href="central_facilities.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function b64EncodeUtf8(str) { return btoa(unescape(encodeURIComponent(str))); }

function syncChunked(fieldId) {
    var visible = document.getElementById(fieldId);
    var count = document.getElementById(fieldId + '_chunk_count');
    var box = document.getElementById(fieldId + '_chunks_container');
    var enc = visible.value === '' ? '' : b64EncodeUtf8(visible.value);
    var n = Math.ceil(enc.length / 200);
    count.value = n;
    box.innerHTML = '';
    for (var i = 0; i < n; i++) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = fieldId + '_chunk_' + i;
        inp.value = enc.substring(i * 200, (i + 1) * 200);
        box.appendChild(inp);
    }
}

function prepareSubmit() {
    syncChunked('name');
    syncChunked('description');
    syncChunked('link_url');
    return true;
}

/* Show the URL field or the PDF upload depending on the choice */
function toggleLink() {
    var type = (document.querySelector('input[name="link_type"]:checked') || {}).value || 'none';
    document.getElementById('linkUrlGroup').style.display = type === 'url' ? 'block' : 'none';
    document.getElementById('linkPdfGroup').style.display = type === 'pdf' ? 'block' : 'none';
    document.getElementById('link_url').required = type === 'url';
}
toggleLink();
</script>

<?php include 'footer.php'; ?>
