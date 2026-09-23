<?php

require_once __DIR__ . '/config.php';

include 'header.php';

/*
|--------------------------------------------------------------------------
| Fetch M.Tech Students
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        programme,
        roll_number,
        student_name,
        mentor_1,
        mentor_2
    FROM mtech_students
    ORDER BY
        CASE programme
            WHEN 'Interdisciplinary Dual Degree Program (IDDD)' THEN 1
            WHEN 'International Interdisciplinary Masters Program (I2MP)' THEN 2
            WHEN 'Joint Masters Program (JMP)' THEN 3
            ELSE 4
        END,
        id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Group students by programme
|--------------------------------------------------------------------------
*/

$programmes = [];

foreach ($students as $student) {
    $programmes[$student['programme']][] = $student;
}

?>

<!-- HERO SECTION -->
<section class="hero">

    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">

        <div class="hero-content about-section">
            <h2>M.Tech Students</h2>
        </div>

    </div>

</section>


<?php foreach ($programmes as $programme => $students): ?>

<section class="phd-table-section">

    <div class="container">

        <h3 class="clusters-side-heading">
            <?php echo htmlspecialchars($programme); ?>
        </h3>

        <div class="table-responsive">

            <table class="phd-table">

                <thead>

                    <tr>

                        <th>
                            <p>Roll Number</p>
                        </th>

                        <th>
                            <p>Student Name</p>
                        </th>

                        <th>
                            <p>Mentor 1</p>
                        </th>

                        <th>
                            <p>Mentor 2</p>
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($students as $student): ?>

                        <tr>

                            <td>
                                <p>
                                    <?php echo htmlspecialchars($student['roll_number'] ?? ''); ?>
                                </p>
                            </td>

                            <td>
                                <p>
                                    <?php echo htmlspecialchars($student['student_name']); ?>
                                </p>
                            </td>

                            <td>
                                <p>
                                    <?php echo htmlspecialchars($student['mentor_1'] ?? ''); ?>
                                </p>
                            </td>

                            <td>
                                <p>
                                    <?php echo htmlspecialchars($student['mentor_2'] ?? ''); ?>
                                </p>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<?php endforeach; ?>


<style>

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

    .news-content h3 {
        display: inline-block;
        background-color: #1F3C44;
        color: #fff;
        font-size: 28px;
        margin-bottom: 20px;
    }

</style>


<style>

    @media (max-width: 768px) {

        .hero-content h2 {
            font-size: 22px;
        }

        .hero-img {
            height: 150px !important;
        }

        .hero-content {
            top: 70% !important;
            background-color: #1F3C44;
            padding: 5px 20px;
            left: 0px;
        }

    }

</style>


<?php include 'footer.php'; ?>