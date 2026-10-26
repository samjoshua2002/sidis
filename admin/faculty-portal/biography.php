<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $about1         = reconstructChunks('about1');
    $resrch         = reconstructChunks('research_interests');
    $specialization = reconstructChunks('specialization');
    $resrchgrp      = reconstructChunks('research_group');

    // Legacy fallbacks
    if ($resrch === '') {
        $resrch = reconstructChunks('resrch');
    }
    if ($resrchgrp === '') {
        $resrchgrp = reconstructChunks('resrchgrp');
    }

    // Raw POST fallbacks if JS chunking was bypassed
    if ($about1 === '' && isset($_POST['about1_raw'])) {
        $about1 = trim($_POST['about1_raw']);
    }
    if ($resrch === '' && isset($_POST['research_interests_raw'])) {
        $resrch = trim($_POST['research_interests_raw']);
    } elseif ($resrch === '' && isset($_POST['resrch_raw'])) {
        $resrch = trim($_POST['resrch_raw']);
    }
    if ($specialization === '' && isset($_POST['specialization_raw'])) {
        $specialization = trim($_POST['specialization_raw']);
    }
    if ($resrchgrp === '' && isset($_POST['research_group_raw'])) {
        $resrchgrp = trim($_POST['research_group_raw']);
    } elseif ($resrchgrp === '' && isset($_POST['resrchgrp_raw'])) {
        $resrchgrp = trim($_POST['resrchgrp_raw']);
    }

    // Safety length truncation for single-line columns
    if (strlen($specialization) > 255) {
        $specialization = substr($specialization, 0, 255);
    }
    if (strlen($resrchgrp) > 500) {
        $resrchgrp = substr($resrchgrp, 0, 500);
    }

    try {
        $stmtUp = $pdo->prepare("
            UPDATE faculty SET
                about1         = :about1,
                resrch         = :resrch,
                specialization = :specialization,
                resrchgrp      = :resrchgrp
            WHERE id = :id
        ");

        $stmtUp->execute([
            ':about1'         => $about1,
            ':resrch'         => $resrch,
            ':specialization' => $specialization,
            ':resrchgrp'      => $resrchgrp,
            ':id'             => $faculty_id
        ]);

        $success = 'Biography and research interests updated successfully!';

        // Refresh faculty record
        $stmtFac->execute([':id' => $faculty_id]);
        $currentFaculty = $stmtFac->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Biography & Research Interests</h1>
        <p>Manage your academic biography, research domains, area of specialization, and research laboratory group.</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-circle-check" style="font-size:16px; color:#15803d;"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:16px; color:#dc2626;"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<form method="POST" action="" id="bioForm" onsubmit="return prepareBioSubmit();">

    <!-- Specialization & Research Group -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-atom" style="color:#184C74;"></i> Specialization & Research Group
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="specialization" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                    Area of Specialization
                </label>
                <input
                    type="text"
                    id="specialization"
                    class="form-control"
                    value="<?= htmlspecialchars($currentFaculty['specialization'] ?? '') ?>"
                    placeholder="e.g. Oxide Semiconductor Devices, Flexible Electronics"
                    oninput="syncChunkedField('specialization')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" name="specialization_chunk_count" id="specialization_chunk_count" value="0">
                <div id="specialization_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="research_group" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                    Research Group / Laboratory Name
                </label>
                <input
                    type="text"
                    id="research_group"
                    class="form-control"
                    value="<?= htmlspecialchars($currentFaculty['resrchgrp'] ?? '') ?>"
                    placeholder="e.g. Advanced Electronic Materials & Devices Lab"
                    oninput="syncChunkedField('research_group')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" name="research_group_chunk_count" id="research_group_chunk_count" value="0">
                <div id="research_group_chunks_container"></div>
            </div>
        </div>
    </div>

    <!-- Biography -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-book-open" style="color:#184C74;"></i> Academic Biography
        </h3>
        <div class="form-group">
            <label for="about1" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Biography Overview (Enter each paragraph or bullet on a new line)
            </label>
            <textarea
                id="about1"
                class="form-control"
                rows="8"
                placeholder="Enter biography details..."
                oninput="syncChunkedField('about1')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['about1'] ?? '') ?></textarea>
            <input type="hidden" name="about1_chunk_count" id="about1_chunk_count" value="0">
            <div id="about1_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line will be displayed as a distinct paragraph or bullet point on your inner page.
            </div>
        </div>
    </div>

    <!-- Research Interests -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-microscope" style="color:#184C74;"></i> Research Interests
        </h3>
        <div class="form-group">
            <label for="research_interests" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Research Areas & Topics (Enter each topic on a new line)
            </label>
            <textarea
                id="research_interests"
                class="form-control"
                rows="8"
                placeholder="e.g.&#10;Oxide Semiconductor Thin-Film Transistors (TFTs)&#10;Flexible and Transparent Electronics&#10;Neuromorphic Synaptic Devices and Hardware AI"
                oninput="syncChunkedField('research_interests')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['resrch'] ?? '') ?></textarea>
            <input type="hidden" name="research_interests_chunk_count" id="research_interests_chunk_count" value="0">
            <div id="research_interests_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line creates a bullet point in the Research Interests accordion.
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 40px;">
        <button type="submit" class="btn-portal-primary" style="background:#184C74; color:#fff; padding:12px 26px; border:none; border-radius:6px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 6px rgba(24,76,116,0.25);">
            <i class="fa-solid fa-floppy-disk"></i> Save Biography & Research
        </button>
    </div>

</form>

<script>
/* Reliable UTF-8 to Base64 Encoder with CRLF Normalization */
function b64EncodeUtf8(str) {
    if (!str) return '';
    // Normalize Windows CRLF to LF to avoid HTTP response splitting/firewall triggers
    const normalized = str.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    try {
        return btoa(encodeURIComponent(normalized).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode(parseInt(p1, 16));
        }));
    } catch (e) {
        return btoa(unescape(encodeURIComponent(normalized)));
    }
}

/* Split into small fixed-size chunks (150 chars for firewall compliance) */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Synchronize input/textarea value into chunked hidden inputs */
function syncChunkedField(fieldId) {
    const elem = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container = document.getElementById(fieldId + '_chunks_container');

    if (!elem || !countInput || !container) return;

    const base64 = b64EncodeUtf8(elem.value);
    const chunks = splitIntoChunks(base64, 150);

    countInput.value = chunks.length;
    container.innerHTML = '';

    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = fieldId + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }
}

function prepareBioSubmit() {
    syncChunkedField('specialization');
    syncChunkedField('research_group');
    syncChunkedField('about1');
    syncChunkedField('research_interests');
    return true;
}

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
    prepareBioSubmit();
});
</script>

<?php include 'footer.php'; ?>
