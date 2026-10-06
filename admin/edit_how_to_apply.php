<?php

/*
|--------------------------------------------------------------------------
| BOOTSTRAP - process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$error = '';

/*
|--------------------------------------------------------------------------
| DECODE HELPERS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: how_to_apply.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH CURRENT RECORD
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("SELECT * FROM how_to_apply WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: how_to_apply.php');
        exit;
    }

} catch (PDOException $e) {

    header('Location: how_to_apply.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| INITIAL VALUES
|--------------------------------------------------------------------------
*/

$title         = $item['title'];
$image         = $item['image'];
$link_type     = $item['link_type'];
$link_url      = $item['link_url'] ?? '';
$attachment    = $item['attachment'] ?? '';
$display_order = (int)$item['display_order'];
$status        = (int)$item['status'];

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title         = reconstructChunks('title');
    $link_type     = $_POST['link_type'] ?? 'link';
    $link_url      = reconstructChunks('link_url');
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
    $status        = isset($_POST['status']) ? 1 : 0;

    /*
    | Basic Validation
    */
    if (empty($title)) {
        $error = "Please enter the card title.";
    } elseif (!in_array($link_type, ['link', 'attachment'], true)) {
        $error = "Invalid destination type selected.";
    } elseif ($link_type === 'link' && empty($link_url)) {
        $error = "Please enter the target link URL.";
    }

    /*
    | Attachment Validation (if switched or updated)
    */
    if (empty($error) && $link_type === 'attachment') {
        $hasNewAttachment = isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK;
        if (!$hasNewAttachment && empty($attachment)) {
            $error = "Please upload an attachment file.";
        }
    }

    /*
    | Optional Image Replacement Validation
    */
    $hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;
    if (empty($error) && $hasNewImage) {
        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $imageType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($imageType, $allowedImageMimes, true)) {
            $error = "Only JPG, PNG or WEBP images are allowed.";
        } elseif ($_FILES['image']['size'] > 10 * 1024 * 1024) {
            $error = "Image size must be less than 10MB.";
        }
    }

    /*
    | Save Files and Database Update
    */
    if (empty($error)) {

        $uploadDir = __DIR__ . '/../images/';
        $finalImageName = $image;

        if ($hasNewImage) {
            $allowedExtMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];
            $imageExtension = $allowedExtMap[$imageType] ?? 'png';
            $finalImageName = 'apply_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $imageExtension;
            $imagePath = $uploadDir . $finalImageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $error = "Unable to save new image.";
            }
        }

        $finalAttachmentName = ($link_type === 'attachment') ? $attachment : null;

        if (empty($error) && $link_type === 'attachment') {
            $hasNewAttachment = isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK;

            if ($hasNewAttachment) {
                $attachmentDir = __DIR__ . '/../images/attachments/';
                if (!is_dir($attachmentDir)) {
                    @mkdir($attachmentDir, 0755, true);
                }

                $origName = pathinfo($_FILES['attachment']['name'], PATHINFO_FILENAME);
                $origExt  = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $origName);
                $finalAttachmentName = $safeName . '_' . time() . '.' . $origExt;
                $attachmentPath = $attachmentDir . $finalAttachmentName;

                if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachmentPath)) {
                    $error = "Unable to save new attachment.";
                }
            }
        }

        if (empty($error)) {

            try {

                $updateSql = "
                    UPDATE how_to_apply
                    SET
                        title = ?,
                        image = ?,
                        link_type = ?,
                        link_url = ?,
                        attachment = ?,
                        display_order = ?,
                        status = ?
                    WHERE id = ?
                ";

                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    $title,
                    $finalImageName,
                    $link_type,
                    ($link_type === 'link') ? $link_url : null,
                    ($link_type === 'attachment') ? $finalAttachmentName : null,
                    $display_order,
                    $status,
                    $id
                ]);

                header('Location: how_to_apply.php?success=updated');
                exit;

            } catch (PDOException $e) {

                $error = "Database update failed: " . $e->getMessage();

            }

        }

    }

}

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">

    <div class="page-header">

        <div>
            <h1>Edit How to Apply Card</h1>
            <p>Update title, image, or target destination.</p>
        </div>

        <a href="how_to_apply.php" class="btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to Cards
        </a>

    </div>


    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3>Edit Card Details</h3>
                <p>Modify fields below and click Save Changes.</p>
            </div>
        </div>


        <div class="card-body" style="padding: 24px;">

            <form method="POST" action="" enctype="multipart/form-data" id="cardForm" onsubmit="return prepareSubmit()">


                <!-- Title -->

                <div class="form-group">

                    <label for="title">
                        Card Title <span class="required" style="color:red;">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        class="form-control"
                        value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                        oninput="syncChunked('title')"
                        required
                    >

                    <input type="hidden" id="title_chunk_count" name="title_chunk_count" value="0">
                    <div id="title_chunks_container"></div>

                </div>


                <!-- Card Image -->

                <div class="form-group">

                    <label for="image">
                        Card Image
                    </label>

                    <?php if (!empty($image)): ?>

                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 12px;">

                            <img
                                src="../images/<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Current Image"
                                style="width: 100px; height: 65px; object-fit: cover; border-radius: 4px; border: 1px solid #d1d5db;"
                            >

                            <small style="color: #6B7280;">Current image: <?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?> (upload below only to replace)</small>

                        </div>

                    <?php endif; ?>

                    <input
                        type="file"
                        name="image"
                        id="image"
                        class="form-control"
                        accept="image/png, image/jpeg, image/webp"
                    >

                </div>


                <!-- Destination Type -->

                <div class="form-group">

                    <label>
                        Action Destination <span class="required" style="color:red;">*</span>
                    </label>

                    <div style="display: flex; gap: 20px; align-items: center; margin-top: 6px;">

                        <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-weight: 500;">
                            <input
                                type="radio"
                                name="link_type"
                                value="link"
                                <?= ($link_type === 'link') ? 'checked' : '' ?>
                                onchange="toggleType(this.value)"
                            >
                            <span><i class="fa-solid fa-link"></i> External or Page Link URL</span>
                        </label>

                        <label style="display: inline-flex; align-items: center; gap: 7px; cursor: pointer; font-weight: 500;">
                            <input
                                type="radio"
                                name="link_type"
                                value="attachment"
                                <?= ($link_type === 'attachment') ? 'checked' : '' ?>
                                onchange="toggleType(this.value)"
                            >
                            <span><i class="fa-solid fa-paperclip"></i> File Attachment (PDF / Document)</span>
                        </label>

                    </div>

                </div>


                <!-- Link URL Input -->

                <div class="form-group" id="linkGroup" style="display: <?= ($link_type === 'link') ? 'block' : 'none' ?>;">

                    <label for="link_url">
                        Link URL <span class="required" style="color:red;">*</span>
                    </label>

                    <input
                        type="text"
                        id="link_url"
                        class="form-control"
                        value="<?= htmlspecialchars($link_url, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="e.g. clusters.php or https://example.com"
                        oninput="syncChunked('link_url')"
                    >

                    <input type="hidden" id="link_url_chunk_count" name="link_url_chunk_count" value="0">
                    <div id="link_url_chunks_container"></div>

                </div>


                <!-- Attachment Input -->

                <div class="form-group" id="attachmentGroup" style="display: <?= ($link_type === 'attachment') ? 'block' : 'none' ?>;">

                    <label for="attachment">
                        File Attachment
                    </label>

                    <?php if (!empty($attachment)): ?>

                        <div style="margin-bottom: 8px;">

                            <a
                                href="../images/attachments/<?= htmlspecialchars($attachment, ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank"
                                style="color: #184C74; font-size: 13px; text-decoration: underline; display: inline-flex; align-items: center; gap: 5px;"
                            >
                                <i class="fa-solid fa-file-pdf" style="color: #DC2626;"></i>
                                Current file: <?= htmlspecialchars($attachment, ENT_QUOTES, 'UTF-8') ?>
                            </a>

                        </div>

                    <?php endif; ?>

                    <input
                        type="file"
                        name="attachment"
                        id="attachment"
                        class="form-control"
                    >

                    <small style="color: #6B7280; display: block; margin-top: 4px;">
                        Upload a new file only if you wish to replace the current attachment in <code>images/attachments/</code>.
                    </small>

                </div>


                <!-- Display Order -->

                <div class="form-group">

                    <label for="display_order">
                        Display Order
                    </label>

                    <input
                        type="number"
                        name="display_order"
                        id="display_order"
                        class="form-control"
                        value="<?= (int)$display_order ?>"
                        style="max-width: 200px;"
                    >

                </div>


                <!-- Status -->

                <div class="form-group" style="margin-top: 15px; margin-bottom: 20px;">

                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">

                        <input
                            type="checkbox"
                            name="status"
                            id="status"
                            value="1"
                            <?= $status ? 'checked' : '' ?>
                            style="width: 18px; height: 18px; cursor: pointer;"
                        >

                        <span>Active (Visible on public website)</span>

                    </label>

                </div>


                <!-- Form Actions -->

                <div class="form-actions" style="margin-top: 25px; display: flex; gap: 12px;">

                    <button
                        type="submit"
                        class="btn-add"
                        style="border: none; cursor: pointer;"
                    >
                        Save Changes
                    </button>

                    <a
                        href="how_to_apply.php"
                        class="btn-delete"
                        style="text-decoration: none; display: inline-flex; align-items: center;"
                    >
                        Cancel
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


<script>

function b64EncodeUtf8(str) {
    try {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
            return String.fromCharCode('0x' + p1);
        }));
    } catch (e) {
        return btoa(str);
    }
}

function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

function syncChunked(fieldId) {

    const visible    = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container  = document.getElementById(fieldId + '_chunks_container');

    if (!visible || !countInput || !container) return;

    const base64 = b64EncodeUtf8(visible.value);
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

function prepareSubmit() {
    syncChunked('title');
    syncChunked('link_url');
    return true;
}

function toggleType(type) {

    const linkGroup       = document.getElementById('linkGroup');
    const attachmentGroup = document.getElementById('attachmentGroup');

    if (type === 'attachment') {
        linkGroup.style.display       = 'none';
        attachmentGroup.style.display = 'block';
    } else {
        linkGroup.style.display       = 'block';
        attachmentGroup.style.display = 'none';
    }

}

document.addEventListener('DOMContentLoaded', function () {
    syncChunked('title');
    syncChunked('link_url');
});

</script>


<?php include 'footer.php'; ?>
