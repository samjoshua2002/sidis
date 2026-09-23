<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

$current_page = basename($_SERVER['PHP_SELF']);


$navigation = [

    'Homepage' => [
        'hero.php' => 'Hero Section',
        'director-message.php' => 'Message from the Director',
        'head-message.php' => 'Message from Head',
        'news.php' => 'Latest News & Events',
        'announcements.php' => 'Announcements',
        'research.php' => 'Featured Research',
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

    'Contact' => [
        'contact.php' => 'Contact Us',
    ]

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

            <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">

                <span>
                    Dashboard
                </span>

            </a>


            <!-- Dropdowns -->

            <?php foreach ($navigation as $section => $pages): ?>

                <?php

                $section_open = array_key_exists($current_page, $pages);

                $submenu_id = 'submenu-' . strtolower(
                    preg_replace('/[^a-zA-Z0-9]+/', '-', $section)
                );

                ?>


                <!-- Dropdown -->

                <button type="button" class="homepage-toggle" data-target="<?= $submenu_id ?>">

                    <span>
                        <?= htmlspecialchars($section) ?>
                    </span>

                    <span class="dropdown-arrow">
                        <i class="fa-solid fa-caret-down"></i>
                    </span>

                </button>


                <!-- Submenu -->

                <div class="homepage-submenu <?= $section_open ? 'open' : '' ?>" id="<?= $submenu_id ?>">

                    <?php foreach ($pages as $page => $pageTitle): ?>

                        <a href="<?= htmlspecialchars($page) ?>" class="<?= $current_page === $page ? 'active' : '' ?>">

                            <?= htmlspecialchars($pageTitle) ?>

                        </a>

                    <?php endforeach; ?>

                </div>


            <?php endforeach; ?>


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


            <a href="logout.php" class="logout-link">
                Logout
            </a>

        </div>


    </aside>



    <!-- MAIN CONTENT -->

    <div class="admin-main">


        <header class="admin-header">

            <h2>
                School of Interdisciplinary Studies
            </h2>

        </header>


        <main class="admin-content">


            <script>

                document.querySelectorAll('.homepage-toggle').forEach(function (toggle) {

                    toggle.addEventListener('click', function () {

                        const target = document.getElementById(this.dataset.target);

                        document.querySelectorAll('.homepage-submenu').forEach(function (submenu) {

                            if (submenu !== target) {
                                submenu.classList.remove('open');
                            }

                        });

                        target.classList.toggle('open');

                    });

                });

            </script>