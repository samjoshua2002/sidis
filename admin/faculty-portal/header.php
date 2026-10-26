<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config.php';

$faculty_id = (int)$_SESSION['faculty_id'];

// Fetch current faculty details
$stmtFac = $pdo->prepare("SELECT * FROM faculty WHERE id = :id LIMIT 1");
$stmtFac->execute([':id' => $faculty_id]);
$currentFaculty = $stmtFac->fetch(PDO::FETCH_ASSOC);

if (!$currentFaculty) {
    echo "Faculty record not found.";
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// Decode Helpers matching the admin chunking strategy
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

$facPhoto = !empty($currentFaculty['image']) ? '../../images/faculty/' . $currentFaculty['image'] : '../../images/faculty/dummy.png';
if (!file_exists(__DIR__ . '/' . $facPhoto)) {
    $facPhoto = '../../images/faculty/dummy.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../images/iitm-logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal - <?= htmlspecialchars($currentFaculty['name']) ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Modern Premium Sidebar Overrides */
        .admin-sidebar {
            width: 270px !important;
            background: linear-gradient(180deg, #102636 0%, #163e5c 60%, #184C74 100%) !important;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.12) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow-y: auto !important;
            z-index: 100 !important;
        }

        .sidebar-logo {
            padding: 22px 20px 20px 20px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sidebar-brand-wrapper img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
        }

        .sidebar-brand-text h1 {
            font-size: 20px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin: 0 !important;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-brand-text .portal-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #7dd3fc;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-top: 3px;
        }

        .admin-nav {
            padding: 18px 14px !important;
            flex: 1;
        }

        /* Refined Sidebar Links */
        .admin-nav .nav-link {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 12px !important;
            padding: 11px 15px !important;
            margin-bottom: 5px !important;
            border-radius: 8px !important;
            color: #cbd5e1 !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            background: transparent !important;
            border: 1px solid transparent !important;
            border-left: 3px solid transparent !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .admin-nav .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 15px;
            color: #94a3b8;
            transition: color 0.2s ease;
        }

        .admin-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .admin-nav .nav-link:hover i {
            color: #38bdf8 !important;
        }

        /* Clean, non-jarring active state */
        .admin-nav .nav-link.active {
            background: rgba(56, 189, 248, 0.14) !important;
            color: #ffffff !important;
            border-left: 3px solid #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.25) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .admin-nav .nav-link.active i {
            color: #38bdf8 !important;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 16px 4px;
        }

        .preview-inner-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 10px 14px !important;
            background: rgba(16, 185, 129, 0.12) !important;
            border: 1px solid rgba(16, 185, 129, 0.3) !important;
            border-radius: 8px !important;
            color: #6ee7b7 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }

        .preview-inner-btn:hover {
            background: rgba(16, 185, 129, 0.22) !important;
            color: #ffffff !important;
            border-color: #34d399 !important;
        }

        /* Bottom User Block */
        .sidebar-bottom {
            position: relative !important;
            bottom: auto !important;
            left: auto !important;
            width: 100% !important;
            padding: 16px 18px !important;
            background: rgba(0, 0, 0, 0.2) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            margin-top: auto !important;
        }

        .user-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .user-info-row img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-info-text {
            overflow: hidden;
        }

        .user-info-text .user-label {
            font-size: 10.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .user-info-text .user-name {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .btn-portal-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(239, 68, 68, 0.25);
            transition: all 0.2s ease;
        }

        .btn-portal-logout:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* Top Header Overrides */
        .admin-main {
            margin-left: 270px !important;
            min-height: 100vh !important;
            background: #f8fafc !important;
        }

        .admin-header {
            height: 70px !important;
            background: #ffffff !important;
            padding: 0 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-bottom: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 90 !important;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-title-box h2 {
            font-size: 17px !important;
            font-weight: 700 !important;
            color: #184C74 !important;
            margin: 0 !important;
            line-height: 1.25;
            letter-spacing: 0.2px;
        }

        .header-title-box .header-subtitle {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
        }

        .btn-live-view {
            padding: 7px 15px;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
        }

        .btn-live-view:hover {
            background: #15803d;
            color: #ffffff;
            border-color: #15803d;
            box-shadow: 0 2px 6px rgba(21, 128, 61, 0.25);
        }

        /* Publication Segmented Tabs */
        .pub-nav-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
            padding: 5px;
            background: #ffffff;
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            width: fit-content;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .pub-nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pub-nav-tab i {
            font-size: 14px;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .pub-nav-tab:hover {
            background: #f8fafc;
            color: #184C74;
        }

        .pub-nav-tab:hover i {
            color: #184C74;
        }

        .pub-nav-tab.active {
            background: #184C74;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(24, 76, 116, 0.25);
        }

        .pub-nav-tab.active i {
            color: #38bdf8;
        }

        .pub-tab-badge {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .pub-nav-tab:not(.active) .pub-tab-badge {
            background: #e2e8f0;
            color: #475569;
        }

        .sidebar-nav-heading {
            font-size: 10.5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 15px 5px;
            display: block;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 16px;
            border-left: 1px solid #e2e8f0;
        }

        .header-user-chip img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .header-user-meta {
            text-align: right;
        }

        .header-user-meta .chip-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .header-user-meta .chip-role {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Content Area Improvements */
        .admin-content {
            padding: 30px 35px 50px 35px !important;
        }

        .content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .content-header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }

        .content-header-title p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .section-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 26px 28px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .section-box h3 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-box h3 i {
            color: #184C74;
        }

        .form-hint {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 5px;
        }

        .form-control {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            color: #0f172a !important;
            transition: all 0.2s ease !important;
        }

        .form-control:focus {
            border-color: #184C74 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(24, 76, 116, 0.12) !important;
        }

        .btn-portal-primary {
            background-color: #184C74;
            color: #ffffff;
            border: none;
            padding: 11px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-portal-primary:hover {
            background-color: #123957;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(24, 76, 116, 0.2);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="sidebar-logo">
            <div class="sidebar-brand-wrapper">
                <img src="../../images/iitm-logo.png" alt="IITM Logo">
                <div class="sidebar-brand-text">
                    <h1>SIDiS</h1>
                    <span class="portal-subtitle">Faculty Portal</span>
                </div>
            </div>
        </div>

        <nav class="admin-nav">
            
            <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard Overview</span>
            </a>

            <a href="profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-id-card"></i>
                <span>Profile & Contact</span>
            </a>

            <a href="education.php" class="nav-link <?= $current_page === 'education.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Education & Degrees</span>
            </a>

            <a href="biography.php" class="nav-link <?= in_array($current_page, ['biography.php', 'bio-research.php'], true) ? 'active' : '' ?>">
                <i class="fa-solid fa-microscope"></i>
                <span>Biography & Research</span>
            </a>

            <a href="experience.php" class="nav-link <?= $current_page === 'experience.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-briefcase"></i>
                <span>Experience & Roles</span>
            </a>

            <a href="teaching.php" class="nav-link <?= $current_page === 'teaching.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teaching & Students</span>
            </a>

            <div class="sidebar-nav-heading">Publications & Research</div>

            <?php 
            $pubTypeParam = $_GET['type'] ?? '';
            $isJournalActive = in_array($current_page, ['journals.php', 'add-journal.php', 'edit-journal.php'], true) || (in_array($current_page, ['add-publication.php', 'edit-publication.php'], true) && $pubTypeParam === 'journal');
            $isConfActive = in_array($current_page, ['conferences.php', 'add-conference.php', 'edit-conference.php'], true) || (in_array($current_page, ['add-publication.php', 'edit-publication.php'], true) && $pubTypeParam === 'conference');
            $isPatentActive = ($current_page === 'patents.php');
            $isBookActive = ($current_page === 'books.php');
            $isOtherActive = in_array($current_page, ['other-publications.php'], true) || (in_array($current_page, ['add-publication.php', 'edit-publication.php'], true) && !in_array($pubTypeParam, ['journal', 'conference'], true));
            ?>

            <a href="journals.php" class="nav-link <?= $isJournalActive ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Journal Articles</span>
            </a>

            <a href="conferences.php" class="nav-link <?= $isConfActive ? 'active' : '' ?>">
                <i class="fa-solid fa-users-line"></i>
                <span>Conferences</span>
            </a>

            <a href="patents.php" class="nav-link <?= $isPatentActive ? 'active' : '' ?>">
                <i class="fa-solid fa-stamp"></i>
                <span>Patents</span>
            </a>

            <a href="books.php" class="nav-link <?= $isBookActive ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i>
                <span>Books & Chapters</span>
            </a>

            <a href="other-publications.php" class="nav-link <?= $isOtherActive ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines"></i>
                <span>Publications</span>
            </a>

            <div class="nav-divider"></div>

            <a href="../../innerfaculty.php?id=<?= $faculty_id ?>" target="_blank" class="preview-inner-btn">
                <span><i class="fa-solid fa-globe" style="margin-right: 8px;"></i> Live Inner Profile</span>
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i>
            </a>

        </nav>

        <div class="sidebar-bottom">
            <div class="user-info-row">
                <img src="<?= htmlspecialchars($facPhoto) ?>" alt="">
                <div class="user-info-text">
                    <span class="user-label">Faculty Account</span>
                    <span class="user-name" title="<?= htmlspecialchars($currentFaculty['name']) ?>"><?= htmlspecialchars($currentFaculty['name']) ?></span>
                </div>
            </div>
            <a href="logout.php" class="btn-portal-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <div class="admin-main">

        <header class="admin-header">
            <div class="header-left">
                <div class="header-title-box">
                    <h2>School of Interdisciplinary Studies</h2>
                    <span class="header-subtitle">Faculty Management Portal &bull; IIT Madras</span>
                </div>
            </div>

            <div class="header-right">
                <a href="../../innerfaculty.php?id=<?= $faculty_id ?>" target="_blank" class="btn-live-view">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Page
                </a>

                <div class="header-user-chip">
                    <div class="header-user-meta">
                        <div class="chip-name"><?= htmlspecialchars($currentFaculty['name']) ?></div>
                        <div class="chip-role"><?= htmlspecialchars($currentFaculty['designation'] ?: 'Assistant Professor') ?></div>
                    </div>
                    <img src="<?= htmlspecialchars($facPhoto) ?>" alt="">
                </div>
            </div>
        </header>

        <main class="admin-content">
