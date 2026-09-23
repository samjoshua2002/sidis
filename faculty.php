<?php include 'header.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">
        <div class="hero-content about-section">
            <h2>Faculty</h2>
        </div>
    </div>
</section>
<style>
    td>p>a {
        color: #000;
    }

    @media (max-width: 768px) {
        .hero-content h2 {
            font-size: 22px;
        }
    }

    @media (max-width: 768px) {
        .hero-img {
            height: 150px !important;
        }
    }

    @media (max-width: 768px) {
        .hero-content {
            top: 70% !important;
            background-color: #1F3C44;
            padding: 5px 20px;
            left: 0px;
        }
    }




    .phd-table-section {
        padding: 40px 0;
    }

    .phd-table {
        width: 100%;
        border-collapse: collapse;
    }

    .phd-table thead tr {
        background-color: #1F3C44;
    }

    .phd-table thead th {
        padding: 16px 18px;
        border-right: 1px solid #fff;
    }

    .phd-table thead th:last-child {
        border-right: none;
    }

    .phd-table thead th p {
        color: #fff;
        margin: 0;
    }

    .phd-table tbody tr:nth-child(even) {
        background-color: #F4E796;
    }

    .phd-table tbody td {
        padding: 25px 18px;
        border-right: 2px solid #fff;
        vertical-align: top;
    }

    .phd-table tbody td p {
        margin: 0;
    }

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
    }

    .clusters-side-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        padding-bottom: 20px;
    }

    .about-section p {
        font-family: 'Inter', sans-serif;
        margin: 5px;
        text-align: left;
    }

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }



    .hero-content h2 {
        color: #fff;
    }

    .news-content h3 {
        display: inline-block;
        background-color: #1F3C44;
        color: #fff;
        font-size: 28px;

        margin-bottom: 20px;
    }

    .faculty-top-section {
        padding: 50px 0 40px;
        background: #ffffff;
    }

    .faculty-top-cards {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 30px;
        /* max-width: 800px; */
        margin: 0 auto;
    }

    .faculty-top-cards .faculty-card {
        width: 350px;
    }

    @media (max-width: 767px) {
        .faculty-top-cards {
            flex-direction: column;
            align-items: center;
        }

        .faculty-top-cards .faculty-card {
            width: 100%;
            max-width: 350px;
        }
    }
</style>


<?php
$topFacultyQuery = "
    SELECT *
    FROM faculty
    WHERE top_order = 1
    ORDER BY id ASC
";

