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
| FORM DEFAULTS
|--------------------------------------------------------------------------
| Unique variable names to avoid clashes with header.php
*/

$facultyName     = '';
$facultyDept     = '';
$facultyDesig    = '';
$facultyEmail    = '';
$facultyPage     = '';
$topOrder        = '0';
$selectedClusters = [];


/*
|--------------------------------------------------------------------------
| FORCE RESET ON FRESH GET LOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $facultyName      = '';
    $facultyDept      = '';
    $facultyDesig     = '';
    $facultyEmail     = '';
    $facultyPage      = '';
    $topOrder         = '0';
    $selectedClusters = [];
}


/*
|--------------------------------------------------------------------------
| IMAGE DIRECTORY
|--------------------------------------------------------------------------
*/

$imageDirectory = __DIR__ . '/../images/faculty/';


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
| FETCH CLUSTERS
|--------------------------------------------------------------------------
*/

$clusterStmt = $pdo->prepare("
    SELECT id, cluster_name
    FROM clusters
    WHERE status = 1
    ORDER BY id ASC
");
$clusterStmt->execute();
$clusters = $clusterStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PROCESS ADD FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $facultyName  = reconstructChunks('name');
    $facultyDept  = reconstructChunks('department');
    $facultyDesig = reconstructChunks('designation');
    $facultyEmail = reconstructChunks('email');
    $facultyPage  = reconstructChunks('personal_page');

    $topOrder = isset($_POST['top_order']) ? (string)$_POST['top_order'] : '0';


    /*
    |--------------------------------------------------------------------------
    | CLUSTERS
    |--------------------------------------------------------------------------
    */

    $selectedClusters = $_POST['clusters'] ?? [];

    if (!is_array($selectedClusters)) {
        $selectedClusters = [];
    }

    $selectedClusters = array_map('intval', $selectedClusters);
    $selectedClusters = array_values(
        array_unique(
            array_filter(
                $selectedClusters,
                function ($value) {
                    return $value > 0;
                }
            )
        )
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATE TOP ORDER
    |--------------------------------------------------------------------------
    */

    if ($topOrder !== '0' && $topOrder !== '1') {
        $topOrder = '0';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($facultyName === '') {

        $error = 'Please enter the faculty member name.';

    } elseif ($facultyDept === '') {

        $error = 'Please enter the department.';

    } elseif ($facultyDesig === '') {

        $error = 'Please enter the designation.';

    } elseif ($topOrder === '0' && empty($selectedClusters)) {

        $error = 'Please select at least one cluster for cluster faculty.';

    } elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please upload a faculty image.';

    }


    /*
    |--------------------------------------------------------------------------
    | TOP FACULTY
    |--------------------------------------------------------------------------
    | Top faculty cannot have clusters.
    */

    if ($topOrder === '1') {
        $selectedClusters = [];
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE VALIDATION
    |--------------------------------------------------------------------------
    */

    $mimeType = '';

    if ($error === '') {

        $image = $_FILES['image'];

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($image['tmp_name']);

        if (!isset($allowedTypes[$mimeType])) {

            $error = 'Only JPG, PNG and WEBP images are allowed.';

        } elseif ($image['size'] > 5 * 1024 * 1024) {

            $error = 'Image size must not exceed 5 MB.';

        } elseif (@getimagesize($image['tmp_name']) === false) {

            $error = 'The uploaded file is not a valid image.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $imagePath = null;

        try {

            if (!is_dir($imageDirectory)) {
                @mkdir($imageDirectory, 0755, true);
            }

            if (!is_writable($imageDirectory)) {
                throw new Exception('Upload directory is not writable.');
            }

            $extension = $allowedTypes[$mimeType];

            $imageName = 'faculty_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $imagePath = $imageDirectory . $imageName;

            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                throw new Exception('Unable to upload image.');
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE CLUSTER IDs
            |--------------------------------------------------------------------------
            */

            $clusterIds = '';

            if ($topOrder === '0' && !empty($selectedClusters)) {
                $clusterIds = implode(',', $selectedClusters);
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT FACULTY
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO faculty
                (name, image, department, designation, email, personal_page, cluster_id, top_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $facultyName,
                $imageName,
                $facultyDept,
                $facultyDesig,
                $facultyEmail,
                $facultyPage,
                $clusterIds,
                $topOrder
            ]);

            header('Location: faculty.php?success=added');
            exit;

        } catch (Exception $e) {

            if ($imagePath !== null && file_exists($imagePath)) {
                @unlink($imagePath);
            }

            error_log("Faculty Add Error: " . $e->getMessage());
            $error = 'Unable to add the faculty member. Please try again.';

        } catch (PDOException $e) {

            if ($imagePath !== null && file_exists($imagePath)) {
                @unlink($imagePath);
            }

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to add the faculty member. Please try again.';

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
            <h2>Add Faculty</h2>
            <p>Add a new faculty member to the website.</p>
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
                <h3>Faculty Details</h3>
                <p>Enter the faculty member's details.</p>
            </div>
        </div>


        <form method="POST" action="" enctype="multipart/form-data" id="facultyForm" onsubmit="return prepareSubmit()">


            <!-- NAME (chunked Base64) -->

            <div class="form-group">

                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyName, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter faculty name"
                    oninput="syncChunked('name')"
                    required
                >

                <input type="hidden" id="name_chunk_count" name="name_chunk_count" value="0">
                <div id="name_chunks_container"></div>

            </div>


            <!-- DEPARTMENT (chunked Base64) -->

            <div class="form-group">

                <label for="department">Department</label>

                <input
                    type="text"
                    id="department"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyDept, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter department"
                    oninput="syncChunked('department')"
                    required
                >

                <input type="hidden" id="department_chunk_count" name="department_chunk_count" value="0">
                <div id="department_chunks_container"></div>

            </div>


            <!-- DESIGNATION (chunked Base64) -->

            <div class="form-group">

                <label for="designation">Designation</label>

                <textarea
                    id="designation"
                    class="form-control"
                    rows="3"
                    placeholder="Enter designation"
                    oninput="syncChunked('designation')"
                    required
                ><?= htmlspecialchars($facultyDesig, ENT_QUOTES, 'UTF-8') ?></textarea>

                <input type="hidden" id="designation_chunk_count" name="designation_chunk_count" value="0">
                <div id="designation_chunks_container"></div>

            </div>


            <!-- EMAIL (chunked Base64) -->

            <div class="form-group">

                <label for="email">Email</label>

                <input
                    type="text"
                    id="email"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyEmail, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter email address"
                    oninput="syncChunked('email')"
                >

                <input type="hidden" id="email_chunk_count" name="email_chunk_count" value="0">
                <div id="email_chunks_container"></div>

            </div>


            <!-- PERSONAL PAGE (chunked Base64) -->

            <div class="form-group">

                <label for="personal_page">Personal Page</label>

                <input
                    type="text"
                    id="personal_page"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyPage, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://example.com"
                    oninput="syncChunked('personal_page')"
                >

                <input type="hidden" id="personal_page_chunk_count" name="personal_page_chunk_count" value="0">
                <div id="personal_page_chunks_container"></div>

            </div>


            <!-- FACULTY TYPE -->

            <div class="form-group">

                <label>Faculty Type</label>

                <div style="margin-top:10px;">

                    <label style="margin-right:25px;">
                        <input
                            type="radio"
                            name="top_order"
                            value="1"
                            id="topFaculty"
                            <?= $topOrder === '1' ? 'checked' : '' ?>
                        >
                        Top Faculty
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="top_order"
                            value="0"
                            id="clusterFaculty"
                            <?= $topOrder === '0' ? 'checked' : '' ?>
                        >
                        Cluster Faculty
                    </label>

                </div>

            </div>


            <!-- CLUSTERS -->

            <div
                class="form-group"
                id="clusterSection"
                style="<?= $topOrder === '1' ? 'display:none;' : '' ?>"
            >

                <label>Clusters</label>

                <small style="display:block; margin-bottom:12px;">
                    Select one or more clusters.
                </small>

                <div
                    style="
                        display:grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap:10px;
                    "
                >

                    <?php foreach ($clusters as $cluster): ?>

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:8px;
                                padding:10px;
                                border:1px solid #ddd;
                                border-radius:6px;
                            "
                        >

                            <input
                                type="checkbox"
                                name="clusters[]"
                                value="<?= (int)$cluster['id'] ?>"
                                <?= in_array((int)$cluster['id'], $selectedClusters, true) ? 'checked' : '' ?>
                            >

                            <?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?>

                        </label>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- IMAGE -->

            <div class="form-group">

                <label for="image">Faculty Image</label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <small>
                    Allowed formats: JPG, PNG, WEBP. Maximum size: 5 MB.
                </small>

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Add Faculty
                </button>

                <a href="faculty.php" class="btn btn-secondary">
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

/* Ensure all fields are encoded before submit */
function prepareSubmit() {
    syncChunked('name');
    syncChunked('department');
    syncChunked('designation');
    syncChunked('email');
    syncChunked('personal_page');
    return true;
}

/* Initialize */
document.addEventListener('DOMContentLoaded', function () {

    syncChunked('name');
    syncChunked('department');
    syncChunked('designation');
    syncChunked('email');
    syncChunked('personal_page');


    /* Toggle clusters visibility based on faculty type */

    const topFaculty     = document.getElementById('topFaculty');
    const clusterFaculty = document.getElementById('clusterFaculty');
    const clusterSection = document.getElementById('clusterSection');

    function toggleClusters() {
        if (topFaculty.checked) {
            clusterSection.style.display = 'none';
            clusterSection.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                cb.checked = false;
            });
        } else {
            clusterSection.style.display = 'block';
        }
    }

    if (topFaculty && clusterFaculty && clusterSection) {
        topFaculty.addEventListener('change', toggleClusters);
        clusterFaculty.addEventListener('change', toggleClusters);
    }

});

</script>


<?php include 'footer.php'; ?>