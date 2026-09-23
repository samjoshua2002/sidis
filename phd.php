<?php

require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| Fetch Ph.D. Scholars
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        batch,
        roll_number,
        student_name,
        mail_id,
        mentor_1,
        mentor_2
    FROM phd_scholars
    ORDER BY
        batch ASC,
        id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Group Students By Batch
|--------------------------------------------------------------------------
*/

$batches = [];

foreach ($scholars as $scholar) {

    $batch = trim($scholar['batch']);

    if ($batch === '') {
        $batch = 'Others';
    }

    $batches[$batch][] = $scholar;
}


/*
|--------------------------------------------------------------------------
| Sort Batches
|--------------------------------------------------------------------------
|
| Example:
| Jan 2025
| Jul 2025
| Jan 2026
| Jul 2026
|
| No batch names are hard-coded.
| The date/year is extracted from the batch name.
|
*/

uksort($batches, function ($a, $b) {

    $aTimestamp = strtotime('01 ' . $a);
    $bTimestamp = strtotime('01 ' . $b);

    if ($aTimestamp !== false && $bTimestamp !== false) {
        return $aTimestamp <=> $bTimestamp;
    }

    return strnatcasecmp($a, $b);
});

include 'header.php';

?>

<!-- HERO SECTION -->

<section class="hero">

    <img
        src="images/about-banner.png"
        class="hero-img"
        style="height: auto;"
    >

    <div class="container">

        <div class="hero-content about-section">

            <h2>Ph.D Scholars</h2>

        </div>

    </div>

</section>


<style>

    td > p > a {
        color: #000;
    }

</style>


<?php if (!empty($batches)): ?>

    <?php foreach ($batches as $batch => $students): ?>

        <section class="phd-table-section">

            <div class="container">

                <h3 class="clusters-side-heading">

                    <?php echo htmlspecialchars(
                        $batch,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                    Batch List

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
                                    <p>Mail ID</p>
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

                                <?php

                                $mail = trim(
                                    $student['mail_id'] ?? ''
                                );

                                /*
                                |--------------------------------------------------------------------------
                                | Obfuscate mail ID for display
                                |--------------------------------------------------------------------------
                                |
                                | Works even if the mail ID does not contain
                                | @ or .
                                |
                                */

                                $displayMail = str_replace(
                                    ['@', '.'],
                                    ['[at]', '[.]'],
                                    $mail
                                );

                                ?>


                                <tr>

                                    <td>

                                        <p>
                                            <?php echo htmlspecialchars(
                                                $student['roll_number'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                    </td>


                                    <td>

                                        <p>
                                            <?php echo htmlspecialchars(
                                                $student['student_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                    </td>


                                    <td>

                                        <p>

                                            <?php if ($mail !== ''): ?>

                                                <a
                                                    href="mailto:<?php echo htmlspecialchars(
                                                        $mail,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>"
                                                >

                                                    <?php echo htmlspecialchars(
                                                        $displayMail,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>

                                                </a>

                                            <?php endif; ?>

                                        </p>

                                    </td>


                                    <td>

                                        <p>
                                            <?php echo htmlspecialchars(
                                                $student['mentor_1'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </p>

                                    </td>


                                    <td>

                                        <p>
                                            <?php echo htmlspecialchars(
                                                $student['mentor_2'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
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


<?php else: ?>

    <section class="phd-table-section">

        <div class="container">

            <p>No Ph.D. scholars found.</p>

        </div>

    </section>

<?php endif; ?>

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

</style>


<?php include 'footer.php'; ?>