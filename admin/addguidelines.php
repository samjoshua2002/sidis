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
| FORM DEFAULTS
|--------------------------------------------------------------------------
| Unique variable names to avoid clashes with header.php
*/

$guidelineTitle = '';
$guidelineText  = '';


/*
|--------------------------------------------------------------------------
| FORCE RESET ON FRESH GET LOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $guidelineTitle = '';
    $guidelineText  = '';
}


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
| PROCESS ADD FORM
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
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO guidelines
                (title, guideline)
                VALUES (?, ?)
            ");

            $stmt->execute([
                $guidelineTitle,
                $guidelineText
            ]);

            header('Location: guidelines.php?success=added');
            exit;

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to add the guideline. Please try again.';

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
            <h2>Add Guideline</h2>
            <p>Add a new guideline to the Guidelines section.</p>
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
                <h3>Guideline Details</h3>
                <p>Select the guideline category and enter the guideline point.</p>
            </div>
        </div>


        <form method="POST" action="" id="guidelineForm" onsubmit="return prepareSubmit()">


            <!-- TITLE (dropdown, base64-encoded via JS) -->
            <div class="form-group">

                <label for="title">Guideline Title</label>

                <select
                    id="title"
                    class="form-control"
                    onchange="syncChunked('title')"
                    required
                >

                    <option value="" <?= $guidelineTitle === '' ? 'selected' : '' ?>>
                        Select guideline title
                    </option>

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


            <!-- GUIDELINE (chunked Base64) -->
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
                    Add Guideline
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