$topFacultyStmt = $pdo->prepare($topFacultyQuery);
$topFacultyStmt->execute();
$topFaculty = $topFacultyStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="faculty-top-section">
    <div class="container">

        <div class="faculty-top-cards">

            <?php foreach ($topFaculty as $member): ?>

                <a href="<?php echo htmlspecialchars(
                    $member['personal_page'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>" target="_blank" class="faculty-card">

                    <div class="faculty-image">
                        <img src="images/faculty/<?php echo htmlspecialchars(
                            $member['image'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>" alt="<?php echo htmlspecialchars(
                             $member['name'],
                             ENT_QUOTES,
                             'UTF-8'
                         ); ?>">
                    </div>

                    <div class="faculty-content">

                        <h3 class="faculty-name">
                            <?php echo htmlspecialchars(
                                $member['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </h3>
                        <p class="faculty-department">
                            <?php echo htmlspecialchars(
                                $member['department'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </p>

                        <p class="faculty-designation">
                            <?php echo htmlspecialchars(
                                $member['designation'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </p>

                        <?php if (!empty($member['email'])): ?>
                            <p class="faculty-email">
                                <i class="fa-regular fa-envelope"></i>

                                <?php echo htmlspecialchars(
                                    $member['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </p>
                        <?php endif; ?>

                    </div>

                </a>

            <?php endforeach; ?>

        </div>

    </div>
</section>




















<?php

$clusterSql = "
    SELECT
        id,
        cluster_name
    FROM clusters
    WHERE status = 1
    ORDER BY id ASC
";

$clusterStmt = $pdo->prepare($clusterSql);
$clusterStmt->execute();

$clusters = $clusterStmt->fetchAll();


$facultySql = "
    SELECT
        f.id,
        f.cluster_id,
        f.name,
        f.image,
        f.department,
        f.designation,
        f.email,
        f.personal_page
    FROM faculty f
    WHERE f.status = 1
        AND f.top_order = 0
        AND f.cluster_id IS NOT NULL
        AND f.cluster_id != ''
    ORDER BY
        
        f.name ASC
";

$facultyStmt = $pdo->prepare($facultySql);
$facultyStmt->execute();

$faculty = $facultyStmt->fetchAll();

?>

<section class="faculty-section">

    <div class="container">

        <div class="row">

            <div class="col-md-3 faculty-filter-column">

                <div class="faculty-filters">

                    <button type="button" class="faculty-filter active" data-cluster-filter="all">
                        All
                    </button>

                    <?php foreach ($clusters as $cluster): ?>

                        <button type="button" class="faculty-filter"
                            data-cluster-filter="<?php echo (int) $cluster['id']; ?>">
                            <?php echo htmlspecialchars(
                                $cluster['cluster_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </button>

                    <?php endforeach; ?>

                </div>

            </div>


            <div class="col-md-9 faculty-card-column">

                <div class="row" id="facultyContainer">

                    <?php foreach ($faculty as $member): ?>

                        <?php
                        $personalPage = trim($member['personal_page'] ?? '');

                        /*
                        |--------------------------------------------------------------------------
                        | Faculty can belong to multiple clusters
                        |
                        | Example:
                        | cluster_id = "1,2"
                        | cluster_id = "1,3,5"
                        |--------------------------------------------------------------------------
                        */

                        $clusterIds = trim($member['cluster_id']);
                        ?>

                        <div class="col-lg-4 col-md-6 faculty-card-wrapper"
                            data-cluster-id="<?php echo htmlspecialchars(
                                $clusterIds,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">

                            <?php if (!empty($personalPage)): ?>

                                <a href="<?php echo htmlspecialchars(
                                    $personalPage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>" target="_blank" class="faculty-card">

                                <?php else: ?>

                                    <div class="faculty-card">

                                    <?php endif; ?>

                                    <div class="faculty-image">

                                        <img src="images/faculty/<?php echo htmlspecialchars(
                                            $member['image'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>" alt="<?php echo htmlspecialchars(
                                             $member['name'],
                                             ENT_QUOTES,
                                             'UTF-8'
                                         ); ?>" loading="lazy">

                                    </div>

                                    <div class="faculty-content">

                                        <h3 class="faculty-name">
                                            <?php echo htmlspecialchars(
                                                $member['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </h3>

                                        <p class="faculty-department">
                                            <?php echo htmlspecialchars(
                                                $member['department'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                        <p class="faculty-designation">
                                            <?php echo htmlspecialchars(
                                                $member['designation'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                        <p class="faculty-email">
                                            <i class="fa-regular fa-envelope"></i>
                                            <?php echo htmlspecialchars(
                                                $member['email'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                    </div>

                                    <?php if (!empty($personalPage)): ?>

                                </a>

                            <?php else: ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

                <div id="facultyNoResult" class="faculty-no-result">
                    No faculty members found in this cluster.
                </div>

            </div>

        </div>

    </div>

    </div>

</section>

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const filterButtons = document.querySelectorAll('.faculty-filter');

        const facultyCards = document.querySelectorAll('.faculty-card-wrapper');

        const noResult = document.getElementById('facultyNoResult');

        filterButtons.forEach(function (button) {

            button.addEventListener('click', function (event) {

                event.preventDefault();

                const selectedCluster = this.getAttribute(
                    'data-cluster-filter'
                );

                filterButtons.forEach(function (filterButton) {

                    filterButton.classList.remove('active');

                });

                this.classList.add('active');

                let visibleCards = 0;

                facultyCards.forEach(function (card) {

                    const facultyCluster = card.getAttribute(
                        'data-cluster-id'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Convert comma-separated cluster IDs into an array
                    |
                    | Example:
                    | "1,2" -> ["1", "2"]
                    | "1,3,5" -> ["1", "3", "5"]
                    |--------------------------------------------------------------------------
                    */

                    const facultyClusters = facultyCluster
                        ? facultyCluster.split(',').map(function (id) {
                            return id.trim();
                        })
                        : [];


                    /*
                    |--------------------------------------------------------------------------
                    | Show faculty
                    |--------------------------------------------------------------------------
                    */

                    if (
                        selectedCluster === 'all' ||
                        facultyClusters.includes(selectedCluster)
                    ) {

                        card.style.display = '';

                        visibleCards++;

                    } else {

                        card.style.display = 'none';

                    }

                });


                /*
                |--------------------------------------------------------------------------
                | No result message
                |--------------------------------------------------------------------------
                */

                if (visibleCards === 0) {

                    noResult.style.display = 'block';

                } else {

                    noResult.style.display = 'none';

                }

            });

        });

    });

</script>



















<style>
    .faculty-section {
        padding: 70px 0;
        background: #ffffff;
    }


    .faculty-filter-column {
        padding-right: 30px;
    }

    .faculty-card-column {
        padding-left: 15px;
    }

    .faculty-filters {
        width: 100%;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, 0.18);
    }

    .faculty-filter {
        width: 100%;
        min-height: 62px;
        padding: 15px 20px;
        border: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        background: linear-gradient(to bottom,
                #1F3C44 0%,
                #184C74 100%);
        color: #ffffff;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition:
            background 0.35s ease,
            color 0.35s ease,
            box-shadow 0.35s ease,
            transform 0.35s ease;
    }

    .faculty-filter:last-child {
        border-bottom: none;
    }

    .faculty-filter:hover {
        background: linear-gradient(to bottom,
                #28505A 0%,
                #1F5B86 100%);
        color: #ffffff;
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.12),
            0 4px 14px rgba(24, 76, 116, 0.25);
        transform: translateX(3px);
    }

    .faculty-filter.active {
        background: #ffffff;
        color: #184C74;
        box-shadow:
            inset 0 0 0 1px rgba(24, 76, 116, 0.12),
            0 3px 12px rgba(24, 76, 116, 0.12);
        transform: none;
    }

    .faculty-filter.active:hover {
        background: #ffffff;
        color: #184C74;
        box-shadow:
            inset 0 0 0 1px rgba(24, 76, 116, 0.16),
            0 4px 14px rgba(24, 76, 116, 0.16);
        transform: translateX(3px);
    }

    .faculty-card-wrapper {
        margin-bottom: 30px;
    }

    .faculty-card {
        display: block;
        width: 100%;
        height: 100%;
        background: #ffffff;
        border: 1px solid #e3e3e3;
        text-decoration: none;
        color: inherit;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease;
    }

    .faculty-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .faculty-image {
        width: 100%;
        height: 280px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .faculty-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.4s ease;
    }

    .faculty-card:hover .faculty-image img {
        transform: scale(1.03);
    }

    .faculty-content {
        padding: 20px 18px 22px;
        text-align: center;
    }

    .faculty-name {
        margin: 0 0 8px;
        color: #184C74;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.35;
    }

    .faculty-department {
        margin: 0 0 6px;
        color: #555555;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.5;
    }

    .faculty-designation {
        margin: 0 0 12px;
        color: #666666;
        font-size: 15px;
        line-height: 1.5;
    }

    .faculty-email {
        margin: 0;
        color: #184C74;
        font-size: 14px;
        word-break: break-word;
    }

    .faculty-email i {
        margin-right: 6px;
    }

    .faculty-no-result {
        display: none;
        width: 100%;
        padding: 40px 20px;
        text-align: center;
        color: #666666;
        font-size: 17px;
    }

    @media (max-width: 991px) {

        .faculty-filter-column {
            padding-right: 15px;
            margin-bottom: 30px;
        }

        .faculty-card-column {
            padding-left: 15px;
        }

        .faculty-filters {
            display: flex;
            flex-wrap: wrap;
        }

        .faculty-filter {
            width: 50%;
            min-height: 55px;
        }

    }

    @media (max-width: 575px) {

        .faculty-section {
            padding: 45px 15px;
        }

        .faculty-filter {
            width: 100%;
        }

        .faculty-image {
            height: 300px;
        }

    }
</style>


<?php include 'footer.php'; ?>