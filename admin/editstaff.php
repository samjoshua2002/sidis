<?php
/*
|--------------------------------------------------------------------------
| BOOTSTRAP — process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$error = '';

/*
|--------------------------------------------------------------------------
| IMAGE DIRECTORY
|--------------------------------------------------------------------------
*/

$imageDirectory = __DIR__ . '/../images/staff/';


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? $_POST['id'] ?? '';

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: staff.php');
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
| FETCH EXISTING STAFF
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, name, designation, image
    FROM staff
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: staff.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DEFAULT FORM VALUES (unique names to avoid clashes with header.php)
|--------------------------------------------------------------------------
*/

$staffName  = $member['name'];
$staffDesig = $member['designation'];
$image      = $member['image'];


/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $staffName  = reconstructChunks('name');
    $staffDesig = reconstructChunks('designation');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($staffName === '') {

        $error = 'Please enter the staff member name.';

    } elseif ($staffDesig === '') {

        $error = 'Please enter the designation.';

    }


    /*
    |--------------------------------------------------------------------------
    | NEW IMAGE (optional)
    |--------------------------------------------------------------------------
    */

    $newImageName = null;
    $newImagePath = null;

    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = 'Unable to upload the new image.';

        } else {

            $uploadedImage = $_FILES['image'];

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($uploadedImage['tmp_name']);

            if (!isset($allowedTypes[$mimeType])) {

                $error = 'Only JPG, PNG and WEBP images are allowed.';

            } elseif ($uploadedImage['size'] > 5 * 1024 * 1024) {

                $error = 'Image size must not exceed 5 MB.';

            } elseif (@getimagesize($uploadedImage['tmp_name']) === false) {

                $error = 'The uploaded file is not a valid image.';

            } else {

                if (!is_dir($imageDirectory)) {
                    @mkdir($imageDirectory, 0755, true);
                }

                if (!is_writable($imageDirectory)) {

                    $error = 'Image upload directory is not writable.';

                } else {

                    $extension    = $allowedTypes[$mimeType];
                    $newImageName = 'staff_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $newImagePath = $imageDirectory . $newImageName;

                    if (!move_uploaded_file($uploadedImage['tmp_name'], $newImagePath)) {
                        $error = 'Unable to upload the new image.';
                    }

                }

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            /*
            |--------------------------------------------------------------------------
            | UPDATE WITH NEW IMAGE
            |--------------------------------------------------------------------------
            */

            if ($newImageName !== null) {

                $stmt = $pdo->prepare("
                    UPDATE staff
                    SET name = ?, designation = ?, image = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $staffName,
                    $staffDesig,
                    $newImageName,
                    $id
                ]);

            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE WITHOUT CHANGING IMAGE
            |--------------------------------------------------------------------------
            */

            else {

                $stmt = $pdo->prepare("
                    UPDATE staff
                    SET name = ?, designation = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $staffName,
                    $staffDesig,
                    $id
                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | DELETE OLD IMAGE (only if new image was uploaded)
            |--------------------------------------------------------------------------
            */

            if (
                $newImageName !== null &&
                !empty($image) &&
                file_exists($imageDirectory . $image)
            ) {
                @unlink($imageDirectory . $image);
            }


            /*
            |--------------------------------------------------------------------------
            | REDIRECT BEFORE HEADER.PHP
            |--------------------------------------------------------------------------
            */

            header('Location: staff.php?success=updated');
            exit;

        } catch (PDOException $e) {

            if ($newImagePath !== null && file_exists($newImagePath)) {
                @unlink($newImagePath);
            }

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to update the staff member. Please try again.';

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


    <!-- PAGE HEADER -->

    <div class="page-header">
        <div>
            <h2>Edit Staff</h2>
            <p>Update the staff member details.</p>
        </div>
    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>


    <!-- FORM CARD -->

    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3>Edit Staff #<?= $id ?></h3>
                <p>Update the staff member's details.</p>
            </div>
        </div>


        <form method="POST" action="" enctype="multipart/form-data" id="staffForm" onsubmit="return prepareSubmit()">

            <input type="hidden" name="id" value="<?= $id ?>">


            <!-- NAME (chunked Base64) -->

            <div class="form-group">

                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    class="form-control"
                    value="<?= htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter staff name"
                    oninput="syncChunked('name')"
                    required
                >

                <input type="hidden" id="name_chunk_count" name="name_chunk_count" value="0">
                <div id="name_chunks_container"></div>

            </div>


            <!-- DESIGNATION (chunked Base64) -->

            <div class="form-group">

                <label for="designation">Designation</label>

                <textarea
                    id="designation"
                    class="form-control"
                    rows="4"
                    placeholder="Enter designation"
                    oninput="syncChunked('designation')"
                    required
                ><?= htmlspecialchars($staffDesig, ENT_QUOTES, 'UTF-8') ?></textarea>

                <input type="hidden" id="designation_chunk_count" name="designation_chunk_count" value="0">
                <div id="designation_chunks_container"></div>

            </div>


            <!-- CURRENT IMAGE -->

            <div class="form-group">

                <label>Current Image</label>

                <?php if (!empty($image)): ?>

                    <div style="margin-bottom:15px;">
                        <img
                            src="../images/staff/<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>?v=<?= time() ?>"
                            alt="<?= htmlspecialchars($staffName, ENT_QUOTES, 'UTF-8') ?>"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:8px;
                            "
                        >
                    </div>

                <?php endif; ?>

                <label for="image">Replace Image</label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Leave empty to keep the current image.
                    Allowed formats: JPG, PNG, WEBP.
                    Maximum size: 5 MB.
                </small>

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Update Staff
                </button>

                <a href="staff.php" class="btn btn-secondary">
                    Cancel
                </a>

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

/* Encode all fields before submit */
function prepareSubmit() {
    syncChunked('name');
    syncChunked('designation');
    return true;
}

/* Initialize on page load */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('name');
    syncChunked('designation');
});

</script>


<?php include 'footer.php'; ?>