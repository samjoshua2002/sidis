<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: counter.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `counter` WHERE id = ?");
$stmt->execute([$id]);
$counter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$counter) {
    header('Location: counter.php');
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

$heading = $counter['cheading'];
$value = $counter['cvalue'];
$displayOrder = (string)$counter['display_order'];
$status = (string)$counter['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heading = reconstructChunks('cheading');
    if ($heading === '' && isset($_POST['cheading'])) $heading = trim($_POST['cheading']);
    $value = reconstructChunks('cvalue');
    if ($value === '' && isset($_POST['cvalue'])) $value = trim($_POST['cvalue']);
    $autoSync = isset($_POST['auto_sync']) && $_POST['auto_sync'] === '1';

    if ($heading === '') {
        $error = 'Please enter a counter name / title.';
    } elseif ($value === '') {
        $error = 'Please enter a count / value.';
    } else {
        try {
            if (!$autoSync) {
                // User unchecked Auto-Sync: convert to MANUAL and disconnect from template!
                $updateStmt = $pdo->prepare("
                    UPDATE `counter`
                    SET `cheading` = ?, `cvalue` = ?, `template_key` = NULL
                    WHERE `id` = ?
                ");
                $updateStmt->execute([$heading, $value, $id]);
            } else {
                // Auto-Sync is checked: preserve or re-link template
                $tplKey = !empty($counter['template_key']) ? $counter['template_key'] : null;
                if (empty($tplKey)) {
                    $cName = strtolower(trim($heading));
                    if ($cName === 'core faculty') $tplKey = 'core_faculty';
                    elseif (in_array($cName, ['faculty', 'all faculty', 'total faculty'])) $tplKey = 'faculty';
                    elseif ($cName === 'staff') $tplKey = 'staff';
                    elseif (in_array($cName, ['mtech students', 'm.tech students', 'mtech'])) $tplKey = 'mtech';
                    elseif (in_array($cName, ['m.s. research scholars', 'ms research scholars', 'ms students', 'm.s. students'])) $tplKey = 'ms';
                    elseif (in_array($cName, ['ph.d. research scholars', 'phd research scholars', 'ph.d. scholars', 'phd scholars'])) $tplKey = 'phd';
                    elseif (in_array($cName, ['clusters', 'interdisciplinary clusters', 'research clusters'])) $tplKey = 'clusters';
                }
                $updateStmt = $pdo->prepare("
                    UPDATE `counter`
                    SET `cheading` = ?, `cvalue` = ?, `template_key` = ?
                    WHERE `id` = ?
                ");
                $updateStmt->execute([$heading, $value, $tplKey, $id]);
            }
            header('Location: counter.php?success=updated');
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Edit Counter</h2>
            <p>Modify the statistics value or name for this item.</p>
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

    <div class="dashboard-card" style="max-width: 700px;">
        <div class="card-header">
            <div>
                <h3>Counter Details</h3>
                <p>Editing counter #<?= $id ?>: <strong><?= htmlspecialchars($counter['cheading'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            </div>
        </div>

        <form method="POST" action="editcounter.php?id=<?= $id ?>" id="counterForm" onsubmit="return prepareSubmit()">
            <input type="hidden" name="id" value="<?= $id ?>">

            <?php $isTemplate = !empty($counter['template_key']); ?>

            <div id="type_info_card" style="margin-bottom: 20px; padding: 12px 16px; background: <?= $isTemplate ? '#f0f9ff' : '#f8fafc' ?>; border: 1px solid <?= $isTemplate ? '#bae6fd' : '#e2e8f0' ?>; border-radius: 8px; font-size: 13px; color: <?= $isTemplate ? '#0369a1' : '#475569' ?>; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <div>
                    <strong id="type_badge_title" style="display: flex; align-items: center; gap: 6px;">
                        <?php if ($isTemplate): ?>
                            <i class="fa-solid fa-arrows-rotate"></i> Type: Template (Auto-Sync)
                        <?php else: ?>
                            <i class="fa-solid fa-pen"></i> Type: Manual
                        <?php endif; ?>
                    </strong>
                    <div id="type_badge_desc" style="font-size: 12px; margin-top: 3px;">
                        <?= $isTemplate ? 'This counter automatically updates from the live database. Count input is muted.' : 'Manual mode: You can enter any custom count and title.' ?>
                    </div>
                </div>

                <label style="font-size: 12px; font-weight: 600; color: #1e293b; margin: 0; cursor: pointer; display: flex; align-items: center; gap: 6px; background: #ffffff; padding: 6px 12px; border-radius: 4px; border: 1px solid #cbd5e1;">
                    <input type="checkbox" id="auto_sync" name="auto_sync" value="1" <?= $isTemplate ? 'checked' : '' ?> onchange="toggleAutoSync(this.checked)">
                    <span id="auto_sync_label"><?= $isTemplate ? 'Auto-Sync Active (Template)' : 'Enable Auto-Sync (Template)' ?></span>
                </label>
            </div>

            <!-- COUNTER VALUE (COUNT) -->
            <div class="form-group">
                <label for="cvalue">Counter Value (Count) *</label>
                <input
                    type="text"
                    id="cvalue"
                    name="cvalue"
                    class="form-control"
                    value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. 1107, 39, 452+, 829"
                    required
                    <?= $isTemplate ? 'readonly style="background:#f1f5f9; color:#64748b; cursor:not-allowed;"' : '' ?>
                    oninput="syncChunked('cvalue')"
                >
                <small id="cvalue_helper" style="display:block; margin-top:4px; font-size:12px;">
                    <?php if ($isTemplate): ?>
                        <span style="color:#0284c7;"><i class="fa-solid fa-lock"></i> Auto-synced from live database. Uncheck box above to edit count.</span>
                    <?php else: ?>
                        <span style="color:#64748b;">This is the bold number displayed prominently on the card.</span>
                    <?php endif; ?>
                </small>
                <input type="hidden" id="cvalue_chunk_count" name="cvalue_chunk_count" value="0">
                <div id="cvalue_chunks_container"></div>
            </div>

            <!-- COUNTER HEADING (NAME) -->
            <div class="form-group">
                <label for="cheading">Counter Name (Title / Label) *</label>
                <input
                    type="text"
                    id="cheading"
                    name="cheading"
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
                    Update Counter
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

const initialTemplateValue = <?= json_encode((string)$counter['cvalue']) ?>;
const isOriginallyTemplate = <?= $isTemplate ? 'true' : 'false' ?>;

function toggleAutoSync(isChecked) {
    const valInput = document.getElementById('cvalue');
    const helper = document.getElementById('cvalue_helper');
    const label = document.getElementById('auto_sync_label');
    const badgeType = document.getElementById('type_badge_title');
    const typeDesc = document.getElementById('type_badge_desc');
    const typeCard = document.getElementById('type_info_card');

    if (!valInput) return;

    if (isChecked) {
        // Mute input
        valInput.readOnly = true;
        valInput.style.background = '#f1f5f9';
        valInput.style.color = '#64748b';
        valInput.style.cursor = 'not-allowed';
        if (isOriginallyTemplate) {
            valInput.value = initialTemplateValue;
            syncChunked('cvalue');
        }
        if (label) label.textContent = 'Auto-Sync Active (Template)';
        if (badgeType) badgeType.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Type: Template (Auto-Sync)';
        if (typeDesc) typeDesc.textContent = 'This counter automatically updates from the live database. Count input is muted.';
        if (typeCard) {
            typeCard.style.background = '#f0f9ff';
            typeCard.style.borderColor = '#bae6fd';
            typeCard.style.color = '#0369a1';
        }
        if (helper) {
            helper.innerHTML = '<span style="color:#0284c7;"><i class="fa-solid fa-lock"></i> Auto-synced from live database. Uncheck box above to edit count.</span>';
        }
    } else {
        // Unmute input
        valInput.readOnly = false;
        valInput.style.background = '#ffffff';
        valInput.style.color = '#1e293b';
        valInput.style.cursor = 'text';
        if (label) label.textContent = 'Auto-Sync Disabled (Manual)';
        if (badgeType) badgeType.innerHTML = '<i class="fa-solid fa-pen"></i> Type: Manual';
        if (typeDesc) typeDesc.textContent = 'Auto-sync is disabled. You can now customize the value and title.';
        if (typeCard) {
            typeCard.style.background = '#f8fafc';
            typeCard.style.borderColor = '#e2e8f0';
            typeCard.style.color = '#475569';
        }
        if (helper) {
            helper.innerHTML = '<span style="color:#059669; font-weight:600;"><i class="fa-solid fa-unlock"></i> Manual Mode: You can now enter a custom count.</span>';
        }
        valInput.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    ['cvalue', 'cheading'].forEach(f => syncChunked(f));
});
</script>

<?php include 'footer.php'; ?>
