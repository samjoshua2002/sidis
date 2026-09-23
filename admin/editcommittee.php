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
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? $_POST['id'] ?? '';

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: committee.php');
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
| FETCH EXISTING MEMBER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, name, designation
    FROM advisory_committee
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header('Location: committee.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DEFAULT FORM VALUES
|--------------------------------------------------------------------------
| Unique variable names to avoid clashes with header.php
*/

$committeeName        = $member['name'];
$committeeDesignation = $member['designation'];


/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $committeeName        = reconstructChunks('name');
    $committeeDesignation = reconstructChunks('designation');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($committeeName === '') {

        $error = 'Please enter the committee member name.';

    } elseif ($committeeDesignation === '') {

        $error = 'Please enter the designation.';

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                UPDATE advisory_committee
                SET name = ?, designation = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $committeeName,
                $committeeDesignation,
                $id
            ]);

            header('Location: committee.php?success=updated');
            exit;

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to update the committee member. Please try again.';

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
            <h2>Edit Advisory Committee Member</h2>
            <p>Update the committee member details.</p>
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
                <h3>Edit Committee Member #<?= $id ?></h3>
                <p>Update the member's name and designation.</p>
            </div>
        </div>


        <form method="POST" action="" id="committeeForm" onsubmit="return prepareSubmit()">

            <input type="hidden" name="id" value="<?= $id ?>">


            <!-- NAME (chunked Base64) -->
            <div class="form-group">

                <label for="name">Name</label>

                <input
                    type="text"
                    id="name"
                    class="form-control"
                    value="<?= htmlspecialchars($committeeName, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter member name"
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
                ><?= htmlspecialchars($committeeDesignation, ENT_QUOTES, 'UTF-8') ?></textarea>

                <input type="hidden" id="designation_chunk_count" name="designation_chunk_count" value="0">
                <div id="designation_chunks_container"></div>

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Update Committee Member
                </button>

                <a href="committee.php" class="btn btn-secondary">
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

/* Ensure fields are encoded before submit */
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