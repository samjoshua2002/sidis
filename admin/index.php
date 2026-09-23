<?php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$facultyCount = 0;
$staffCount = 0;
$msCount = 0;
$phdCount = 0;
$mtechCount = 0;
$clusterCount = 0;


/*
|--------------------------------------------------------------------------
| SAFE COUNT FUNCTION
|--------------------------------------------------------------------------
*/

function getTableCount($pdo, $table)
{
    try {

        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");

        return (int) $stmt->fetchColumn();

    } catch (PDOException $e) {

        return 0;
    }
}


/*
|--------------------------------------------------------------------------
| FETCH COUNTS
|--------------------------------------------------------------------------
*/

$facultyCount = getTableCount($pdo, 'faculty');

$staffCount = getTableCount($pdo, 'staff');

$msCount = getTableCount($pdo, 'ms');

$phdCount = getTableCount($pdo, 'phd_scholars');

$mtechCount = getTableCount($pdo, 'mtech_students');

$clusterCount = getTableCount($pdo, 'clusters');

?>

<style>

/* =========================================================
   DASHBOARD
========================================================= */

.dashboard-page {
    width: 100%;
}


/* =========================================================
   WELCOME
========================================================= */

.dashboard-welcome {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 25px;

    margin-bottom: 25px;

    padding: 25px 28px;

    background: #fff;

    border: 1px solid #e8edf0;

    border-radius: 12px;

    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);

}


.dashboard-welcome-content h2 {

    margin: 0 0 7px;

    color: #184C74;

    font-size: 25px;

    font-weight: 700;

}


.dashboard-welcome-content p {

    margin: 0;

    color: #6b7280;

    font-size: 14px;

    line-height: 1.6;

}


.dashboard-welcome-badge {

    flex-shrink: 0;

    padding: 10px 16px;

    border-radius: 8px;

    background: #f4f8fb;

    color: #184C74;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.dashboard-section {

    margin-bottom: 28px;

}


.dashboard-section-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 15px;

}


.dashboard-section-header h3 {

    margin: 0;

    color: #184C74;

    font-size: 18px;

    font-weight: 700;

}


.dashboard-section-header p {

    margin: 4px 0 0;

    color: #777;

    font-size: 13px;

}


/* =========================================================
   STAT CARDS
========================================================= */

.dashboard-stats {

    display: grid;

    grid-template-columns:
        repeat(6, minmax(130px, 1fr));

    gap: 15px;

}


.stat-card {

    display: flex;

    align-items: center;

    gap: 13px;

    min-height: 92px;

    padding: 17px;

    background: #fff;

    border: 1px solid #e8edf0;

    border-radius: 10px;

    text-decoration: none;

    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.045);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


.stat-card:hover {

    transform: translateY(-3px);

    border-color: #184C74;

    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);

}


.stat-icon {

    width: 42px;

    height: 42px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    background: #f1f6fa;

    color: #184C74;

    font-size: 17px;

}


.stat-content strong {

    display: block;

    margin-bottom: 3px;

    color: #184C74;

    font-size: 21px;

    line-height: 1;

}


.stat-content span {

    color: #777;

    font-size: 12px;

}


/* =========================================================
   QUICK ACCESS
========================================================= */

.quick-access-grid {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 16px;

}


.quick-card {

    position: relative;

    display: block;

    padding: 20px;

    background: #fff;

    border: 1px solid #e8edf0;

    border-radius: 10px;

    color: #184C74;

    text-decoration: none;

    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.045);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


.quick-card:hover {

    transform: translateY(-3px);

    border-color: #184C74;

    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);

}


.quick-card-icon {

    width: 40px;

    height: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 14px;

    border-radius: 8px;

    background: #f1f6fa;

    color: #184C74;

    font-size: 16px;

}


.quick-card strong {

    display: block;

    margin-bottom: 6px;

    color: #184C74;

    font-size: 15px;

}


.quick-card small {

    display: block;

    color: #777;

    font-size: 12px;

    line-height: 1.5;

}


.quick-card-arrow {

    position: absolute;

    top: 20px;

    right: 20px;

    color: #a7b2ba;

    font-size: 13px;

    transition: transform 0.2s ease;

}


.quick-card:hover .quick-card-arrow {

    transform: translateX(3px);

    color: #184C74;

}


/* =========================================================
   MANAGEMENT CARDS
========================================================= */

.management-grid {

    display: grid;

    grid-template-columns:
        repeat(5, minmax(0, 1fr));

    gap: 15px;

}


.management-card {

    display: block;

    padding: 18px;

    background: #fff;

    border: 1px solid #e8edf0;

    border-radius: 10px;

    text-decoration: none;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


.management-card:hover {

    transform: translateY(-2px);

    border-color: #184C74;

    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);

}


.management-card strong {

    display: block;

    margin-bottom: 5px;

    color: #184C74;

    font-size: 14px;

}


.management-card span {

    color: #888;

    font-size: 11px;

    line-height: 1.5;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .dashboard-stats {

        grid-template-columns:
            repeat(3, 1fr);

    }


    .management-grid {

        grid-template-columns:
            repeat(3, 1fr);

    }

}


