<?php
/*
|--------------------------------------------------------------------------
| BOOTSTRAP - process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$error = '';
$facultyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($facultyId <= 0) {
    header('Location: faculty.php');
    exit;
}

$imageDirectory = __DIR__ . '/../images/faculty/';

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

// Fetch existing faculty record
$stmt = $pdo->prepare("SELECT * FROM faculty WHERE id = ? LIMIT 1");
$stmt->execute([$facultyId]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faculty) {
    header('Location: faculty.php');
    exit;
}

// Fetch Clusters
$clusterStmt = $pdo->prepare("SELECT id, cluster_name FROM clusters WHERE status = 1 ORDER BY id ASC");
$clusterStmt->execute();
$clusters = $clusterStmt->fetchAll(PDO::FETCH_ASSOC);

$facultyName      = $faculty['name'];
$facultyDept      = $faculty['department'];
$facultyDesig     = $faculty['designation'];
$facultyEmail     = $faculty['email'];
$facultyPage      = $faculty['personal_page'];
$facultyOffice    = $faculty['office'];
$facultyPhone     = $faculty['phone'];
$pageType         = !empty($faculty['page_type']) ? $faculty['page_type'] : ((!empty($facultyPage) && $facultyPage !== '#') ? 'personal' : 'inner');
$topOrder         = (string)($faculty['top_order'] ?? '0');
$selectedClusters = !empty($faculty['cluster_id']) ? array_map('intval', explode(',', $faculty['cluster_id'])) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facultyName   = reconstructChunks('name');
    $facultyDept   = reconstructChunks('department');
    $facultyDesig  = reconstructChunks('designation');
    $facultyEmail  = reconstructChunks('email');
    $facultyPage   = reconstructChunks('personal_page');
    $facultyOffice = reconstructChunks('office');
    $facultyPhone  = reconstructChunks('phone');
    $pageType      = reconstructChunks('page_type');

    $topOrder = isset($_POST['top_order']) ? (string)$_POST['top_order'] : '0';

    if (in_array($topOrder, ['1', '2', '3'], true)) {
        if ($pageType !== 'inner' && $pageType !== 'personal') {
            $pageType = (!empty($facultyPage) && $facultyPage !== '#') ? 'personal' : 'inner';
        }
        $selectedClusters = [];
    } else {
        $topOrder = '0';
        $pageType = 'personal';
        $selectedClusters = $_POST['clusters'] ?? [];
        if (!is_array($selectedClusters)) $selectedClusters = [];
        $selectedClusters = array_map('intval', $selectedClusters);
        $selectedClusters = array_values(array_unique(array_filter($selectedClusters, function($v) { return $v > 0; })));
    }

    if (!in_array($topOrder, ['0', '1', '2', '3'], true)) {
        $topOrder = '0';
    }

    if ($facultyName === '') {
        $error = 'Please enter the faculty member name.';
    } elseif ($facultyDept === '') {
        $error = 'Please enter the department.';
    } elseif ($facultyDesig === '') {
        $error = 'Please enter the designation.';
    } elseif ($topOrder === '0' && empty($selectedClusters)) {
        $error = 'Please select at least one cluster for associate faculty.';
    }

    // Image Upload (optional on edit)
    $imageName = $faculty['image'];
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if ($error === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($image['tmp_name']);

        if (!isset($allowedTypes[$mimeType])) {
            $error = 'Only JPG, PNG and WEBP images are allowed.';
        } elseif ($image['size'] > 5 * 1024 * 1024) {
            $error = 'Image size must not exceed 5 MB.';
        } elseif (@getimagesize($image['tmp_name']) === false) {
            $error = 'The uploaded file is not a valid image.';
        } else {
            $ext = $allowedTypes[$mimeType];
            $newImageName = 'faculty_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $newImagePath = $imageDirectory . $newImageName;

            if (move_uploaded_file($image['tmp_name'], $newImagePath)) {
                $imageName = $newImageName;
            } else {
                $error = 'Failed to upload new faculty image.';
            }
        }
    }

    if ($error === '') {
        try {
            $clusterIds = '';
            if ($topOrder === '0' && !empty($selectedClusters)) {
                $clusterIds = implode(',', $selectedClusters);
            }

            $stmtUp = $pdo->prepare("
                UPDATE faculty SET
                    name          = ?,
                    image         = ?,
                    department    = ?,
                    designation   = ?,
                    email         = ?,
                    personal_page = ?,
                    cluster_id    = ?,
                    top_order     = ?,
                    page_type     = ?,
                    office        = ?,
                    phone         = ?
                WHERE id = ?
            ");

            $stmtUp->execute([
                $facultyName,
                $imageName,
                $facultyDept,
                $facultyDesig,
                $facultyEmail,
                $facultyPage,
                $clusterIds,
                $topOrder,
                $pageType,
                $facultyOffice,
                $facultyPhone,
                $facultyId
            ]);

            header('Location: faculty.php?success=updated');
            exit;
        } catch (PDOException $e) {
            error_log("Faculty Edit Error: " . $e->getMessage());
            $error = 'Unable to update faculty member. Please try again.';
        }
    }
}

include 'header.php';
?>

<div class="dashboard-content">
    <div class="page-header">
        <div>
            <h2>Edit Faculty: <?= htmlspecialchars($faculty['name']) ?></h2>
            <p>Update faculty information, category, profile options, and contact details.</p>
        </div>
        <div>
            <a href="faculty.php" class="btn btn-secondary">&larr; Back to Faculty</a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>Faculty Details</h3>
                <p>Modify faculty member information.</p>
            </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" id="facultyForm" onsubmit="return prepareSubmit()">

            <!-- NAME (chunked Base64) -->
            <div class="form-group">
                <label for="name">Name *</label>
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
                <label for="department">Department *</label>
                <input
                    type="text"
                    id="department"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyDept, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Metallurgical and Materials Engineering or SIDIS"
                    oninput="syncChunked('department')"
                    required
                >
                <input type="hidden" id="department_chunk_count" name="department_chunk_count" value="0">
                <div id="department_chunks_container"></div>
            </div>

            <!-- DESIGNATION (chunked Base64) -->
            <div class="form-group">
                <label for="designation">Designation *</label>
                <textarea
                    id="designation"
                    class="form-control"
                    rows="2"
                    placeholder="e.g. Assistant Professor or Associate Professor"
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
                    placeholder="e.g. name@iitm.ac.in"
                    oninput="syncChunked('email')"
                >
                <input type="hidden" id="email_chunk_count" name="email_chunk_count" value="0">
                <div id="email_chunks_container"></div>
            </div>

            <!-- FACULTY TYPE -->
            <!-- FACULTY CATEGORY -->
            <div class="form-group">
                <label style="font-weight:600;">Faculty Category *</label>
                <p style="font-size:13px; color:#64748b; margin-top:2px; margin-bottom:10px;">Select faculty category. Top faculty members (Core, Adjunct/Visiting, PoP) have dedicated profile pages.</p>

                <div style="background:#f8fafc; padding:16px 18px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:12px;">
                    <div style="font-size:12px; font-weight:700; color:#184C74; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                        Top Faculty (SIDIS Faculty) — with Inner Profile Pages
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:20px;">
                        <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                            <input
                                type="radio"
                                name="top_order"
                                value="1"
                                id="catCore"
                                <?= $topOrder === '1' ? 'checked' : '' ?>
                                onchange="handleFacultyTypeChange('1')"
                            >
                            <strong>Core Faculty</strong>
                        </label>
                        <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                            <input
                                type="radio"
                                name="top_order"
                                value="2"
                                id="catAdjunct"
                                <?= $topOrder === '2' ? 'checked' : '' ?>
                                onchange="handleFacultyTypeChange('2')"
                            >
                            <strong>Adjunct / Visiting Faculty</strong>
                        </label>
                        <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                            <input
                                type="radio"
                                name="top_order"
                                value="3"
                                id="catPop"
                                <?= $topOrder === '3' ? 'checked' : '' ?>
                                onchange="handleFacultyTypeChange('3')"
                            >
                            <strong>Professor of Practice (PoP)</strong>
                        </label>
                    </div>
                </div>

                <div style="background:#f8fafc; padding:14px 18px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom: 20px;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px; margin:0;">
                        <input
                            type="radio"
                            name="top_order"
                            value="0"
                            id="catAssociate"
                            <?= $topOrder === '0' ? 'checked' : '' ?>
                            onchange="handleFacultyTypeChange('0')"
                        >
                        <span><strong>Associate Faculty</strong> (Cluster Faculty / Department Affiliate)</span>
                    </label>
                </div>
            </div>

            <!-- PROFILE PAGE TYPE (Only shown for Top Faculty) -->
            <div class="form-group" id="profileTypeWrapper" style="<?= in_array($topOrder, ['1', '2', '3'], true) ? '' : 'display:none;' ?>; background: #f8fafc; padding: 18px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                <label style="font-weight:600; color:#184C74;">Profile Page Option</label>
                <p style="font-size:13px; color:#64748b; margin-top:2px; margin-bottom:12px;">Choose whether this top-order faculty member links to an external personal website or uses the dedicated SIDIS inner profile page.</p>

                <div style="display:flex; gap:25px; margin-bottom: 12px;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input
                            type="radio"
                            name="page_type_radio"
                            value="personal"
                            id="typePersonal"
                            <?= $pageType === 'personal' ? 'checked' : '' ?>
                            onchange="handlePageTypeChange('personal')"
                        >
                        Personal / External Page Link
                    </label>

                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input
                            type="radio"
                            name="page_type_radio"
                            value="inner"
                            id="typeInner"
                            <?= $pageType === 'inner' ? 'checked' : '' ?>
                            onchange="handlePageTypeChange('inner')"
                        >
                        Inner Faculty Page (innerfaculty.php)
                    </label>
                </div>

                <input type="hidden" id="page_type_val" value="<?= htmlspecialchars($pageType) ?>">
                <input type="hidden" id="page_type_chunk_count" name="page_type_chunk_count" value="0">
                <div id="page_type_chunks_container"></div>

                <!-- BASIC DETAILS FOR INNER PAGE (Building No & Mobile) -->
                <div id="innerBasicDetails" style="<?= (in_array($topOrder, ['1', '2', '3'], true) && $pageType === 'inner') ? '' : 'display:none;' ?>; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div>
                            <label for="office" style="font-size:13px; font-weight:600;">Building / Office No</label>
                            <input
                                type="text"
                                id="office"
                                class="form-control"
                                value="<?= htmlspecialchars($facultyOffice, ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="e.g. 521, ED Building"
                                oninput="syncChunked('office')"
                            >
                            <input type="hidden" id="office_chunk_count" name="office_chunk_count" value="0">
                            <div id="office_chunks_container"></div>
                        </div>

                        <div>
                            <label for="phone" style="font-size:13px; font-weight:600;">Mobile / Office Phone Number</label>
                            <input
                                type="text"
                                id="phone"
                                class="form-control"
                                value="<?= htmlspecialchars($facultyPhone, ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="e.g. 044-2257-2303"
                                oninput="syncChunked('phone')"
                            >
                            <input type="hidden" id="phone_chunk_count" name="phone_chunk_count" value="0">
                            <div id="phone_chunks_container"></div>
                        </div>
                    </div>
                    <small style="color:#64748b; font-size:12px; margin-top:6px; display:block;">
                        * The faculty member will be able to log in to the Faculty Portal to manage education, biography, and publications.
                    </small>
                </div>
            </div>

            <!-- PERSONAL PAGE URL -->
            <div class="form-group" id="personalPageWrapper" style="<?= (in_array($topOrder, ['1', '2', '3'], true) && $pageType === 'inner') ? 'display:none;' : '' ?>">
                <label for="personal_page" id="personalPageLabel"><?= in_array($topOrder, ['1', '2', '3'], true) ? 'Personal Website URL' : 'Department Profile / Personal Page URL' ?></label>
                <input
                    type="text"
                    id="personal_page"
                    class="form-control"
                    value="<?= htmlspecialchars($facultyPage, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://example.iitm.ac.in/..."
                    oninput="syncChunked('personal_page')"
                >
                <input type="hidden" id="personal_page_chunk_count" name="personal_page_chunk_count" value="0">
                <div id="personal_page_chunks_container"></div>
            </div>

            <!-- CLUSTERS (Associate / Cluster Faculty Only) -->
            <div class="form-group" id="clusterSection" style="<?= in_array($topOrder, ['1', '2', '3'], true) ? 'display:none;' : '' ?>">
                <label>Clusters *</label>
                <small style="display:block; margin-bottom:12px;">Select one or more clusters.</small>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <?php foreach ($clusters as $cluster): ?>
                        <label style="display:flex; align-items:center; gap:8px; padding:10px; border:1px solid #ddd; border-radius:6px; cursor:pointer;">
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
                <?php if (!empty($faculty['image'])): ?>
                    <div style="margin-bottom:10px;">
                        <img src="../images/faculty/<?= htmlspecialchars($faculty['image']) ?>" alt="" style="width:70px; height:70px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                    </div>
                <?php endif; ?>
                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <small>Leave empty to keep existing photo. Allowed formats: JPG, PNG, WEBP. Maximum size: 5 MB.</small>
            </div>

            <!-- BUTTONS -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="background:#184C74;">
                    Update Faculty
                </button>
                <a href="faculty.php" class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

<script>
function handleFacultyTypeChange(val) {
    const clusterSection = document.getElementById('clusterSection');
    const profileTypeWrapper = document.getElementById('profileTypeWrapper');
    const personalPageWrapper = document.getElementById('personalPageWrapper');
    const personalPageLabel = document.getElementById('personalPageLabel');

    if (val === '1' || val === '2' || val === '3') {
        clusterSection.style.display = 'none';
        profileTypeWrapper.style.display = 'block';
        if (personalPageLabel) personalPageLabel.textContent = 'Personal Website URL';
        const curType = document.getElementById('page_type_val').value || 'personal';
        handlePageTypeChange(curType);
    } else {
        clusterSection.style.display = 'block';
        profileTypeWrapper.style.display = 'none';
        personalPageWrapper.style.display = 'block';
        if (personalPageLabel) personalPageLabel.textContent = 'Department Profile / Personal Page URL';
        document.getElementById('innerBasicDetails').style.display = 'none';
        document.getElementById('page_type_val').value = 'personal';
        syncCustomChunked('page_type', 'personal');
    }
}

function handlePageTypeChange(val) {
    document.getElementById('page_type_val').value = val;
    syncCustomChunked('page_type', val);

    const innerBasicDetails = document.getElementById('innerBasicDetails');
    const personalPageWrapper = document.getElementById('personalPageWrapper');

    if (val === 'inner') {
        innerBasicDetails.style.display = 'block';
        personalPageWrapper.style.display = 'none';
    } else {
        innerBasicDetails.style.display = 'none';
        personalPageWrapper.style.display = 'block';
    }
}

function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

function syncChunked(fieldId) {
    const visible = document.getElementById(fieldId);
    if (!visible) return;
    const value = visible.value || '';
    const base64 = b64EncodeUtf8(value);
    const chunks = splitIntoChunks(base64, 200);

    const countElem = document.getElementById(fieldId + '_chunk_count');
    const container = document.getElementById(fieldId + '_chunks_container');

    if (countElem) countElem.value = chunks.length;
    if (container) {
        container.innerHTML = '';
        chunks.forEach((chunk, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = fieldId + '_chunk_' + index;
            input.value = chunk;
            container.appendChild(input);
        });
    }
}

function syncCustomChunked(targetName, val) {
    const base64 = b64EncodeUtf8(String(val || ''));
    const chunks = splitIntoChunks(base64, 200);

    const countElem = document.getElementById(targetName + '_chunk_count');
    const container = document.getElementById(targetName + '_chunks_container');

    if (countElem) countElem.value = chunks.length;
    if (container) {
        container.innerHTML = '';
        chunks.forEach((chunk, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = targetName + '_chunk_' + index;
            input.value = chunk;
            container.appendChild(input);
        });
    }
}

function prepareSubmit() {
    ['name', 'department', 'designation', 'email', 'personal_page', 'office', 'phone'].forEach(f => syncChunked(f));
    const pt = document.getElementById('page_type_val').value || 'personal';
    syncCustomChunked('page_type', pt);
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    ['name', 'department', 'designation', 'email', 'personal_page', 'office', 'phone'].forEach(f => syncChunked(f));
    const pt = document.getElementById('page_type_val').value || 'personal';
    syncCustomChunked('page_type', pt);
});
</script>

<?php include 'footer.php'; ?>
