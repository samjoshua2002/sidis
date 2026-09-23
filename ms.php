<?php

include 'header.php';
require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| Fetch M.S. Students
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | Fetch all students
    |--------------------------------------------------------------------------
    |
    | Batch is taken directly from the ms.batch column.
    |
    */

    $sql = "SELECT
                id,
                batch,
                roll_number,
                student_name,
                mail_id,
                mentor_1,
                mentor_2
            FROM ms
            ORDER BY
                id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $students = $stmt->fetchAll();


    /*
    |--------------------------------------------------------------------------
    | Group students by batch
    |--------------------------------------------------------------------------
    */

    $students_by_batch = [];

    foreach ($students as $student) {

        $batch = trim($student['batch'] ?? '');

        /*
        | Skip students where batch is empty
        */

        if ($batch === '') {
            continue;
        }

        if (!isset($students_by_batch[$batch])) {
            $students_by_batch[$batch] = [];
        }

        $students_by_batch[$batch][] = $student;
    }


    /*
    |--------------------------------------------------------------------------
    | Sort batches
    |--------------------------------------------------------------------------
    |
    | This automatically sorts batches based on the date in the
    | batch name.
    |
    | Example:
    |
    | Jan 2025
    | Jul 2025
    | Jan 2026
    | Jul 2026
    | Jan 2027
    |
    */

    uksort($students_by_batch, function ($a, $b) {

        $dateA = DateTime::createFromFormat('M Y', $a);
        $dateB = DateTime::createFromFormat('M Y', $b);

        /*
        | If both are valid dates
        */

        if ($dateA && $dateB) {
            return $dateA <=> $dateB;
        }

        /*
        | If one/both are not valid dates,
        | keep normal alphabetical order.
        */

        return strcasecmp($a, $b);
    });


} catch (PDOException $e) {

    die(
        "Error fetching M.S. student data: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}

?>


<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="hero">

    <img
        src="images/about-banner.png"
        class="hero-img"
        style="height: auto;"
        alt="M.S Students"
    >

    <div class="container">

        <div class="hero-content about-section">

            <h2>
                M.S Students
            </h2>

        </div>

    </div>

</section>


<style>

    td > p > a {
        color: #000;
    }

</style>


<!-- =========================================================
     BATCH SECTIONS
========================================================= -->

<?php if (!empty($students_by_batch)): ?>


    <?php foreach ($students_by_batch as $batch => $batch_students): ?>


        <section class="phd-table-section">

            <div class="container">


                <!-- =================================================
                     BATCH HEADING
                ================================================== -->

                <h3 class="clusters-side-heading">

                    <?= htmlspecialchars(
                        $batch,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    Batch List

                </h3>


                <!-- =================================================
                     STUDENT TABLE
                ================================================== -->

                <div class="table-responsive">

                    <table class="phd-table">

                        <thead>

                            <tr>

                                <th>
                                    <p>
                                        Roll Number
                                    </p>
                                </th>

                                <th>
                                    <p>
                                        Student Name
                                    </p>
                                </th>

                                <th>
                                    <p>
                                        Mail ID
                                    </p>
                                </th>

                                <th>
                                    <p>
                                        Mentor 1
                                    </p>
                                </th>

                                <th>
                                    <p>
                                        Mentor 2
                                    </p>
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach ($batch_students as $student): ?>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Student Details
                                |--------------------------------------------------------------------------
                                */

                                $roll_number = htmlspecialchars(
                                    $student['roll_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                $student_name = htmlspecialchars(
                                    $student['student_name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                $mail_id_raw =
                                    trim(
                                        $student['mail_id'] ?? ''
                                    );


                                $mail_id = htmlspecialchars(
                                    $mail_id_raw,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                $mentor_1 = htmlspecialchars(
                                    $student['mentor_1'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                $mentor_2 = htmlspecialchars(
                                    $student['mentor_2'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | Email Display
                                |--------------------------------------------------------------------------
                                |
                                | Actual:
                                | id26s001@smail.iitm.ac.in
                                |
                                | Display:
                                | id26s001[at]smail[.]iitm[.]ac[.]in
                                |
                                */

                                $display_email = str_replace(
                                    ['@', '.'],
                                    ['[at]', '[.]'],
                                    $mail_id_raw
                                );

                                ?>


                                <tr>


                                    <!-- ROLL NUMBER -->

                                    <td>

                                        <p>
                                            <?= $roll_number ?>
                                        </p>

                                    </td>


                                    <!-- STUDENT NAME -->

                                    <td>

                                        <p>
                                            <?= $student_name ?>
                                        </p>

                                    </td>


                                    <!-- MAIL ID -->

                                    <td>

                                        <p>

                                            <?php if (!empty($mail_id_raw)): ?>

                                                <a
                                                    href="mailto:<?= $mail_id ?>"
                                                >

                                                    <?= htmlspecialchars(
                                                        $display_email,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                </a>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </p>

                                    </td>


                                    <!-- MENTOR 1 -->

                                    <td>

                                        <p>

                                            <?php if (!empty($mentor_1)): ?>

                                                <?= $mentor_1 ?>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </p>

                                    </td>


                                    <!-- MENTOR 2 -->

                                    <td>

                                        <p>

                                            <?php if (!empty($mentor_2)): ?>

                                                <?= $mentor_2 ?>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

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


    <!-- =========================================================
         NO STUDENTS
    ========================================================= -->

    <section class="phd-table-section">

        <div class="container">

            <h3 class="clusters-side-heading">

                No M.S. Students Found

            </h3>

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


    .phd-table tbody td:last-child {
        border-right: none;
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