@media (max-width: 900px) {

    .quick-access-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .management-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 600px) {

    .dashboard-welcome {

        flex-direction: column;

        align-items: flex-start;

    }


    .dashboard-stats {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .quick-access-grid {

        grid-template-columns: 1fr;

    }


    .management-grid {

        grid-template-columns: 1fr;

    }

}

</style>


<div class="dashboard-page">


    <!-- =====================================================
         WELCOME
    ====================================================== -->

    <div class="dashboard-welcome">

        <div class="dashboard-welcome-content">

            <h2>
                Welcome to SIDiS Dashboard
            </h2>

            <p>
                Manage the School of Interdisciplinary Studies website
                content, people, students, clusters and contact information.
            </p>

        </div>


        <div class="dashboard-welcome-badge">

            Administration Panel

        </div>

    </div>



    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="dashboard-section">

        <div class="dashboard-section-header">

            <div>

                <h3>
                    Overview
                </h3>

                <p>
                    Current content available in the website database.
                </p>

            </div>

        </div>


        <div class="dashboard-stats">


            <!-- FACULTY -->

            <a href="faculty.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-chalkboard-teacher"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $facultyCount ?>
                    </strong>

                    <span>
                        Faculty
                    </span>

                </div>

            </a>


            <!-- STAFF -->

            <a href="staff.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-user-tie"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $staffCount ?>
                    </strong>

                    <span>
                        Staff
                    </span>

                </div>

            </a>


            <!-- M.S. -->

            <a href="ms.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-user-graduate"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $msCount ?>
                    </strong>

                    <span>
                        M.S. Students
                    </span>

                </div>

            </a>


            <!-- PH.D. -->

            <a href="phd.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-graduation-cap"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $phdCount ?>
                    </strong>

                    <span>
                        Ph.D. Scholars
                    </span>

                </div>

            </a>


            <!-- M.TECH -->

            <a href="mtech.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-book-reader"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $mtechCount ?>
                    </strong>

                    <span>
                        M.Tech Students
                    </span>

                </div>

            </a>


            <!-- CLUSTERS -->

            <a href="clusters.php" class="stat-card">

                <div class="stat-icon">

                    <i class="fas fa-layer-group"></i>

                </div>

                <div class="stat-content">

                    <strong>
                        <?= $clusterCount ?>
                    </strong>

                    <span>
                        Clusters
                    </span>

                </div>

            </a>


        </div>

    </div>



    <!-- =====================================================
         QUICK ACCESS
    ====================================================== -->

    <div class="dashboard-section">

        <div class="dashboard-section-header">

            <div>

                <h3>
                    Quick Access
                </h3>

                <p>
                    Frequently used website content.
                </p>

            </div>

        </div>


        <div class="quick-access-grid">


            <!-- HERO -->

            <a href="hero.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-image"></i>

                </div>

                <strong>
                    Hero Section
                </strong>

                <small>
                    Manage the homepage hero banner and content.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


            <!-- DIRECTOR -->

            <a href="director-message.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-user-edit"></i>

                </div>

                <strong>
                    Director's Message
                </strong>

                <small>
                    Update the message displayed from the Director.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


            <!-- HEAD -->

            <a href="head-message.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-comment-alt"></i>

                </div>

                <strong>
                    Head's Message
                </strong>

                <small>
                    Manage the message from the Head of SIDiS.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


            <!-- NEWS -->

            <a href="news.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-newspaper"></i>

                </div>

                <strong>
                    Latest News & Events
                </strong>

                <small>
                    Add, edit and manage website news and events.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


            <!-- ANNOUNCEMENTS -->

            <a href="announcements.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-bullhorn"></i>

                </div>

                <strong>
                    Announcements
                </strong>

                <small>
                    Manage important homepage announcements.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


            <!-- RESEARCH -->

            <a href="research.php" class="quick-card">

                <div class="quick-card-icon">

                    <i class="fas fa-flask"></i>

                </div>

                <strong>
                    Featured Research
                </strong>

                <small>
                    Manage research content highlighted on the website.
                </small>

                <span class="quick-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </span>

            </a>


        </div>

    </div>



    <!-- =====================================================
         CONTENT MANAGEMENT
    ====================================================== -->

    <div class="dashboard-section">

        <div class="dashboard-section-header">

            <div>

                <h3>
                    Content Management
                </h3>

                <p>
                    Manage the main sections of the SIDiS website.
                </p>

            </div>

        </div>


        <div class="management-grid">


            <!-- HOMEPAGE -->

            <a href="hero.php" class="management-card">

                <strong>
                    Homepage
                </strong>

                <span>
                    Hero, messages, news, announcements and research.
                </span>

            </a>


            <!-- ORGANIZATION -->

            <a href="committee.php" class="management-card">

                <strong>
                    Organizational Structure
                </strong>

                <span>
                    Committee, SCC and institutional guidelines.
                </span>

            </a>


            <!-- PEOPLE -->

            <a href="faculty.php" class="management-card">

                <strong>
                    People & Students
                </strong>

                <span>
                    Faculty, staff, M.S., Ph.D. and M.Tech records.
                </span>

            </a>


            <!-- CLUSTERS -->

            <a href="clusters.php" class="management-card">

                <strong>
                    Research Clusters
                </strong>

                <span>
                    Manage clusters and their associated faculty.
                </span>

            </a>


            <!-- CONTACT -->

            <a href="contact.php" class="management-card">

                <strong>
                    Contact Information
                </strong>

                <span>
                    Manage address, email, phone and Google Maps.
                </span>

            </a>


        </div>

    </div>


</div>


<?php

require_once __DIR__ . '/footer.php';

?>
```
