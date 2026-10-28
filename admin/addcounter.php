<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

/*
|--------------------------------------------------------------------------
| CHECK MAX 10 LIMIT
|--------------------------------------------------------------------------
*/
$totalCount = (int)$pdo->query("SELECT COUNT(*) FROM `counter`")->fetchColumn();
if ($totalCount >= 10) {
    header('Location: counter.php?error=limit_reached');
    exit;
}

$error = '';

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

// Next order default
$maxOrder = (int)$pdo->query("SELECT MAX(display_order) FROM `counter`")->fetchColumn();
$defaultOrder = $maxOrder + 1;

$heading = '';
$value = '';
$displayOrder = (string)$defaultOrder;
$status = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Re-check count on POST
    $currentCount = (int)$pdo->query("SELECT COUNT(*) FROM `counter`")->fetchColumn();
    if ($currentCount >= 10) {
        header('Location: counter.php?error=limit_reached');
        exit;
    }

    $heading = reconstructChunks('cheading');
    $value = reconstructChunks('cvalue');
    $displayOrder = $defaultOrder;
    $status = 1;

    $templateKey = !empty($_POST['template_key']) ? trim($_POST['template_key']) : null;

    if ($heading === '') {
        $error = 'Please enter a counter name / title.';
    } elseif ($value === '') {
        $error = 'Please enter a count / value.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `counter` (`cheading`, `cvalue`, `template_key`, `display_order`, `status`)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$heading, $value, !empty($templateKey) ? $templateKey : null, $displayOrder, $status]);
            header('Location: counter.php?success=added');
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| PREDEFINED LIVE SYSTEM TEMPLATES (Faculty, Students, Staff, Clusters)
|--------------------------------------------------------------------------
*/
$predefinedTemplates = [
    [
        'key'        => 'core_faculty',
        'title'      => 'Core Faculty',
        'query'      => "SELECT COUNT(*) FROM `faculty` WHERE `status` = 1 AND `top_order` = 1",
    ],
    [
        'key'        => 'faculty',
        'title'      => 'Faculty',
        'query'      => "SELECT COUNT(*) FROM `faculty` WHERE `status` = 1",
    ],
    [
        'key'        => 'staff',
        'title'      => 'Staff',
        'query'      => "SELECT COUNT(*) FROM `staff`",
    ],
    [
        'key'        => 'mtech',
        'title'      => 'MTech Students',
        'query'      => "SELECT COUNT(*) FROM `mtech_students`",
    ],
    [
        'key'        => 'ms',
        'title'      => 'M.S. Research Scholars',
        'query'      => "SELECT COUNT(*) FROM `ms`",
    ],
    [
        'key'        => 'phd',
        'title'      => 'Ph.D. Research Scholars',
        'query'      => "SELECT COUNT(*) FROM `phd_scholars`",
    ],
    [
        'key'        => 'clusters',
        'title'      => 'Clusters',
        'query'      => "SELECT COUNT(*) FROM `clusters` WHERE `status` = 1",
    ],
];

foreach ($predefinedTemplates as &$tpl) {
    try {
        $tpl['live_count'] = (int)$pdo->query($tpl['query'])->fetchColumn();
    } catch (Exception $e) {
        $tpl['live_count'] = 0;
    }
}
unset($tpl);

include 'header.php';
?>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Add Counter</h2>
            <p>Add a new key statistic to the homepage banner. (<?= $totalCount ?> / 10 added)</p>
        </div>
        <div>
            <a href="counter.php" class="btn-edit" style="background:#f1f5f9; color:#334155; text-decoration:none;">
                &larr; Back to Counters
            </a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card" style="max-width: 750px;">
        <div class="card-header">
            <div>
                <h3>Counter Details</h3>
                <p>Enter custom statistics or pick a live template below.</p>
            </div>
        </div>

        <!-- QUICK-FILL FROM LIVE DATABASE -->
        <div style="margin-bottom: 22px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
            <div style="font-size:12px; font-weight:700; color:#184C74; margin-bottom:8px;">
                Quick-Fill from Live System Data:
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ($predefinedTemplates as $t): ?>
                    <button type="button" 
                            onclick="fillTemplate(<?= htmlspecialchars(json_encode($t['title'])) ?>, <?= htmlspecialchars(json_encode((string)$t['live_count'])) ?>, <?= htmlspecialchars(json_encode($t['key'] ?? '')) ?>)"
                            style="background:#fff; border:1px solid #cbd5e1; border-radius:6px; padding:5px 12px; font-size:12px; cursor:pointer; color:#334155; display:inline-flex; align-items:center; gap:8px; transition:all 0.15s ease;"
                            onmouseover="this.style.borderColor='#184C74'; this.style.background='#f0f9ff';"
                            onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#fff';">
                        <strong style="font-weight:600;"><?= htmlspecialchars($t['title']) ?></strong>
                        <span style="background:#e0f2fe; color:#0369a1; padding:2px 7px; border-radius:4px; font-size:11px; font-weight:700;"><?= (int)$t['live_count'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="POST" action="addcounter.php" id="counterForm" onsubmit="return prepareSubmit()">
            <input type="hidden" id="template_key" name="template_key" value="">

            <!-- COUNTER VALUE (COUNT) -->
            <div class="form-group">
                <label for="cvalue">Counter Value (Count) *</label>
                <input
                    type="text"
                    id="cvalue"
                    class="form-control"
                    value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. 1107, 39, 452+, 829"
                    required
                    oninput="syncChunked('cvalue')"
                >
                <small style="color:#64748b; font-size:12px;">This is the bold number displayed prominently on the card.</small>
                <input type="hidden" id="cvalue_chunk_count" name="cvalue_chunk_count" value="0">
                <div id="cvalue_chunks_container"></div>
            </div>

            <!-- COUNTER HEADING (NAME) -->
            <div class="form-group">
                <label for="cheading">Counter Name (Title / Label) *</label>
                <input
                    type="text"
                    id="cheading"
                    class="form-control"
                    value="<?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Undergraduate Students, Faculty, Projects"
                    required
                    oninput="syncChunked('cheading')"
                >
                <small style="color:#64748b; font-size:12px;">This is the label displayed underneath the number.</small>
                <input type="hidden" id="cheading_chunk_count" name="cheading_chunk_count" value="0">
                <div id="cheading_chunks_container"></div>
            </div>

            <!-- BUTTONS -->
            <div class="form-actions" style="margin-top: 25px; display:flex; gap:12px;">
                <button type="submit" class="btn-add">
                    Save Counter
                </button>
                <a href="counter.php" class="btn-edit" style="background:#e2e8f0; color:#334155; text-decoration:none;">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>

<script>
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

function prepareSubmit() {
    ['cvalue', 'cheading'].forEach(f => syncChunked(f));
    return true;
}

function fillTemplate(title, value, key) {
    const titleInp = document.getElementById('cheading');
    const valInp = document.getElementById('cvalue');
    const keyInp = document.getElementById('template_key');
    if (titleInp && valInp) {
        titleInp.value = title;
        valInp.value = value;
        if (keyInp) keyInp.value = key || '';
        syncChunked('cheading');
        syncChunked('cvalue');
        titleInp.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    ['cvalue', 'cheading'].forEach(f => syncChunked(f));
});
</script>

<?php include 'footer.php'; ?>
