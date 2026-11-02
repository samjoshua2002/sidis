<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Sub-pages (e.g. add/edit forms) can set $navActivePage before including
// this header to highlight their parent menu item.
if (!empty($navActivePage)) {
    $current_page = $navActivePage;
}


$navigation = [

    'Homepage' => [
        'hero.php' => 'Hero Section',
        'director-message.php' => 'Message from the Director',
        'head-message.php' => 'Message from Head',
        'counter.php' => 'Key Statistics / Counter',
        'news.php' => 'Latest News & Events',
        'announcements.php' => 'Announcements',
        'research.php' => 'Featured Research',
    ],

    'Admissions' => [
        'important_dates.php' => 'Important Dates',
        'how_to_apply.php' => 'How to Apply',
    ],

    'Organizational Structure' => [
        'committee.php' => 'Advisory Committee',
        'scc.php' => 'School Consultative Committee (SCC)',
        'guidelines.php' => 'Guidelines',
    ],

    'People' => [
        'faculty.php' => 'Faculty',
        'staff.php' => 'Staff',
        'ms.php' => 'M.S. Students',
        'phd.php' => 'Ph.D. Scholars',
        'mtech.php' => 'M.Tech Students',
    ],

    'Clusters' => [
        'clusters.php' => 'Clusters',
    ],

    'Careers' => [
        'faculty_recruitment.php' => 'Faculty Recruitment',
        'postdoc_recruitment.php' => 'Postdoc Recruitment',
        'staff_recruitment.php'   => 'Staff Recruitment',
    ],

    'Contact' => [
        'contact.php' => 'Contact Us',
    ]

];

/* Pending "Forgot password?" requests -> badge on Users & Faculty Logins */
$pendingResetRequests = 0;
try {
    $pendingResetRequests = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'faculty' AND reset_requested_at IS NOT NULL")->fetchColumn();
} catch (PDOException $e) {
    $pendingResetRequests = 0;   // column not migrated yet
}

