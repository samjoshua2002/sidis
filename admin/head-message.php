<?php include 'header.php'; ?>

<?php

require_once '../config.php';

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| DECODE HELPER — Base64 or plain fallback
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

/*
|--------------------------------------------------------------------------
| RECONSTRUCT CHUNKED BASE64 — for long text like Message
|--------------------------------------------------------------------------
*/

function reconstructChunks($fieldName) {
    $countField = $fieldName . '_chunk_count';

    // If no chunks, fall back to a regular field
    if (!isset($_POST[$countField]) || (int)$_POST[$countField] === 0) {

        // Try regular encoded field
        if (isset($_POST[$fieldName . '_encoded'])) {
            return decodeInput($_POST[$fieldName . '_encoded']);
        }

        // Try plain field
        if (isset($_POST[$fieldName])) {
            return trim($_POST[$fieldName]);
        }

        return '';
    }

    $chunkCount = (int)$_POST[$countField];
    $reconstructed = '';

    for ($i = 0; $i < $chunkCount; $i++) {
        $chunkName = $fieldName . '_chunk_' . $i;
        if (isset($_POST[$chunkName])) {
            $reconstructed .= $_POST[$chunkName];
        }
    }

    // Decode the full Base64 string
    return decodeInput($reconstructed);
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? '';

    // Short fields — regular Base64
    $name        = decodeInput($_POST['name_encoded']        ?? '');
    $designation = decodeInput($_POST['designation_encoded'] ?? '');

    // Long field — chunked Base64
    $description = reconstructChunks('description');

    if (
        empty($id) ||
        empty($name) ||
        empty($designation) ||
        empty($description)
    ) {

        $error = "Please fill in all the required fields.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | GET CURRENT IMAGE
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("SELECT image FROM head_message WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {

            $error = "Head message record not found.";

        } else {

            $imageName = $current['image'];

            /*
            |--------------------------------------------------------------------------
            | IMAGE UPLOAD (unchanged)
            |--------------------------------------------------------------------------
            */

            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                    $error = "There was an error uploading the image.";

                } else {

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    $fileType = mime_content_type($_FILES['image']['tmp_name']);

                    if (!in_array($fileType, $allowedTypes)) {

                        $error = "Only JPG, PNG or WEBP images are allowed.";

                    } else {

                        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);

                        if ($imageInfo === false) {

                            $error = "The file is not a valid image.";

                        } else {

                            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {

                                $error = "Image size must be less than 5MB.";

                            } else {

                                $extensionMap = [
                                    'image/jpeg' => 'jpg',
                                    'image/png'  => 'png',
                                    'image/webp' => 'webp'
                                ];

                                $extension = $extensionMap[$fileType];

                                $newImageName = 'head_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                                $uploadDir = __DIR__ . '/../images/';

                                if (!is_dir($uploadDir)) {
                                    @mkdir($uploadDir, 0755, true);
                                }

                                if (!is_writable($uploadDir)) {

                                    $error = "Upload directory is not writable. Please contact administrator.";
                                    error_log("Upload directory not writable: " . $uploadDir);

                                } else {

                                    $uploadPath = $uploadDir . $newImageName;

                                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {

                                        if (!empty($imageName)) {

                                            $oldImage = $uploadDir . basename($imageName);

                                            if (file_exists($oldImage)) {
                                                @unlink($oldImage);
                                            }
                                        }

                                        $imageName = $newImageName;

                                    } else {

                                        $error = "Unable to upload the image.";
                                        error_log("move_uploaded_file failed: " . $uploadPath);
                                    }
                                }
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
                        UPDATE head_message
                        SET
                            name = ?,
                            designation = ?,
                            description = ?,
                            image = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $name,
                        $designation,
                        $description,
                        $imageName,
                        $id
                    ]);

                    $success = "Head message updated successfully.";

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
| FETCH LATEST HEAD MESSAGE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, name, designation, description, image
    FROM head_message
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute();
$headMessage = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Message from Head, SIDiS</h2>
            <p>Manage the Head's message, details and profile image.</p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3>Head's Message</h3>
                <p>Update the Head's details, message and profile image displayed on the website.</p>
            </div>
        </div>

        <?php if ($headMessage): ?>

            <form method="POST" enctype="multipart/form-data" id="headForm" onsubmit="return prepareSubmit()">

                <input type="hidden" name="id" value="<?= htmlspecialchars($headMessage['id']) ?>">

                <!-- Short fields — plain Base64 -->
                <input type="hidden" name="name_encoded"        id="name_encoded"        value="">
                <input type="hidden" name="designation_encoded" id="designation_encoded" value="">

                <!-- Long field — chunked Base64 -->
                <input type="hidden" name="description_chunk_count" id="description_chunk_count" value="0">
                <div id="description_chunks_container"></div>

                <div class="head-message-grid">

                    <!-- IMAGE -->
                    <div class="image-section">

                        <label>Current Image</label>

                        <div class="current-image">
                            <?php if (!empty($headMessage['image'])): ?>
                                <img
                                    src="../images/<?= htmlspecialchars($headMessage['image']) ?>?v=<?= time() ?>"
                                    alt="<?= htmlspecialchars($headMessage['name']) ?>"
                                    id="currentHeadImage"
                                >
                            <?php else: ?>
                                <div class="no-image">No image uploaded</div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="image">Change Image</label>
                            <input
                                type="file"
                                id="image"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                                onchange="previewImage(event)"
                            >
                            <small class="form-help">JPG, PNG or WEBP only. Max 5MB.</small>
                            <p id="image-error" style="color:red; margin-top:8px;"></p>
                        </div>

                    </div>

                    <!-- DETAILS + MESSAGE -->
                    <div class="description-section">

                        <!-- NAME -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input
                                type="text"
                                id="name"
                                class="form-control"
                                value="<?= htmlspecialchars($headMessage['name']) ?>"
                                oninput="syncField('name', 'name_encoded')"
                                required
                            >
                        </div>

                        <!-- DESIGNATION -->
                        <div class="form-group">
                            <label for="designation">Designation</label>
                            <input
                                type="text"
                                id="designation"
                                class="form-control"
                                value="<?= htmlspecialchars($headMessage['designation']) ?>"
                                oninput="syncField('designation', 'designation_encoded')"
                                required
                            >
                        </div>

                        <!-- MESSAGE -->
                        <div class="form-group">
                            <label for="description">Message</label>
                            <textarea
                                id="description"
                                class="form-control"
                                rows="12"
                                oninput="syncChunkedField('description')"
                                required
                            ><?= htmlspecialchars($headMessage['description']) ?></textarea>
                            <small class="form-help">
                                Long messages are automatically split into safe chunks.
                            </small>
                        </div>

                        <!-- CURRENT DETAILS -->
                        <div class="person-details">
                            <strong><?= htmlspecialchars($headMessage['name']) ?></strong>
                            <span><?= htmlspecialchars($headMessage['designation']) ?></span>
                        </div>

                        <!-- UPDATE -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                Update Head Message
                            </button>
                        </div>

                    </div>

                </div>

            </form>

        <?php else: ?>
            <p>No Head message found.</p>
        <?php endif; ?>

    </div>

</div>


<script>
// UTF-8-safe Base64 encoder
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

// Sync a short text field to its Base64 hidden field
function syncField(visibleId, hiddenId) {
    const visible = document.getElementById(visibleId);
    const hidden  = document.getElementById(hiddenId);

    if (visible && hidden) {
        hidden.value = b64EncodeUtf8(visible.value);
    }
}

// Split a long string into chunks
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

// Sync the long description field into chunked hidden inputs
function syncChunkedField(fieldId) {
    const textarea  = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container  = document.getElementById(fieldId + '_chunks_container');

    if (!textarea || !countInput || !container) return;

    // Encode to Base64
    const base64 = b64EncodeUtf8(textarea.value);

    // Split into 200-char chunks (WAF-safe)
    const chunks = splitIntoChunks(base64, 200);

    // Update chunk count
    countInput.value = chunks.length;

    // Rebuild hidden inputs
    container.innerHTML = '';
    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = fieldId + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }
}

// Ensure everything is synced before submit
function prepareSubmit() {
    syncField('name',        'name_encoded');
    syncField('designation', 'designation_encoded');
    syncChunkedField('description');
    return true;
}

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
    syncField('name',        'name_encoded');
    syncField('designation', 'designation_encoded');
    syncChunkedField('description');
});

// Image preview + validation
function previewImage(event) {
    const file     = event.target.files[0];
    const errorMsg = document.getElementById('image-error');
    const preview  = document.getElementById('currentHeadImage');

    errorMsg.textContent = '';

    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        errorMsg.textContent = 'Only JPG, PNG or WEBP images are allowed.';
        event.target.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        errorMsg.textContent = 'Image size must be less than 5MB.';
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        if (preview) {
            preview.src = e.target.result;
        } else {
            const wrapper = document.querySelector('.current-image');
            if (wrapper) {
                wrapper.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="currentHeadImage">';
            }
        }
    };
    reader.readAsDataURL(file);
}
</script>


<?php include 'footer.php'; ?>