<?php
include 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_type     = $_POST['page_type'] ?? 'inner';
    $name          = reconstructChunks('name');
    $lname         = reconstructChunks('lname');
    $honors        = reconstructChunks('honors');
    $designation   = reconstructChunks('designation');
    $department    = reconstructChunks('department');
    $office        = reconstructChunks('office');
    $phone         = reconstructChunks('phone');
    $email         = reconstructChunks('email');
    $personal_page = reconstructChunks('personal_page');
    $scopuslink    = reconstructChunks('scopuslink');
    $lablink       = reconstructChunks('lablink');
    $cvpdf         = reconstructChunks('cvpdf');

    if (empty($name)) {
        $error = 'Name is required.';
    } else {
        $imageName = $currentFaculty['image'];

        // Handle Photo Upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['photo']['tmp_name'];
            $fileOrig = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($fileOrig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $newFilename = 'faculty_' . time() . '_' . uniqid() . '.' . $ext;
                $targetPath = __DIR__ . '/../../images/faculty/' . $newFilename;
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $imageName = $newFilename;
                }
            } else {
                $error = 'Only JPG, PNG, and WebP images are allowed.';
            }
        }

        if (empty($error)) {
            $stmtUp = $pdo->prepare("
                UPDATE faculty SET
                    page_type     = :page_type,
                    name          = :name,
                    lname         = :lname,
                    honors        = :honors,
                    designation   = :designation,
                    department    = :department,
                    office        = :office,
                    phone         = :phone,
                    email         = :email,
                    personal_page = :personal_page,
                    scopuslink    = :scopuslink,
                    lablink       = :lablink,
                    cvpdf         = :cvpdf,
                    image         = :image
                WHERE id = :id
            ");

            $stmtUp->execute([
                ':page_type'     => $page_type,
                ':name'          => $name,
                ':lname'         => $lname,
                ':honors'        => $honors,
                ':designation'   => $designation,
                ':department'    => $department,
                ':office'        => $office,
                ':phone'         => $phone,
                ':email'         => $email,
                ':personal_page' => $personal_page,
                ':scopuslink'    => $scopuslink,
                ':lablink'       => $lablink,
                ':cvpdf'         => $cvpdf,
                ':image'         => $imageName,
                ':id'            => $faculty_id
            ]);

            $success = 'Profile and contact information updated successfully!';
            
            // Refresh
            $stmtFac->execute([':id' => $faculty_id]);
            $currentFaculty = $stmtFac->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Profile & Contact Details</h1>
        <p>Manage your identity, profile type choice, office location, contact numbers, and external links.</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div style="background: #e6f4ea; color: #137333; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="background: #fce8e6; color: #c5221f; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="profile.php" enctype="multipart/form-data" onsubmit="return prepareProfileSubmit();">

    <!-- Profile Type Choice -->
    <div class="section-box">
        <h3><i class="fa-solid fa-sliders"></i> Profile Page Option</h3>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">
            Choose what happens when visitors click your profile card on the SIDIS website faculty listing.
        </p>

        <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-bottom: 15px;">
            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; max-width: 400px;">
                <input type="radio" name="page_type" value="inner" id="typeInner" <?= $currentFaculty['page_type'] !== 'personal' ? 'checked' : '' ?> onchange="togglePageType()" style="margin-top: 3px;">
                <div>
                    <strong>SIDIS Inner Faculty Page (Recommended)</strong>
                    <div style="font-size: 13px; color: #64748b;">
                        Displays your full academic profile on SIDIS with accordions for biography, research, publications, and teaching.
                    </div>
                </div>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; max-width: 400px;">
                <input type="radio" name="page_type" value="personal" id="typePersonal" <?= $currentFaculty['page_type'] === 'personal' ? 'checked' : '' ?> onchange="togglePageType()" style="margin-top: 3px;">
                <div>
                    <strong>External Personal Website</strong>
                    <div style="font-size: 13px; color: #64748b;">
                        Redirects visitors directly to an external website or your home department profile link.
                    </div>
                </div>
            </label>
        </div>
    </div>

    <!-- Personal & Professional Identity -->
    <div class="section-box">
        <h3><i class="fa-solid fa-user"></i> Basic Identity & Designation</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="name">First / Full Name *</label>
                <input type="text" id="name" class="form-control" value="<?= htmlspecialchars($currentFaculty['name']) ?>" required>
                <input type="hidden" name="name_chunk_count" id="name_chunk_count" value="0">
                <div id="name_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="lname">Last Name / Suffix</label>
                <input type="text" id="lname" class="form-control" value="<?= htmlspecialchars($currentFaculty['lname']) ?>">
                <input type="hidden" name="lname_chunk_count" id="lname_chunk_count" value="0">
                <div id="lname_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="honors">Honors / Post-Nominals (e.g. FNAE, FNASc)</label>
                <input type="text" id="honors" class="form-control" value="<?= htmlspecialchars($currentFaculty['honors'] ?? '') ?>">
                <input type="hidden" name="honors_chunk_count" id="honors_chunk_count" value="0">
                <div id="honors_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" class="form-control" value="<?= htmlspecialchars($currentFaculty['designation']) ?>">
                <input type="hidden" name="designation_chunk_count" id="designation_chunk_count" value="0">
                <div id="designation_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" class="form-control" value="<?= htmlspecialchars($currentFaculty['department']) ?>">
                <input type="hidden" name="department_chunk_count" id="department_chunk_count" value="0">
                <div id="department_chunks_container"></div>
            </div>
        </div>
    </div>

    <!-- Contact & Location -->
    <div class="section-box">
        <h3><i class="fa-solid fa-location-dot"></i> Contact & Office Information</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="office">Office / Building Room No</label>
                <input type="text" id="office" class="form-control" value="<?= htmlspecialchars($currentFaculty['office']) ?>" placeholder="e.g. 521, ED Building">
                <input type="hidden" name="office_chunk_count" id="office_chunk_count" value="0">
                <div id="office_chunks_container"></div>
                <div class="form-hint">Displayed with the building icon on your profile.</div>
            </div>

            <div class="form-group">
                <label for="phone">Phone / Extension</label>
                <input type="text" id="phone" class="form-control" value="<?= htmlspecialchars($currentFaculty['phone']) ?>" placeholder="e.g. 044-2257-2303">
                <input type="hidden" name="phone_chunk_count" id="phone_chunk_count" value="0">
                <div id="phone_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" class="form-control" value="<?= htmlspecialchars($currentFaculty['email']) ?>" placeholder="username@iitm.ac.in">
                <input type="hidden" name="email_chunk_count" id="email_chunk_count" value="0">
                <div id="email_chunks_container"></div>
            </div>
        </div>
    </div>

    <!-- Profile Photo -->
    <div class="section-box">
        <h3><i class="fa-solid fa-image"></i> Profile Photo</h3>
        <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
            <?php 
            $photoPath = !empty($currentFaculty['image']) ? '../../images/faculty/' . $currentFaculty['image'] : '../../images/faculty/dummy.png';
            if (!file_exists(__DIR__ . '/' . $photoPath)) $photoPath = '../../images/faculty/dummy.png';
            ?>
            <img src="<?= htmlspecialchars($photoPath) ?>" alt="Current Photo" style="width: 110px; height: 125px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            
            <div style="flex: 1; min-width: 240px;">
                <label for="photo" style="display: block; font-weight: 600; margin-bottom: 6px;">Upload New Photo</label>
                <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%; max-width: 380px;">
                <div class="form-hint">Recommended aspect ratio: 4:5 (e.g. 600x750 px). Max size: 5MB.</div>
            </div>
        </div>
    </div>

    <!-- External Web Links -->
    <div class="section-box">
        <h3><i class="fa-solid fa-link"></i> Academic & Web Links</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="personal_page">Personal Website URL</label>
                <input type="text" id="personal_page" class="form-control" value="<?= htmlspecialchars($currentFaculty['personal_page']) ?>" placeholder="https://...">
                <input type="hidden" name="personal_page_chunk_count" id="personal_page_chunk_count" value="0">
                <div id="personal_page_chunks_container"></div>
                <div class="form-hint" id="personalPageHint">Required if you select 'External Personal Website' above.</div>
            </div>

            <div class="form-group">
                <label for="scopuslink">Google Scholar / Scopus Profile URL</label>
                <input type="text" id="scopuslink" class="form-control" value="<?= htmlspecialchars($currentFaculty['scopuslink']) ?>" placeholder="https://scholar.google.com/citations?user=...">
                <input type="hidden" name="scopuslink_chunk_count" id="scopuslink_chunk_count" value="0">
                <div id="scopuslink_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="lablink">Research Lab Website URL</label>
                <input type="text" id="lablink" class="form-control" value="<?= htmlspecialchars($currentFaculty['lablink']) ?>" placeholder="https://...">
                <input type="hidden" name="lablink_chunk_count" id="lablink_chunk_count" value="0">
                <div id="lablink_chunks_container"></div>
            </div>

            <div class="form-group">
                <label for="cvpdf">Curriculum Vitae (PDF Link or URL)</label>
                <input type="text" id="cvpdf" class="form-control" value="<?= htmlspecialchars($currentFaculty['cvpdf'] ?? $currentFaculty['cv']) ?>" placeholder="https://... or link to CV">
                <input type="hidden" name="cvpdf_chunk_count" id="cvpdf_chunk_count" value="0">
                <div id="cvpdf_chunks_container"></div>
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 40px;">
        <button type="submit" class="btn-portal-primary">
            <i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i> Save Profile Changes
        </button>
    </div>

</form>

<script>
function togglePageType() {
    const isPersonal = document.getElementById('typePersonal').checked;
    const personalInput = document.getElementById('personal_page');
    const hint = document.getElementById('personalPageHint');

    if (isPersonal) {
        personalInput.style.borderColor = '#184C74';
        personalInput.focus();
        hint.style.color = '#184C74';
        hint.style.fontWeight = '600';
    } else {
        personalInput.style.borderColor = '#cbd5e1';
        hint.style.color = '#64748b';
        hint.style.fontWeight = 'normal';
    }
}

function prepareProfileSubmit() {
    syncChunked('name');
    syncChunked('lname');
    syncChunked('honors');
    syncChunked('designation');
    syncChunked('department');
    syncChunked('office');
    syncChunked('phone');
    syncChunked('email');
    syncChunked('personal_page');
    syncChunked('scopuslink');
    syncChunked('lablink');
    syncChunked('cvpdf');
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    prepareProfileSubmit();
});
</script>

<?php include 'footer.php'; ?>
