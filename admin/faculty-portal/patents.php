<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_patents'])) {
    $patent = reconstructChunks('patent');

    try {
        $stmtUp = $pdo->prepare("UPDATE faculty SET patent = :patent WHERE id = :id");
        $stmtUp->execute([
            ':patent' => $patent,
            ':id'     => $faculty_id
        ]);

        $success = 'Patents updated successfully!';
        $stmtFac->execute([':id' => $faculty_id]);
        $currentFaculty = $stmtFac->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Counts for tabs
$stmtJournalCount = $pdo->prepare("SELECT COUNT(*) FROM journalpub WHERE (id = :id OR faculty_id = :id)");
$stmtJournalCount->execute([':id' => $faculty_id]);
$journalCount = (int)$stmtJournalCount->fetchColumn();

$stmtConfCount = $pdo->prepare("SELECT COUNT(*) FROM confrncepub WHERE (id = :id OR faculty_id = :id)");
$stmtConfCount->execute([':id' => $faculty_id]);
$confCount = (int)$stmtConfCount->fetchColumn();

$stmtIndCount = $pdo->prepare("SELECT COUNT(*) FROM indpublication WHERE (id = :id OR faculty_id = :id)");
$stmtIndCount->execute([':id' => $faculty_id]);
$indCount = (int)$stmtIndCount->fetchColumn();
?>

<!-- Clean Page Header -->
<div class="content-header">
    <div class="content-header-title">
        <h1>Patents</h1>
        <p>Manage your registered, filed, and granted patents displayed under the Patents section of your inner profile.</p>
    </div>
</div>

<!-- Publications Segmented Tabs Navigation -->
<div class="pub-nav-tabs">
    <a href="journals.php" class="pub-nav-tab">
        <i class="fa-solid fa-book-open"></i>
        <span>Journal Articles</span>
        <?php if ($journalCount > 0): ?>
            <span class="pub-tab-badge"><?= $journalCount ?></span>
        <?php endif; ?>
    </a>
    <a href="conferences.php" class="pub-nav-tab">
        <i class="fa-solid fa-users-line"></i>
        <span>Conference Proceedings</span>
        <?php if ($confCount > 0): ?>
            <span class="pub-tab-badge"><?= $confCount ?></span>
        <?php endif; ?>
    </a>
    <a href="patents.php" class="pub-nav-tab active">
        <i class="fa-solid fa-stamp"></i>
        <span>Patents</span>
    </a>
    <a href="books.php" class="pub-nav-tab">
        <i class="fa-solid fa-book"></i>
        <span>Books & Chapters</span>
    </a>
    <a href="other-publications.php" class="pub-nav-tab">
        <i class="fa-solid fa-file-lines"></i>
        <span>Publications</span>
        <?php if ($indCount > 0): ?>
            <span class="pub-tab-badge"><?= $indCount ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="background: #e6f4ea; color: #137333; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display:flex; align-items:center; gap:10px; border:1px solid #bbf7d0;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background: #fce8e6; color: #c5221f; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display:flex; align-items:center; gap:10px; border:1px solid #fca5a5;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div class="section-box" style="max-width: 950px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="margin-bottom:0; padding-bottom:0; border-bottom:none;">
            <i class="fa-solid fa-stamp" style="color:#184C74; margin-right:8px;"></i> Patents Record
        </h3>
    </div>

    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #184C74; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:13px; color:#475569;">
        <strong><i class="fa-solid fa-circle-info" style="color:#184C74; margin-right:4px;"></i> Recommended Format:</strong>
        <p style="margin:4px 0 0; color:#64748b;">Enter each patent on a new line with title, patent number, inventors, filing/grant year, and status. Each line will render as an individual bullet item on your inner profile.</p>
    </div>

    <form method="POST" action="" id="patentForm" onsubmit="return submitPatentForm()">
        <input type="hidden" name="action_save_patents" value="1">

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="patent" style="display:block; font-weight:600; margin-bottom:8px; font-size:14px; color:#1e293b;">
                Patents (Granted, Registered, Filed)
            </label>
            <textarea
                id="patent"
                class="form-control"
                rows="10"
                oninput="syncChunked('patent')"
                placeholder="Example:
1. Ravindra Naik Bukke, et al., 'High-Performance Metal Oxide Thin-Film Transistor and Method Thereof', Indian Patent App. No. 202241012345, Filed 2022.
2. Ravindra Naik Bukke, 'Transparent Flexible Display Architecture', US Patent 11,234,567, Granted 2023."
                style="width:100%; padding:14px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:14px; line-height:1.6; font-family:inherit; color:#0f172a; resize:vertical;"
            ><?= htmlspecialchars($currentFaculty['patent'] ?? '') ?></textarea>
            
            <input type="hidden" id="patent_chunk_count" name="patent_chunk_count" value="0">
            <div id="patent_chunks_container"></div>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="background:#184C74; color:#fff; padding:12px 28px; border:none; border-radius:6px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 6px rgba(24,76,116,0.25);">
                <i class="fa-solid fa-floppy-disk"></i> Save Patents
            </button>
        </div>
    </form>
</div>

<script>
function submitPatentForm() {
    syncChunked('patent');
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    syncChunked('patent');
});
</script>

<?php include 'footer.php'; ?>
