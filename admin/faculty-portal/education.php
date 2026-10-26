<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phd   = reconstructChunks('phd');
    $phdc  = reconstructChunks('phdc');
    $mast  = reconstructChunks('mast');
    $mastc = reconstructChunks('mastc');
    $bach  = reconstructChunks('bach');
    $bachc = reconstructChunks('bachc');

    $stmtUp = $pdo->prepare("
        UPDATE faculty SET
            phd   = :phd,
            phdc  = :phdc,
            mast  = :mast,
            mastc = :mastc,
            bach  = :bach,
            bachc = :bachc
        WHERE id = :id
    ");

    $stmtUp->execute([
        ':phd'   => $phd,
        ':phdc'  => $phdc,
        ':mast'  => $mast,
        ':mastc' => $mastc,
        ':bach'  => $bach,
        ':bachc' => $bachc,
        ':id'    => $faculty_id
    ]);

    $success = 'Educational degrees and qualifications updated successfully!';

    // Refresh
    $stmtFac->execute([':id' => $faculty_id]);
    $currentFaculty = $stmtFac->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Education & Degrees</h1>
        <p>Manage your academic degrees and background. These are displayed with education icons on your inner faculty page.</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div style="background: #e6f4ea; color: #137333; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="POST" action="education.php" onsubmit="return prepareEducationSubmit();">

    <!-- Ph.D. -->
    <div class="section-box">
        <h3><i class="fa-solid fa-graduation-cap"></i> Ph.D. Degree</h3>
        <div style="display: grid; grid-template-columns: 240px 1fr; gap: 18px;">
            <div class="form-group">
                <label for="phd">Degree Title</label>
                <input type="text" id="phd" class="form-control" value="<?= htmlspecialchars($currentFaculty['phd']) ?>" placeholder="e.g. Ph.D.">
                <input type="hidden" name="phd_chunk_count" id="phd_chunk_count" value="0">
                <div id="phd_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="phdc">Department, University & Details</label>
                <input type="text" id="phdc" class="form-control" value="<?= htmlspecialchars($currentFaculty['phdc']) ?>" placeholder="e.g. in the Department of Information Display from Kyung Hee University, South Korea.">
                <input type="hidden" name="phdc_chunk_count" id="phdc_chunk_count" value="0">
                <div id="phdc_chunks_container"></div>
            </div>
        </div>
    </div>

    <!-- Master's Degree -->
    <div class="section-box">
        <h3><i class="fa-solid fa-graduation-cap"></i> Master's Degree</h3>
        <div style="display: grid; grid-template-columns: 240px 1fr; gap: 18px;">
            <div class="form-group">
                <label for="mast">Degree Title</label>
                <input type="text" id="mast" class="form-control" value="<?= htmlspecialchars($currentFaculty['mast']) ?>" placeholder="e.g. M.Tech / M.S.">
                <input type="hidden" name="mast_chunk_count" id="mast_chunk_count" value="0">
                <div id="mast_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="mastc">Field & University</label>
                <input type="text" id="mastc" class="form-control" value="<?= htmlspecialchars($currentFaculty['mastc']) ?>" placeholder="e.g. in Electrical Engineering from IIT Madras.">
                <input type="hidden" name="mastc_chunk_count" id="mastc_chunk_count" value="0">
                <div id="mastc_chunks_container"></div>
            </div>
        </div>
    </div>

    <!-- Bachelor's Degree -->
    <div class="section-box">
        <h3><i class="fa-solid fa-graduation-cap"></i> Bachelor's Degree</h3>
        <div style="display: grid; grid-template-columns: 240px 1fr; gap: 18px;">
            <div class="form-group">
                <label for="bach">Degree Title</label>
                <input type="text" id="bach" class="form-control" value="<?= htmlspecialchars($currentFaculty['bach']) ?>" placeholder="e.g. B.Tech">
                <input type="hidden" name="bach_chunk_count" id="bach_chunk_count" value="0">
                <div id="bach_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="bachc">Field & University</label>
                <input type="text" id="bachc" class="form-control" value="<?= htmlspecialchars($currentFaculty['bachc']) ?>" placeholder="e.g. in Materials Science and Engineering from IIT Kanpur.">
                <input type="hidden" name="bachc_chunk_count" id="bachc_chunk_count" value="0">
                <div id="bachc_chunks_container"></div>
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 40px;">
        <button type="submit" class="btn-portal-primary">
            <i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i> Save Education Details
        </button>
    </div>

</form>

<script>
function prepareEducationSubmit() {
    syncChunked('phd');
    syncChunked('phdc');
    syncChunked('mast');
    syncChunked('mastc');
    syncChunked('bach');
    syncChunked('bachc');
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    prepareEducationSubmit();
});
</script>

<?php include 'footer.php'; ?>
