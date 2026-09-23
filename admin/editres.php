<?php
/*
|--------------------------------------------------------------------------
| BOOTSTRAP — process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once '../config.php';

$error = '';

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? $_POST['id'] ?? '';

if (empty($id) || !ctype_digit((string)$id)) {
    header('Location: research.php');
    exit;
}

$id = (int)$id;


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
| FETCH RESEARCH
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, title, link, image, status
    FROM featured_research
    WHERE id = ?
");
$stmt->execute([$id]);
$research = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$research) {
    header('Location: research.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CURRENT CONTENT TYPE
|--------------------------------------------------------------------------
*/

$currentContent = $research['link'] ?? '';
$currentType = 'link';

if (!empty($currentContent) && preg_match('/\.pdf$/i', $currentContent)) {
    $currentType = 'attachment';
}


/*
|--------------------------------------------------------------------------
| FORM VALUES (unique names, no clash with header.php)
|--------------------------------------------------------------------------
*/

$researchTitle = $research['title'];
$linkType      = $currentType;
$researchLink  = $currentType === 'link' ? $currentContent : '';
$status        = (int)$research['status'];


/*
|--------------------------------------------------------------------------
| HANDLE UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $researchTitle = reconstructChunks('title');
    $researchLink  = reconstructChunks('link');

    $linkType = $_POST['link_type'] ?? 'link';
    $status   = isset($_POST['status']) ? 1 : 0;


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($researchTitle)) {

        $error = "Please enter the research title.";

    } elseif (!in_array($linkType, ['link', 'attachment'], true)) {

        $error = "Invalid content type selected.";

    } elseif ($linkType === 'link') {

        if (empty($researchLink)) {
            $error = "Please enter the link.";
        } elseif (!filter_var($researchLink, FILTER_VALIDATE_URL)) {
            $error = "Please enter a valid URL.";
        } elseif (!preg_match('/^https?:\/\//i', $researchLink)) {
            $error = "URL must start with http:// or https://";
        }

    } elseif ($linkType === 'attachment') {

        $newAttachmentUploaded =
            isset($_FILES['attachment']) &&
            $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($newAttachmentUploaded) {

            if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
                $error = "There was an error uploading the PDF.";
            } else {
                $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
                $extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

                if ($fileType !== 'application/pdf' || $extension !== 'pdf') {
                    $error = "Only PDF files are allowed.";
                }
            }

        } elseif ($currentType !== 'attachment') {
            $error = "Please upload a PDF file.";
        }

    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE VALIDATION
    |--------------------------------------------------------------------------
    */

    $newImageUploaded =
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if (empty($error) && $newImageUploaded) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = "There was an error uploading the image.";

        } else {

            $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $imageType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($imageType, $allowedImageTypes, true)) {
                $error = "Only JPG, PNG or WEBP images are allowed.";
            } else {
                $imageInfo = @getimagesize($_FILES['image']['tmp_name']);

                if ($imageInfo === false) {
                    $error = "The uploaded image is not valid.";
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $error = "Image size must be less than 5MB.";
                }
            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS FILES
    |--------------------------------------------------------------------------
    */

    if (empty($error)) {

        $uploadDir = __DIR__ . '/../images/';

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        if (!is_writable($uploadDir)) {

            $error = "Upload directory is not writable. Please contact administrator.";
            error_log("Upload dir not writable: " . $uploadDir);

        } else {

            $imageName = $research['image'];


            /*
            |--------------------------------------------------------------------------
            | NEW IMAGE
            |--------------------------------------------------------------------------
            */

            if ($newImageUploaded) {

                $allowedImageMimes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $imageType      = mime_content_type($_FILES['image']['tmp_name']);
                $imageExtension = $allowedImageMimes[$imageType];
                $newImageName   = 'research_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageExtension;
                $imagePath      = $uploadDir . $newImageName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {

                    if (!empty($imageName)) {

                        $oldImagePath = $uploadDir . basename($imageName);

                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                        }

                    }

                    $imageName = $newImageName;

                } else {

                    $error = "Unable to upload the image.";
                    error_log("move_uploaded_file failed: " . $imagePath);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | CONTENT VALUE
            |--------------------------------------------------------------------------
            */

            if (empty($error)) {

                $contentValue = $currentContent;


                /*
                |--------------------------------------------------------------------------
                | LINK
                |--------------------------------------------------------------------------
                */

                if ($linkType === 'link') {

                    $contentValue = $researchLink;

                    if ($currentType === 'attachment') {

                        $oldAttachment = __DIR__ . '/../images/attachments/' . basename($currentContent);

                        if (file_exists($oldAttachment)) {
                            @unlink($oldAttachment);
                        }

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | ATTACHMENT
                |--------------------------------------------------------------------------
                */

                elseif ($linkType === 'attachment') {

                    $attachmentDir = __DIR__ . '/../images/attachments/';

                    if (!is_dir($attachmentDir)) {
                        @mkdir($attachmentDir, 0755, true);
                    }

                    if (!is_writable($attachmentDir)) {

                        if ($newImageUploaded && file_exists($imagePath)) @unlink($imagePath);
                        $error = "Attachment directory is not writable. Please contact administrator.";
                        error_log("Attachment dir not writable: " . $attachmentDir);

                    } else {

                        if ($newAttachmentUploaded) {

                            $newAttachmentName = 'research_' . time() . '_' . bin2hex(random_bytes(8)) . '.pdf';
                            $attachmentPath    = $attachmentDir . $newAttachmentName;

                            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $attachmentPath)) {

                                if ($currentType === 'attachment' && !empty($currentContent)) {

                                    $oldAttachment = $attachmentDir . basename($currentContent);

                                    if (file_exists($oldAttachment)) {
                                        @unlink($oldAttachment);
                                    }

                                }

                                $contentValue = $newAttachmentName;

                            } else {

                                if ($newImageUploaded && file_exists($imagePath)) @unlink($imagePath);
                                $error = "Unable to upload the PDF.";
                                error_log("move_uploaded_file failed: " . $attachmentPath);

                            }

                        }

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            if (empty($error)) {

                try {

                    $stmt = $pdo->prepare("
                        UPDATE featured_research
                        SET title = ?, link = ?, image = ?, status = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $researchTitle,
                        $contentValue,
                        $imageName,
                        $status,
                        $id
                    ]);

                    header('Location: research.php?success=updated');
                    exit;

                } catch (PDOException $e) {

                    error_log("DB Error: " . $e->getMessage());
                    $error = "Database error. Please try again.";

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| NOW SAFE TO OUTPUT HTML
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Edit Featured Research</h2>
            <p>Update the selected research item.</p>
        </div>
    </div>


    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>


    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3>Edit Research #<?= $id ?></h3>
                <p>Update the research details.</p>
            </div>
        </div>


        <form method="POST" enctype="multipart/form-data" id="researchForm" onsubmit="return prepareSubmit()">

            <input type="hidden" name="id" value="<?= $id ?>">


            <!-- TITLE (chunked Base64) -->
            <div class="form-group">
                <label for="title">Title</label>
                <input
                    type="text"
                    id="title"
                    class="form-control"
                    value="<?= htmlspecialchars($researchTitle, ENT_QUOTES, 'UTF-8') ?>"
                    oninput="syncChunked('title')"
                    required
                >
                <input type="hidden" id="title_chunk_count" name="title_chunk_count" value="0">
                <div id="title_chunks_container"></div>
            </div>


            <!-- CONTENT TYPE -->
            <div class="form-group">
                <label for="link_type">Content Type</label>
                <select
                    id="link_type"
                    name="link_type"
                    class="form-control"
                    onchange="toggleContentType()"
                >
                    <option value="link"       <?= $linkType === 'link' ? 'selected' : '' ?>>Link</option>
                    <option value="attachment" <?= $linkType === 'attachment' ? 'selected' : '' ?>>Attachment (PDF)</option>
                </select>
            </div>


            <!-- LINK (chunked Base64) -->
            <div class="form-group" id="linkField">
                <label for="link">Link</label>
                <input
                    type="text"
                    id="link"
                    class="form-control"
                    value="<?= htmlspecialchars($researchLink, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://example.com"
                    oninput="syncChunked('link')"
                >
                <input type="hidden" id="link_chunk_count" name="link_chunk_count" value="0">
                <div id="link_chunks_container"></div>
                <small class="form-help">Enter a full URL including http:// or https://</small>
            </div>


            <!-- CURRENT PDF -->
            <div class="form-group" id="currentAttachmentField" style="display:none;">
                <label>Current PDF</label>
                <?php if ($currentType === 'attachment'): ?>
                    <div>
                        <a
                            href="../images/attachments/<?= htmlspecialchars($currentContent, ENT_QUOTES, 'UTF-8') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="table-link"
                        >
                            View Current PDF
                        </a>
                    </div>
                <?php endif; ?>
            </div>


            <!-- CHANGE PDF -->
            <div class="form-group" id="attachmentField" style="display:none;">
                <label for="attachment">Change PDF</label>
                <input
                    type="file"
                    id="attachment"
                    name="attachment"
                    class="form-control"
                    accept=".pdf,application/pdf"
                >
                <small class="form-help">Leave empty to keep the current PDF. PDF files only.</small>
            </div>


            <!-- CURRENT IMAGE -->
            <div class="form-group">
                <label>Current Image</label>
                <?php if (!empty($research['image'])): ?>
                    <div style="margin-top:10px;">
                        <img
                            src="../images/<?= htmlspecialchars($research['image'], ENT_QUOTES, 'UTF-8') ?>?v=<?= time() ?>"
                            alt="<?= htmlspecialchars($researchTitle, ENT_QUOTES, 'UTF-8') ?>"
                            class="table-image"
                            style="width:180px;height:110px;"
                        >
                    </div>
                <?php else: ?>
                    <p>No image uploaded.</p>
                <?php endif; ?>
            </div>


            <!-- CHANGE IMAGE -->
            <div class="form-group">
                <label for="image">Change Image</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <small class="form-help">Leave empty to keep the current image. JPG, PNG or WEBP only. Max 5MB.</small>
            </div>


            <!-- STATUS -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input
                        type="checkbox"
                        name="status"
                        value="1"
                        <?= $status == 1 ? 'checked' : '' ?>
                    >
                    Active
                </label>
            </div>


            <!-- ACTIONS -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Featured Research</button>
                <a href="research.php" class="btn btn-secondary">Cancel</a>
            </div>

        </form>

    </div>

</div>


<script>

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

/* Sync a text field into chunked hidden inputs */
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

/* Ensure fields are encoded before submit */
function prepareSubmit() {
    syncChunked('title');
    syncChunked('link');
    return true;
}

/* Toggle between Link and PDF */
function toggleContentType() {

    const type = document.getElementById('link_type').value;

    const linkField              = document.getElementById('linkField');
    const attachmentField        = document.getElementById('attachmentField');
    const currentAttachmentField = document.getElementById('currentAttachmentField');

    const link = document.getElementById('link');

    if (type === 'attachment') {
        linkField.style.display = 'none';
        attachmentField.style.display = 'block';
        currentAttachmentField.style.display = 'block';
        link.removeAttribute('required');
    } else {
        linkField.style.display = 'block';
        attachmentField.style.display = 'none';
        currentAttachmentField.style.display = 'none';
        link.setAttribute('required', 'required');
    }

}

/* Initialize */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('title');
    syncChunked('link');
    toggleContentType();
});

</script>


<?php include 'footer.php'; ?>