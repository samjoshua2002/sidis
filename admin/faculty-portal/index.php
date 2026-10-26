<?php
include 'header.php';

// Quick stats
$pubCount = $pdo->query("SELECT COUNT(*) FROM indpublication WHERE id = $faculty_id OR faculty_id = $faculty_id")->fetchColumn();
$journalCount = $pdo->query("SELECT COUNT(*) FROM journalpub WHERE id = $faculty_id OR faculty_id = $faculty_id")->fetchColumn();
$confCount = $pdo->query("SELECT COUNT(*) FROM confrncepub WHERE id = $faculty_id OR faculty_id = $faculty_id")->fetchColumn();
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Welcome, <?= htmlspecialchars($currentFaculty['name']) ?>!</h1>
        <p>This is your personal SIDIS faculty portal. You can manage your profile, educational degrees, research biography, courses taught, and publications.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <div class="section-box" style="margin-bottom: 0;">
        <div style="display: flex; gap: 18px; align-items: center;">
            <?php 
            $img = !empty($currentFaculty['image']) ? '../../images/faculty/' . $currentFaculty['image'] : '../../images/faculty/dummy.png';
            if (!file_exists(__DIR__ . '/' . $img)) $img = '../../images/faculty/dummy.png';
            ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="" style="width: 75px; height: 85px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;">
            <div>
                <h3 style="margin: 0; padding: 0; border: none; font-size: 19px;">
                    <?= htmlspecialchars($currentFaculty['name'] . (!empty($currentFaculty['lname']) ? ' ' . $currentFaculty['lname'] : '')) ?>
                </h3>
                <div style="color: #184C74; font-weight: 600; font-size: 15px; margin-top: 4px;">
                    <?= htmlspecialchars($currentFaculty['designation']) ?>
                </div>
                <div style="color: #64748b; font-size: 13px; margin-top: 2px;">
                    <?= htmlspecialchars($currentFaculty['department']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="section-box" style="margin-bottom: 0;">
        <h3 style="margin: 0 0 10px 0; padding: 0; border: none; font-size: 16px; color: #64748b;">Current Profile Type</h3>
        <?php if ($currentFaculty['page_type'] === 'personal' && !empty($currentFaculty['personal_page']) && $currentFaculty['personal_page'] !== '#'): ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> External Personal Page
                </span>
            </div>
            <p style="font-size: 13px; color: #64748b; margin-top: 8px;">
                Visitors clicking your card on the faculty page navigate directly to: <br>
                <a href="<?= htmlspecialchars($currentFaculty['personal_page']) ?>" target="_blank" style="color: #184C74; font-weight: 500; word-break: break-all;">
                    <?= htmlspecialchars($currentFaculty['personal_page']) ?>
                </a>
            </p>
        <?php else: ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge" style="background: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                    <i class="fa-solid fa-circle-check"></i> SIDIS Inner Faculty Page Active
                </span>
            </div>
            <p style="font-size: 13px; color: #64748b; margin-top: 8px;">
                Your custom inner faculty page is published on the SIDIS website.<br>
                <a href="../../innerfaculty.php?id=<?= $faculty_id ?>" target="_blank" style="color: #184C74; font-weight: 600;">
                    Click here to preview your public inner page &rarr;
                </a>
            </p>
        <?php endif; ?>
        <div style="margin-top: 10px;">
            <a href="profile.php" style="font-size: 13px; color: #184C74; text-decoration: underline;">Change profile type in Profile settings &rarr;</a>
        </div>
    </div>

    <div class="section-box" style="margin-bottom: 0;">
        <h3 style="margin: 0 0 10px 0; padding: 0; border: none; font-size: 16px; color: #64748b;">Publication Summary</h3>
        <div style="display: flex; gap: 20px; margin-top: 10px;">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #184C74;"><?= (int)$pubCount ?></div>
                <div style="font-size: 12px; color: #64748b;">General Pubs</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #184C74;"><?= (int)$journalCount ?></div>
                <div style="font-size: 12px; color: #64748b;">Journals</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #184C74;"><?= (int)$confCount ?></div>
                <div style="font-size: 12px; color: #64748b;">Conferences</div>
            </div>
        </div>
        <div style="margin-top: 14px;">
            <a href="journals.php" style="font-size: 13px; color: #184C74; text-decoration: underline;">Manage Publications &rarr;</a>
        </div>
    </div>

</div>

<!-- Quick Navigation Cards -->
<div class="section-box">
    <h3><i class="fa-solid fa-list-check"></i> Manage Your Sections</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px;">
        
        <a href="profile.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-id-card" style="color: #184C74; margin-right: 8px;"></i> Profile & Contact</div>
            <div style="font-size: 13px; color: #64748b;">Name, photo, office room, phone, external links, profile page type.</div>
        </a>

        <a href="education.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-graduation-cap" style="color: #184C74; margin-right: 8px;"></i> Education & Degrees</div>
            <div style="font-size: 13px; color: #64748b;">Bachelor's, Master's, Ph.D. degrees and universities.</div>
        </a>

        <a href="biography.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-microscope" style="color: #184C74; margin-right: 8px;"></i> Biography & Research</div>
            <div style="font-size: 13px; color: #64748b;">Short bio, research interests, area of specialization, lab group.</div>
        </a>

        <a href="experience.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-briefcase" style="color: #184C74; margin-right: 8px;"></i> Experience & Roles</div>
            <div style="font-size: 13px; color: #64748b;">Professional background, administrative roles, memberships.</div>
        </a>

        <a href="teaching.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-chalkboard-user" style="color: #184C74; margin-right: 8px;"></i> Teaching & Students</div>
            <div style="font-size: 13px; color: #64748b;">Courses taught, graduate student guidance & links.</div>
        </a>

        <a href="journals.php" style="display: block; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s ease; background: #f8fafc;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;"><i class="fa-solid fa-book-open" style="color: #184C74; margin-right: 8px;"></i> Publications & Scholarly Works</div>
            <div style="font-size: 13px; color: #64748b;">Journals, conferences, patents, books, and reports.</div>
        </a>

    </div>
</div>

<?php include 'footer.php'; ?>
