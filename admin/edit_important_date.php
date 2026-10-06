<?php

/*
|--------------------------------------------------------------------------
| BOOTSTRAP - process BEFORE any output
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
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: important_dates.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| FETCH CURRENT RECORD
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("SELECT * FROM important_dates WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: important_dates.php');
        exit;
    }

} catch (PDOException $e) {

    header('Location: important_dates.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| FETCH DISTINCT SCHEDULES
|--------------------------------------------------------------------------
*/

try {

    $scheduleSql = "
        SELECT DISTINCT schedule_name
        FROM important_dates
        WHERE schedule_name IS NOT NULL
        AND TRIM(schedule_name) <> ''
        ORDER BY schedule_name ASC
    ";

    $scheduleStmt = $pdo->prepare($scheduleSql);
    $scheduleStmt->execute();

    $schedules = $scheduleStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {

    $schedules = [];
}


/*
|--------------------------------------------------------------------------
| INITIAL VALUES
|--------------------------------------------------------------------------
*/

$schedule_name  = $item['schedule_name'];
$new_schedule   = '';
$description    = $item['description'];
$date_schedule  = $item['date_schedule'];


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $schedule_name  = trim($_POST['schedule_name'] ?? '');
    $new_schedule   = reconstructChunks('new_schedule');
    $description    = reconstructChunks('description');
    $date_schedule  = reconstructChunks('date_schedule');

    /*
    | If "Add New Schedule" was selected
    */

    if ($schedule_name === '__new__') {

        if ($new_schedule === '') {
            $error = 'Please enter the new admission schedule name.';
        } else {
            $schedule_name = $new_schedule;
        }

    }


    /*
    | Validation
    */

    if ($error === '') {

        if ($schedule_name === '') {
            $error = 'Please select or enter an admission schedule.';
        } elseif ($description === '') {
            $error = 'Please enter the description.';
        } elseif ($date_schedule === '') {
            $error = 'Please enter the date / schedule details.';
        }

    }


    /*
    | Update Record
    */

    if ($error === '') {

        try {

            $sql = "
                UPDATE important_dates
                SET
                    schedule_name = ?,
                    description = ?,
                    date_schedule = ?
                WHERE id = ?
            ";

            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute([
                $schedule_name,
                $description,
                $date_schedule,
                $id
            ]);

            header('Location: important_dates.php?success=updated');
            exit;

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = 'Failed to update important date: ' . $e->getMessage();

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
            <h1>Edit Important Date</h1>
            <p>Update admission schedule event or milestone.</p>
        </div>

        <a href="important_dates.php" class="btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to Important Dates
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
                <h3>Edit Important Date</h3>
                <p>Modify the details and click Save Changes.</p>
            </div>
        </div>


        <div class="card-body" style="padding: 24px;">

            <form method="POST" action="" id="datesForm" onsubmit="return prepareSubmit()">


                <!-- Admission Schedule -->

                <div class="form-group">

                    <label for="schedule_name">
                        Admission Schedule / Batch <span class="required" style="color:red;">*</span>
                    </label>

                    <select
                        name="schedule_name"
                        id="schedule_name"
                        class="form-control"
                        required
                        onchange="toggleNewSchedule(this.value)"
                    >

                        <option value="">
                            -- Select Admission Schedule --
                        </option>

                        <?php foreach ($schedules as $existingSch): ?>
                            <option
                                value="<?= htmlspecialchars($existingSch, ENT_QUOTES, 'UTF-8') ?>"
                                <?= ($schedule_name === $existingSch) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($existingSch, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>

                        <option value="__new__" <?= ($schedule_name === '__new__') ? 'selected' : '' ?>>
                            + Add New Admission Schedule
                        </option>

                    </select>

                </div>


                <!-- New Schedule Name -->

                <div
                    class="form-group"
                    id="newScheduleGroup"
                    style="display: <?= ($schedule_name === '__new__') ? 'block' : 'none' ?>;"
                >

                    <label for="new_schedule">
                        New Admission Schedule Name <span class="required" style="color:red;">*</span>
                    </label>

                    <input
                        type="text"
                        id="new_schedule"
                        class="form-control"
                        value="<?= htmlspecialchars($new_schedule, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="e.g. Admission Schedule 2026-2027"
                        oninput="syncChunked('new_schedule')"
                    >

                    <input type="hidden" id="new_schedule_chunk_count" name="new_schedule_chunk_count" value="0">
                    <div id="new_schedule_chunks_container"></div>

                </div>


                <!-- Description -->

                <div class="form-group">

                    <label for="description">
                        Description <span class="required" style="color:red;">*</span>
                    </label>

                    <input
                        type="text"
                        id="description"
                        class="form-control"
                        value="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="e.g. Portal open for online application"
                        oninput="syncChunked('description')"
                        required
                    >

                    <input type="hidden" id="description_chunk_count" name="description_chunk_count" value="0">
                    <div id="description_chunks_container"></div>

                </div>


                <!-- Date / Schedule -->

                <div class="form-group">

                    <label for="date_schedule">
                        Date / Schedule <span class="required" style="color:red;">*</span>
                    </label>

                    <input
                        type="text"
                        id="date_schedule"
                        class="form-control"
                        value="<?= htmlspecialchars($date_schedule, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="e.g. 10 April 2026, 30th October 2026 @ 5:00 PM"
                        oninput="syncChunked('date_schedule')"
                        required
                    >

                    <input type="hidden" id="date_schedule_chunk_count" name="date_schedule_chunk_count" value="0">
                    <div id="date_schedule_chunks_container"></div>

                </div>


                <!-- Actions -->

                <div class="form-actions" style="margin-top: 25px; display: flex; gap: 12px;">

                    <button
                        type="submit"
                        class="btn-add"
                        style="border: none; cursor: pointer;"
                    >
                        Save Changes
                    </button>

                    <a
                        href="important_dates.php"
                        class="btn-delete"
                        style="text-decoration: none; display: inline-flex; align-items: center;"
                    >
                        Cancel
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


<script>

/* Encode UTF-8 to Base64 */
function b64EncodeUtf8(str) {
    try {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
            return String.fromCharCode('0x' + p1);
        }));
    } catch (e) {
        return btoa(str);
    }
}

/* Split into chunks */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Sync field into chunked hidden inputs */
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
    syncChunked('new_schedule');
    syncChunked('description');
    syncChunked('date_schedule');
    return true;
}

/* Toggle new schedule group */
function toggleNewSchedule(value) {

    const newGroup = document.getElementById('newScheduleGroup');
    const newInput = document.getElementById('new_schedule');

    if (value === '__new__') {
        newGroup.style.display = 'block';
        newInput.required = true;
    } else {
        newGroup.style.display = 'none';
        newInput.required = false;
        newInput.value = '';
        syncChunked('new_schedule');
    }
}

/* Initialize */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('new_schedule');
    syncChunked('description');
    syncChunked('date_schedule');
});

</script>


<?php include 'footer.php'; ?>