/* Icon for each sidebar group (shown alone when the sidebar is minimized) */
$navIcons = [
    'Homepage'                 => 'fa-house-chimney',
    'Admissions'               => 'fa-calendar-check',
    'Organizational Structure' => 'fa-sitemap',
    'People'                   => 'fa-users',
    'Clusters'                 => 'fa-layer-group',
    'Careers'                  => 'fa-briefcase',
    'Contact'                  => 'fa-location-dot',
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <link rel="icon" type="image/png" href="../images/iitm-logo.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">

    <title>SIDiS Admin</title>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <script>
        /* Apply the saved sidebar state before the page paints (no flicker) */
        try {
            if (localStorage.getItem('sidis_admin_sidebar') === 'collapsed') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}
    </script>

    <style>
        /* =========================================================
           SIDEBAR — icons + collapsible (minimized = icons only)
        ========================================================= */
        .admin-sidebar {
            display: flex;
            flex-direction: column;
            transition: width .22s ease;
        }

        .admin-main {
            transition: margin-left .22s ease;
        }

        .admin-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .admin-nav::-webkit-scrollbar { width: 6px; }
        .admin-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .18); border-radius: 6px; }

        .sidebar-bottom {
            position: static !important;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .nav-ico {
            width: 18px;
            text-align: center;
            font-size: 15px;
            opacity: .9;
            flex-shrink: 0;
        }

        .nav-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-badge {
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        html.sb-collapsed .nav-link { position: relative; }

        html.sb-collapsed .nav-badge {
            position: absolute;
            top: 4px;
            right: 8px;
            min-width: 16px;
            height: 16px;
            font-size: 10px;
            padding: 0 4px;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, .12);
            margin: 10px 4px;
        }

        /* Collapse toggle in the top bar */
        .sb-toggle {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #184C74;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 15px;
            flex-shrink: 0;
            transition: background .2s ease, color .2s ease;
        }

        .sb-toggle:hover {
            background: #184C74;
            color: #fff;
        }

        .admin-header {
            gap: 0;
        }

        .logout-link {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* ---------- Minimized ---------- */
        html.sb-collapsed .admin-sidebar {
            width: 72px;
        }

        html.sb-collapsed .admin-main {
            margin-left: 72px;
        }

        html.sb-collapsed .sidebar-logo {
            padding: 18px 0;
            display: flex;
            justify-content: center;
        }

        html.sb-collapsed .sidebar-brand { gap: 0; }
        html.sb-collapsed .sidebar-brand img { width: 40px; height: 40px; }

        html.sb-collapsed .sidebar-brand h1,
        html.sb-collapsed .sidebar-logo p,
        html.sb-collapsed .nav-text,
        html.sb-collapsed .dropdown-arrow,
        html.sb-collapsed .admin-user,
        html.sb-collapsed .logout-text {
            display: none !important;
        }

        html.sb-collapsed .admin-nav {
            padding: 14px 10px;
        }

        html.sb-collapsed .nav-link,
        html.sb-collapsed .homepage-toggle {
            justify-content: center;
            padding: 12px 0;
        }

        html.sb-collapsed .nav-ico {
            font-size: 17px;
            width: auto;
        }

        html.sb-collapsed .homepage-submenu,
        html.sb-collapsed .homepage-submenu.open {
            display: none !important;
        }

        /* Show which group holds the current page */
        html.sb-collapsed .homepage-toggle.has-active {
            background: rgba(255, 255, 255, .14);
        }

        html.sb-collapsed .sidebar-bottom {
            padding: 14px 10px;
        }

        html.sb-collapsed .logout-link {
            padding: 10px 0;
        }

        @media (max-width: 768px) {
            html.sb-collapsed .admin-main { margin-left: 72px; }
        }
    </style>
</head>


<body>


    <!-- SIDEBAR -->

    <aside class="admin-sidebar">


        <!-- Logo -->

        <div class="sidebar-logo">

            <div class="sidebar-brand">

                <img src="../images/iitm-logo.png" alt="IITM Logo">

                <h1>
                    SIDiS
                </h1>

            </div>

            <p>
                Administration Panel
            </p>

        </div>


        <!-- Navigation -->

        <nav class="admin-nav">


            <!-- Dashboard -->

            <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" data-tip="Dashboard">
                <span class="nav-left">
                    <i class="fa-solid fa-gauge-high nav-ico"></i>
                    <span class="nav-text">Dashboard</span>
                </span>
            </a>


            <!-- Dropdowns -->

            <?php foreach ($navigation as $section => $pages): ?>

                <?php

                $section_open = array_key_exists($current_page, $pages);

                if ($section === 'Homepage' && in_array($current_page, ['counter.php', 'addcounter.php', 'editcounter.php'])) {
                    $section_open = true;
                }

                if ($section === 'Admissions' && in_array($current_page, ['important_dates.php', 'add_important_date.php', 'edit_important_date.php', 'how_to_apply.php', 'add_how_to_apply.php', 'edit_how_to_apply.php'])) {
                    $section_open = true;
                }

                $submenu_id = 'submenu-' . strtolower(
                    preg_replace('/[^a-zA-Z0-9]+/', '-', $section)
                );

                ?>


                <!-- Dropdown -->

                <button type="button" class="homepage-toggle <?= $section_open ? 'has-active' : '' ?>" data-target="<?= $submenu_id ?>" data-tip="<?= htmlspecialchars($section) ?>">

                    <span class="nav-left">
                        <i class="fa-solid <?= $navIcons[$section] ?? 'fa-folder' ?> nav-ico"></i>
                        <span class="nav-text"><?= htmlspecialchars($section) ?></span>
                    </span>

                    <span class="dropdown-arrow">
                        <i class="fa-solid fa-caret-down"></i>
                    </span>

                </button>


                <!-- Submenu -->

                <div class="homepage-submenu <?= $section_open ? 'open' : '' ?>" id="<?= $submenu_id ?>">

                    <?php foreach ($pages as $page => $pageTitle): ?>

                        <?php
                        $isActive = ($current_page === $page) ||
                            ($page === 'counter.php' && in_array($current_page, ['addcounter.php', 'editcounter.php'])) ||
                            ($page === 'important_dates.php' && in_array($current_page, ['add_important_date.php', 'edit_important_date.php'])) ||
                            ($page === 'how_to_apply.php' && in_array($current_page, ['add_how_to_apply.php', 'edit_how_to_apply.php']));
                        ?>

                        <a href="<?= htmlspecialchars($page) ?>" class="<?= $isActive ? 'active' : '' ?>">

                            <?= htmlspecialchars($pageTitle) ?>

                        </a>

                    <?php endforeach; ?>

                </div>


            <?php endforeach; ?>

            <div class="nav-divider"></div>

            <!-- Faculty Logins / Users -->
            <a href="users.php" class="nav-link <?= in_array($current_page, ['users.php', 'adduser.php', 'edituser.php']) ? 'active' : '' ?>" data-tip="Users & Faculty Logins">
                <span class="nav-left">
                    <i class="fa-solid fa-users-gear nav-ico"></i>
                    <span class="nav-text">Users &amp; Faculty Logins</span>
                </span>
                <?php if ($pendingResetRequests > 0): ?>
                    <span class="nav-badge" title="<?= $pendingResetRequests ?> password request(s)"><?= $pendingResetRequests ?></span>
                <?php endif; ?>
            </a>

            <!-- Settings -->
            <a href="settings.php" class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" data-tip="Settings">
                <span class="nav-left">
                    <i class="fa-solid fa-gear nav-ico"></i>
                    <span class="nav-text">Settings</span>
                </span>
            </a>

        </nav>


        <!-- USER SECTION -->

        <div class="sidebar-bottom">

            <div class="admin-user">

                Welcome,

                <strong>
                    <?= htmlspecialchars(
                        $_SESSION['admin_username'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>

            </div>


            <a href="logout.php" class="logout-link" data-tip="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="logout-text">Logout</span>
            </a>

        </div>


    </aside>



    <!-- MAIN CONTENT -->

    <div class="admin-main">


        <header class="admin-header">

            <button type="button" class="sb-toggle" id="sbToggle" aria-label="Minimize sidebar" title="Minimize sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h2>
                School of Interdisciplinary Studies
            </h2>

        </header>


        <main class="admin-content">


            <script>

                (function () {

                    var root = document.documentElement;
                    var KEY  = 'sidis_admin_sidebar';

                    /* Native tooltips only while minimized */
                    function syncTips() {
                        var collapsed = root.classList.contains('sb-collapsed');
                        document.querySelectorAll('.admin-sidebar [data-tip]').forEach(function (el) {
                            if (collapsed) el.setAttribute('title', el.dataset.tip);
                            else el.removeAttribute('title');
                        });
                        var btn = document.getElementById('sbToggle');
                        btn.title = collapsed ? 'Expand sidebar' : 'Minimize sidebar';
                        btn.setAttribute('aria-label', btn.title);
                        btn.innerHTML = collapsed
                            ? '<i class="fa-solid fa-angles-right"></i>'
                            : '<i class="fa-solid fa-bars"></i>';
                    }

                    function setCollapsed(on) {
                        root.classList.toggle('sb-collapsed', on);
                        try { localStorage.setItem(KEY, on ? 'collapsed' : 'expanded'); } catch (e) {}
                        syncTips();
                    }

                    document.getElementById('sbToggle').addEventListener('click', function () {
                        setCollapsed(!root.classList.contains('sb-collapsed'));
                    });

                    /* Group toggles: when minimized, expand the sidebar and open that group */
                    document.querySelectorAll('.homepage-toggle').forEach(function (toggle) {

                        toggle.addEventListener('click', function () {

                            var target = document.getElementById(this.dataset.target);

                            if (root.classList.contains('sb-collapsed')) {
                                setCollapsed(false);
                                document.querySelectorAll('.homepage-submenu').forEach(function (s) { s.classList.remove('open'); });
                                target.classList.add('open');
                                return;
                            }

                            document.querySelectorAll('.homepage-submenu').forEach(function (submenu) {
                                if (submenu !== target) {
                                    submenu.classList.remove('open');
                                }
                            });

                            target.classList.toggle('open');

                        });

                    });

                    syncTips();

                })();

            </script>
