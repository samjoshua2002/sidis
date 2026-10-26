<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Experience = reconstructChunks('Experience');
    $admnres    = reconstructChunks('admnres');
    $prfsnmem   = reconstructChunks('prfsnmem');

    // Extra fallback if JavaScript chunking was bypassed
    if ($Experience === '' && isset($_POST['Experience_raw'])) {
        $Experience = trim($_POST['Experience_raw']);
    }
    if ($admnres === '' && isset($_POST['admnres_raw'])) {
        $admnres = trim($_POST['admnres_raw']);
    }
    if ($prfsnmem === '' && isset($_POST['prfsnmem_raw'])) {
        $prfsnmem = trim($_POST['prfsnmem_raw']);
    }

    try {
        $stmtUp = $pdo->prepare("
            UPDATE faculty SET
                Experience = :Experience,
                precourse  = :precourse,
                admnres    = :admnres,
                prfsnmem   = :prfsnmem
            WHERE id = :id
        ");

        $stmtUp->execute([
            ':Experience' => $Experience,
            ':precourse'  => $Experience,
            ':admnres'    => $admnres,
            ':prfsnmem'   => $prfsnmem,
            ':id'         => $faculty_id
        ]);

        $success = 'Experience, administrative roles, and memberships updated successfully!';

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
        <h1>Experience & Roles</h1>
        <p>Manage your professional background, administrative positions held at IIT Madras or elsewhere, and professional memberships.</p>
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

<form method="POST" action="" id="expForm" onsubmit="return prepareExpSubmit();">

    <!-- Professional Experience -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-briefcase" style="color:#184C74;"></i> Professional Experience
        </h3>
        <div class="form-group">
            <label for="Experience" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Prior Positions & Roles (Enter each experience on a new line)
            </label>
            <textarea
                id="Experience"
                class="form-control"
                rows="8"
                placeholder="e.g.&#10;Postdoctoral Research Fellow, Kyung Hee University (2020-2022)&#10;Visiting Scholar, National Institute of Materials Science (2019)"
                oninput="syncChunkedField('Experience')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['Experience'] ?: $currentFaculty['precourse']) ?></textarea>
            <input type="hidden" name="Experience_chunk_count" id="Experience_chunk_count" value="0">
            <div id="Experience_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line creates a separate bullet point in the Professional Experience accordion.
            </div>
        </div>
    </div>

    <!-- Administrative Responsibilities -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-sitemap" style="color:#184C74;"></i> Administrative Responsibilities
        </h3>
        <div class="form-group">
            <label for="admnres" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Roles & Committee Duties (Enter each role on a new line)
            </label>
            <textarea
                id="admnres"
                class="form-control"
                rows="7"
                placeholder="e.g.&#10;Faculty Advisor, SIDIS M.Tech Program (2023 - Present)&#10;Member, Department Consultative Committee"
                oninput="syncChunkedField('admnres')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['admnres'] ?? '') ?></textarea>
            <input type="hidden" name="admnres_chunk_count" id="admnres_chunk_count" value="0">
            <div id="admnres_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line creates a separate bullet point in Administrative Responsibilities.
            </div>
        </div>
    </div>

    <!-- Professional Membership -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-certificate" style="color:#184C74;"></i> Professional Memberships
        </h3>
        <div class="form-group">
            <label for="prfsnmem" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Societies & Professional Bodies (Enter each membership on a new line)
            </label>
            <textarea
                id="prfsnmem"
                class="form-control"
                rows="6"
                placeholder="e.g.&#10;Senior Member, IEEE Electron Devices Society (EDS)&#10;Member, Materials Research Society of India (MRSI)"
                oninput="syncChunkedField('prfsnmem')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['prfsnmem'] ?? '') ?></textarea>
            <input type="hidden" name="prfsnmem_chunk_count" id="prfsnmem_chunk_count" value="0">
            <div id="prfsnmem_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line creates a separate bullet point in Professional Memberships.
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 40px;">
        <button type="submit" class="btn-portal-primary" style="background:#184C74; color:#fff; padding:12px 26px; border:none; border-radius:6px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 6px rgba(24,76,116,0.25);">
            <i class="fa-solid fa-floppy-disk"></i> Save Experience Details
        </button>
    </div>

</form>

<script>
/* Reliable UTF-8 to Base64 Encoder */
function b64EncodeUtf8(str) {
    if (!str) return '';
    try {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode(parseInt(p1, 16));
        }));
    } catch (e) {
        return btoa(unescape(encodeURIComponent(str)));
    }
}

/* Split into fixed-size chunks (200 characters) */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Synchronize textarea value into chunked hidden inputs */
function syncChunkedField(fieldId) {
    const elem = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container = document.getElementById(fieldId + '_chunks_container');

    if (!elem || !countInput || !container) return;

    const base64 = b64EncodeUtf8(elem.value);
    const chunks = splitIntoChunks(base64, 200);

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

function prepareExpSubmit() {
    syncChunkedField('Experience');
    syncChunkedField('admnres');
    syncChunkedField('prfsnmem');
    return true;
}

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
    prepareExpSubmit();
});
</script>

<?php include 'footer.php'; ?>
