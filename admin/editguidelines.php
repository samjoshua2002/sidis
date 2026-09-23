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
| GUIDELINE TITLES (allowed set)
|--------------------------------------------------------------------------
*/

$guidelineTitles = [
    'Norms to propose a new IDDD program',
    'Norms to invite existing faculty members to SIDiS',
    'Norms to start a new cluster',
    'Performance evaluation of existing clusters'
];


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? $_POST['id'] ?? '';

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: guidelines.php');
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
| FETCH EXISTING GUIDELINE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, title, guideline
    FROM guidelines
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: guidelines.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DEFAULT FORM VALUES (unique names, no clash with header.php)
|--------------------------------------------------------------------------
*/

$guidelineTitle = $item['title'];
$guidelineText  = $item['guideline'];


/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $guidelineTitle = reconstructChunks('title');
    $guidelineText  = reconstructChunks('guideline');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($guidelineTitle === '') {

        $error = 'Please select the guideline title.';

    } elseif (!in_array($guidelineTitle, $guidelineTitles, true)) {

        $error = 'Please select a valid guideline title.';

    } elseif ($guidelineText === '') {

        $error = 'Please enter the guideline.';

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                UPDATE guidelines
                SET title = ?, guideline = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $guidelineTitle,
                $guidelineText,
                $id
            ]);

            header('Location: guidelines.php?success=updated');
            exit;

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to update the guideline. Please try again.';

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
            <h2>Edit Guideline</h2>
            <p>Update the guideline details.</p>
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
                <h3>Edit Guideline #<?= $id ?></h3>
                <p>Update the guideline title and guideline point.</p>
            </div>
        </div>


        <form method="POST" action="" id="guidelineForm" onsubmit="return prepareSubmit()">

            <input type="hidden" name="id" value="<?= $id ?>">


            <!-- TITLE (dropdown, chunked Base64) -->
            <div class="form-group">

                <label for="title">Guideline Title</label>

                <select
                    id="title"
                    class="form-control"
                    onchange="syncChunked('title')"
                    required
                >

                    <?php foreach ($guidelineTitles as $gt): ?>

                        <option
                            value="<?= htmlspecialchars($gt, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $guidelineTitle === $gt ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($gt, ENT_QUOTES, 'UTF-8') ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <input type="hidden" id="title_chunk_count" name="title_chunk_count" value="0">
                <div id="title_chunks_container"></div>

            </div>


            <!-- GUIDELINE (textarea, chunked Base64) -->
            <div class="form-group">

                <label for="guideline">Guideline</label>

                <textarea
                    id="guideline"
                    class="form-control"
                    rows="6"
                    placeholder="Enter guideline"
                    oninput="syncChunked('guideline')"
                    required
                ><?= htmlspecialchars($guidelineText, ENT_QUOTES, 'UTF-8') ?></textarea>

                <input type="hidden" id="guideline_chunk_count" name="guideline_chunk_count" value="0">
                <div id="guideline_chunks_container"></div>

            </div>


            <!-- BUTTONS -->
            <div class="form-actions">

                <button type="submit" class="btn btn-primary">
                    Update Guideline
                </button>

                <a href="guidelines.php" class="btn btn-secondary">
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

/* Sync a text/select field into chunked hidden inputs */
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
    syncChunked('guideline');
    return true;
}

/* Initialize on page load */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('title');
    syncChunked('guideline');
});

</script>


<?php include 'footer.php'; ?>