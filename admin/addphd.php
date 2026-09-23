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
| FETCH EXISTING BATCHES
|--------------------------------------------------------------------------
*/

try {

    $batchSql = "
        SELECT DISTINCT batch
        FROM phd_scholars
        WHERE batch IS NOT NULL
        AND TRIM(batch) <> ''
        ORDER BY batch ASC
    ";

    $batchStmt = $pdo->prepare($batchSql);
    $batchStmt->execute();

    $batches = $batchStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {

    $batches = [];

}


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$batch        = '';
$new_batch    = '';
$roll_number  = '';
$student_name = '';
$mail_id      = '';
$mentor_1     = '';
$mentor_2     = '';


/*
|--------------------------------------------------------------------------
| FORCE RESET ON FRESH GET LOAD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $batch        = '';
    $new_batch    = '';
    $roll_number  = '';
    $student_name = '';
    $mail_id      = '';
    $mentor_1     = '';
    $mentor_2     = '';
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Batch is a dropdown value — keep plain
    $batch     = trim($_POST['batch'] ?? '');
    $new_batch = reconstructChunks('new_batch');

    // Chunked Base64 text fields
    $roll_number  = reconstructChunks('roll_number');
    $student_name = reconstructChunks('student_name');
    $mail_id      = reconstructChunks('mail_id');
    $mentor_1     = reconstructChunks('mentor_1');
    $mentor_2     = reconstructChunks('mentor_2');


    /*
    |--------------------------------------------------------------------------
    | IF ADD NEW BATCH IS SELECTED
    |--------------------------------------------------------------------------
    */

    if ($batch === '__new__') {

        if ($new_batch === '') {
            $error = 'Please enter the new batch name.';
        } else {
            $batch = $new_batch;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if ($batch === '') {
            $error = 'Please select or enter a batch.';
        } elseif ($roll_number === '') {
            $error = 'Please enter the roll number.';
        } elseif ($student_name === '') {
            $error = 'Please enter the student name.';
        } elseif ($mail_id === '') {
            $error = 'Please enter the mail ID.';
        }

    }


    /*
    |--------------------------------------------------------------------------
    | DUPLICATE ROLL NUMBER CHECK
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $checkSql = "
                SELECT id
                FROM phd_scholars
                WHERE roll_number = ?
                LIMIT 1
            ";

            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$roll_number]);

            if ($checkStmt->fetch()) {
                $error = 'A scholar with this roll number already exists.';
            }

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to validate the roll number.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT SCHOLAR
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $sql = "
                INSERT INTO phd_scholars
                (batch, roll_number, student_name, mail_id, mentor_1, mentor_2)
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $batch,
                $roll_number,
                $student_name,
                $mail_id,
                $mentor_1,
                $mentor_2
            ]);

            header('Location: phd.php?success=added');
            exit;

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Unable to add the scholar. Please try again.';

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
            <h1>Add Ph.D. Scholar</h1>
            <p>Add a new Ph.D. scholar to the database.</p>
        </div>

        <a href="phd.php" class="btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to Ph.D. Scholars
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
                <h3>Add Scholar</h3>
                <p>Enter the Ph.D. scholar details below.</p>
            </div>
        </div>


        <div class="card-body">

            <form method="POST" action="" id="phdForm" onsubmit="return prepareSubmit()">


                <!-- BATCH (plain dropdown) -->

                <div class="form-group">

                    <label for="batch">
                        Batch <span class="required">*</span>
                    </label>

                    <select
                        name="batch"
                        id="batch"
                        class="form-control"
                        required
                        onchange="toggleNewBatch(this.value)"
                    >

                        <option value="">Select Batch</option>

                        <?php foreach ($batches as $existingBatch): ?>

                            <option
                                value="<?= htmlspecialchars($existingBatch, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($batch === $existingBatch) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($existingBatch, ENT_QUOTES, 'UTF-8') ?>
                            </option>

                        <?php endforeach; ?>

                        <option value="__new__" <?= ($batch === '__new__') ? 'selected' : '' ?>>
                            + Add New Batch
                        </option>

                    </select>

                </div>


                <!-- NEW BATCH (chunked Base64) -->

                <div
                    class="form-group"
                    id="newBatchGroup"
                    style="display: <?= ($batch === '__new__') ? 'block' : 'none' ?>;"
                >

                    <label for="new_batch">
                        New Batch <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="new_batch"
                        class="form-control"
                        value="<?= htmlspecialchars($new_batch, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter new batch"
                        oninput="syncChunked('new_batch')"
                    >

                    <input type="hidden" id="new_batch_chunk_count" name="new_batch_chunk_count" value="0">
                    <div id="new_batch_chunks_container"></div>

                </div>


                <!-- ROLL NUMBER (chunked Base64) -->

                <div class="form-group">

                    <label for="roll_number">
                        Roll Number <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="roll_number"
                        class="form-control"
                        value="<?= htmlspecialchars($roll_number, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter roll number"
                        oninput="syncChunked('roll_number')"
                        required
                    >

                    <input type="hidden" id="roll_number_chunk_count" name="roll_number_chunk_count" value="0">
                    <div id="roll_number_chunks_container"></div>

                </div>


                <!-- STUDENT NAME (chunked Base64) -->

                <div class="form-group">

                    <label for="student_name">
                        Student Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="student_name"
                        class="form-control"
                        value="<?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter student name"
                        oninput="syncChunked('student_name')"
                        required
                    >

                    <input type="hidden" id="student_name_chunk_count" name="student_name_chunk_count" value="0">
                    <div id="student_name_chunks_container"></div>

                </div>


                <!-- MAIL ID (chunked Base64, no validation) -->

                <div class="form-group">

                    <label for="mail_id">
                        Mail ID <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="mail_id"
                        class="form-control"
                        value="<?= htmlspecialchars($mail_id, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter mail ID"
                        oninput="syncChunked('mail_id')"
                        required
                    >

                    <input type="hidden" id="mail_id_chunk_count" name="mail_id_chunk_count" value="0">
                    <div id="mail_id_chunks_container"></div>

                </div>


                <!-- MENTOR 1 (chunked Base64) -->

                <div class="form-group">

                    <label for="mentor_1">Mentor 1</label>

                    <input
                        type="text"
                        id="mentor_1"
                        class="form-control"
                        value="<?= htmlspecialchars($mentor_1, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter mentor 1"
                        oninput="syncChunked('mentor_1')"
                    >

                    <input type="hidden" id="mentor_1_chunk_count" name="mentor_1_chunk_count" value="0">
                    <div id="mentor_1_chunks_container"></div>

                </div>


                <!-- MENTOR 2 (chunked Base64) -->

                <div class="form-group">

                    <label for="mentor_2">Mentor 2</label>

                    <input
                        type="text"
                        id="mentor_2"
                        class="form-control"
                        value="<?= htmlspecialchars($mentor_2, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Enter mentor 2"
                        oninput="syncChunked('mentor_2')"
                    >

                    <input type="hidden" id="mentor_2_chunk_count" name="mentor_2_chunk_count" value="0">
                    <div id="mentor_2_chunks_container"></div>

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn-confirm-delete"
                        style="background: #184C74;"
                    >
                        <i class="fas fa-save"></i>
                        Add Scholar
                    </button>

                    <a href="phd.php" class="btn-cancel">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

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
    syncChunked('new_batch');
    syncChunked('roll_number');
    syncChunked('student_name');
    syncChunked('mail_id');
    syncChunked('mentor_1');
    syncChunked('mentor_2');
    return true;
}

/* Toggle "New Batch" input visibility */
function toggleNewBatch(value) {

    const newBatchGroup = document.getElementById('newBatchGroup');
    const newBatchInput = document.getElementById('new_batch');

    if (value === '__new__') {

        newBatchGroup.style.display = 'block';
        newBatchInput.required = true;

    } else {

        newBatchGroup.style.display = 'none';
        newBatchInput.required = false;
        newBatchInput.value = '';
        syncChunked('new_batch');

    }

}

/* Initialize */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('new_batch');
    syncChunked('roll_number');
    syncChunked('student_name');
    syncChunked('mail_id');
    syncChunked('mentor_1');
    syncChunked('mentor_2');
});

</script>


<?php include 'footer.php'; ?>