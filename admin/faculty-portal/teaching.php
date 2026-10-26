<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crstaught   = reconstructChunks('crstaught');
    $student     = reconstructChunks('student');
    $studentlink = reconstructChunks('studentlink');

    // Fallbacks
    if ($crstaught === '' && isset($_POST['crstaught_raw'])) {
        $crstaught = trim($_POST['crstaught_raw']);
    }
    if ($student === '' && isset($_POST['student_raw'])) {
        $student = trim($_POST['student_raw']);
    }
    if ($studentlink === '' && isset($_POST['studentlink_raw'])) {
        $studentlink = trim($_POST['studentlink_raw']);
    }

    try {
        $stmtUp = $pdo->prepare("
            UPDATE faculty SET
                crstaught   = :crstaught,
                student     = :student,
                studentlink = :studentlink
            WHERE id = :id
        ");

        $stmtUp->execute([
            ':crstaught'   => $crstaught,
            ':student'     => $student,
            ':studentlink' => $studentlink,
            ':id'          => $faculty_id
        ]);

        $success = 'Courses taught and student guidance updated successfully!';

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
        <h1>Teaching & Students</h1>
        <p>Manage the list of courses you teach and your guided graduate students (M.S., Ph.D.) along with their profiles.</p>
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

<form method="POST" action="" id="teachingForm" onsubmit="return prepareTeachingSubmit();">

    <!-- Courses Taught -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-chalkboard-user" style="color:#184C74;"></i> Courses Taught
        </h3>
        <div class="form-group">
            <label for="crstaught" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                Course List (Enter each course on a new line)
            </label>
            <textarea
                id="crstaught"
                class="form-control"
                rows="7"
                placeholder="e.g.&#10;ID5100: Introduction to Interdisciplinary Engineering Systems&#10;ID6200: Advanced Semiconductor Device Fabrication"
                oninput="syncChunkedField('crstaught')"
                style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
            ><?= htmlspecialchars($currentFaculty['crstaught'] ?? '') ?></textarea>
            <input type="hidden" name="crstaught_chunk_count" id="crstaught_chunk_count" value="0">
            <div id="crstaught_chunks_container"></div>
            <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                <i class="fa-regular fa-lightbulb" style="color:#184C74;"></i> Each new line will be displayed as an individual course in the Courses Taught accordion.
            </div>
        </div>
    </div>

    <!-- Students Guided & Profiles -->
    <div class="section-box" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h3 style="margin-top:0; margin-bottom:14px; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-user-graduate" style="color:#184C74;"></i> Students Guided & Profile Links
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="student" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                    Student Names & Research Topic (1 per line)
                </label>
                <textarea
                    id="student"
                    class="form-control"
                    rows="7"
                    placeholder="e.g.&#10;A. Sharma (Ph.D.) - Oxide Transistors&#10;B. Kumar (M.S.) - Flexible Sensors"
                    oninput="syncChunkedField('student')"
                    style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
                ><?= htmlspecialchars($currentFaculty['student'] ?? '') ?></textarea>
                <input type="hidden" name="student_chunk_count" id="student_chunk_count" value="0">
                <div id="student_chunks_container"></div>
                <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                    Enter each student on a separate line.
                </div>
            </div>

            <div class="form-group">
                <label for="studentlink" style="display:block; font-weight:600; font-size:13.5px; color:#334155; margin-bottom:6px;">
                    Student Website / Profile Links (1 per line)
                </label>
                <textarea
                    id="studentlink"
                    class="form-control"
                    rows="7"
                    placeholder="e.g.&#10;https://scholar.google.com/...&#10;https://linkedin.com/in/..."
                    oninput="syncChunkedField('studentlink')"
                    style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6;"
                ><?= htmlspecialchars($currentFaculty['studentlink'] ?? '') ?></textarea>
                <input type="hidden" name="studentlink_chunk_count" id="studentlink_chunk_count" value="0">
                <div id="studentlink_chunks_container"></div>
                <div class="form-hint" style="font-size:12px; color:#64748b; margin-top:6px;">
                    Line 1 corresponds to Student 1, Line 2 to Student 2, etc. Leave line empty if no link.
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 40px;">
        <button type="submit" class="btn-portal-primary" style="background:#184C74; color:#fff; padding:12px 26px; border:none; border-radius:6px; font-weight:600; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 6px rgba(24,76,116,0.25);">
            <i class="fa-solid fa-floppy-disk"></i> Save Teaching & Students
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

/* Synchronize input/textarea value into chunked hidden inputs */
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

function prepareTeachingSubmit() {
    syncChunkedField('crstaught');
    syncChunkedField('student');
    syncChunkedField('studentlink');
    return true;
}

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
    prepareTeachingSubmit();
});
</script>

<?php include 'footer.php'; ?>
