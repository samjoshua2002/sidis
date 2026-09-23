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

    // Try Base64 decode (strict)
    $decoded = base64_decode($value, true);

    if ($decoded !== false && $decoded !== '') {
        return trim($decoded);
    }

    // Fallback: return as plain text
    return trim($value);
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id       = $_POST['id'] ?? '';
    $title    = decodeInput($_POST['title_encoded']    ?? '');
    $subtitle = decodeInput($_POST['subtitle_encoded'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($id) || empty($title) || empty($subtitle)) {

        $error = "Please fill in all the required fields.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | GET CURRENT HERO
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT image
            FROM hero_section
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {

            $error = "Hero section record not found.";

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

                    $error = "There was an error uploading the image. Error code: " . $_FILES['image']['error'];

                } else {

                    $allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    $fileType = mime_content_type($_FILES['image']['tmp_name']);

                    if (!in_array($fileType, $allowedTypes)) {

                        $error = "Only JPG, PNG or WEBP images are allowed.";

                    } else {

                        // Verify actual image
                        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);

                        if ($imageInfo === false) {

                            $error = "The file is not a valid image.";

                        } else {

                            // File size limit (5MB)
                            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {

                                $error = "Image size must be less than 5MB.";

                            } else {

                                // Get extension based on MIME type
                                $extensionMap = [
                                    'image/jpeg' => 'jpg',
                                    'image/png'  => 'png',
                                    'image/webp' => 'webp'
                                ];

                                $extension = $extensionMap[$fileType];

                                $newImageName = 'hero_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                                $uploadDir = __DIR__ . '/../images/';

                                if (!is_dir($uploadDir)) {

                                    if (!mkdir($uploadDir, 0755, true)) {

                                        $error = "Unable to create the upload directory.";
                                        error_log("Failed to create upload directory: " . $uploadDir);
                                    }
                                }

                                if (empty($error)) {

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
                                            error_log("move_uploaded_file failed. Target: " . $uploadPath);
                                            error_log("Upload error code: " . $_FILES['image']['error']);
                                        }
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
                        UPDATE hero_section
                        SET
                            title = ?,
                            subtitle = ?,
                            image = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $title,
                        $subtitle,
                        $imageName,
                        $id
                    ]);

                    $success = "Hero section updated successfully.";

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
| FETCH LATEST HERO SECTION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        subtitle,
        image
    FROM hero_section
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute();

$hero = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<div class="dashboard-content">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Hero Section</h2>

            <p>
                Manage the hero section title, subtitle and background image displayed on the website.
            </p>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (!empty($success)): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- HERO SECTION -->

    <div class="dashboard-card">

        <div class="card-header">

            <div>

                <h3>Hero Section</h3>

                <p>
                    Update the hero section content and image displayed on the website.
                </p>

            </div>

        </div>


        <?php if ($hero): ?>

            <form method="POST" enctype="multipart/form-data" id="heroForm" onsubmit="return prepareSubmit()">

                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars($hero['id']) ?>"
                >

                <!-- Base64-encoded fields -->
                <input type="hidden" name="title_encoded"    id="title_encoded"    value="">
                <input type="hidden" name="subtitle_encoded" id="subtitle_encoded" value="">


                <div class="head-message-grid">


                    <!-- IMAGE -->

                    <div class="image-section">

                        <label>
                            Current Hero Image
                        </label>


                        <div class="current-image">

                            <?php if (!empty($hero['image'])): ?>

                                <img
                                    src="../images/<?= htmlspecialchars($hero['image']) ?>?v=<?= time() ?>"
                                    alt="<?= htmlspecialchars($hero['title']) ?>"
                                    id="currentHeroImage"
                                >

                            <?php else: ?>

                                <div class="no-image">
                                    No image uploaded
                                </div>

                            <?php endif; ?>

                        </div>


                        <div class="form-group">

                            <label for="image">
                                Change Hero Image
                            </label>

                            <input
                                type="file"
                                id="image"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                                onchange="previewImage(event)"
                            >

                            <small class="form-help">
                                JPG, PNG or WEBP only. Max 5MB.
                            </small>

                            <p id="image-error" style="color:red; margin-top:8px;"></p>

                        </div>

                    </div>



                    <!-- DETAILS -->

                    <div class="description-section">


                        <!-- TITLE -->

                        <div class="form-group">

                            <label for="title">
                                Hero Title
                            </label>

                            <input
                                type="text"
                                id="title"
                                class="form-control"
                                value="<?= htmlspecialchars($hero['title']) ?>"
                                oninput="syncField('title')"
                                required
                            >

                        </div>


                        <!-- SUBTITLE -->

                        <div class="form-group">

                            <label for="subtitle">
                                Hero Subtitle
                            </label>

                            <input
                                type="text"
                                id="subtitle"
                                class="form-control"
                                value="<?= htmlspecialchars($hero['subtitle']) ?>"
                                oninput="syncField('subtitle')"
                                required
                            >

                        </div>


                        <!-- UPDATE -->

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Update Hero Section
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        <?php else: ?>

            <p>No Hero section found.</p>

        <?php endif; ?>

    </div>

</div>


<script>
// UTF-8-safe Base64 encode
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

// Sync a text field into its hidden Base64 field
function syncField(fieldId) {
    const visible = document.getElementById(fieldId);
    const hidden  = document.getElementById(fieldId + '_encoded');

    if (visible && hidden) {
        hidden.value = b64EncodeUtf8(visible.value);
    }
}

// Ensure Base64 encoding is done before submit
function prepareSubmit() {
    syncField('title');
    syncField('subtitle');
    return true;
}

// Initialize hidden fields on page load
document.addEventListener('DOMContentLoaded', function() {
    syncField('title');
    syncField('subtitle');
});

// Preview selected image and validate before upload
function previewImage(event) {
    const file = event.target.files[0];
    const errorMsg = document.getElementById('image-error');
    const preview  = document.getElementById('currentHeroImage');

    errorMsg.textContent = '';

    if (!file) return;

    // Validate type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        errorMsg.textContent = 'Only JPG, PNG or WEBP images are allowed.';
        event.target.value = '';
        return;
    }

    // Validate size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        errorMsg.textContent = 'Image size must be less than 5MB.';
        event.target.value = '';
        return;
    }

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        if (preview) {
            preview.src = e.target.result;
        } else {
            const wrapper = document.querySelector('.current-image');
            if (wrapper) {
                wrapper.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="currentHeroImage">';
            }
        }
    };
    reader.readAsDataURL(file);
}
</script>


<?php include 'footer.php'; ?>