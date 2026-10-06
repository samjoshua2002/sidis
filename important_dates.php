<?php

include 'header.php';
require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| Fetch Important Dates / Admission Schedules
|--------------------------------------------------------------------------
*/

try {

    $sql = "SELECT
                id,
                schedule_name,
                description,
                date_schedule
            FROM important_dates
            ORDER BY
                id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $all_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Group by Admission Schedule
    |--------------------------------------------------------------------------
    */

    $schedules = [];

    foreach ($all_dates as $date_item) {

        $schedule_name = trim($date_item['schedule_name'] ?? '');

        if ($schedule_name === '') {
            $schedule_name = 'Admission Schedule';
        }

        if (!isset($schedules[$schedule_name])) {
            $schedules[$schedule_name] = [];
        }

        $schedules[$schedule_name][] = $date_item;
    }

} catch (PDOException $e) {

    die(
        "Error fetching important dates: " .
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
        alt="Important Dates"
    >

    <div class="container">

        <div class="hero-content about-section">

            <h2>
                Important Dates 
            </h2>

        </div>

    </div>

</section>


<!-- =========================================================
     ADMISSION SCHEDULE SECTIONS
========================================================= -->

<?php if (!empty($schedules)): ?>


    <?php foreach ($schedules as $schedule_title => $schedule_items): ?>


        <section class="schedule-table-section">

            <div class="container">


                <!-- =================================================
                     SCHEDULE HEADING (NO UNDERLINE)
                ================================================== -->

                <h3 class="schedule-heading">

                    <?= htmlspecialchars(
                        $schedule_title,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </h3>


                <!-- =================================================
                     SCHEDULE TABLE
                ================================================== -->

                <div class="table-responsive">

                    <table class="schedule-table">

                        <thead>

                            <tr>

                                <th class="col-desc">
                                    <p>Description</p>
                                </th>

                                <th class="col-date">
                                    <p>Date/ Schedule</p>
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach ($schedule_items as $item): ?>


                                <?php

                                $description = htmlspecialchars(
                                    $item['description'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                $date_schedule = htmlspecialchars(
                                    $item['date_schedule'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>


                                <tr>


                                    <!-- DESCRIPTION -->

                                    <td class="td-desc">

                                        <p>
                                            <?= $description ?>
                                        </p>

                                    </td>


                                    <!-- DATE / SCHEDULE -->

                                    <td class="td-date">

                                        <p>
                                            <?= $date_schedule ?>
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
         NO SCHEDULES FOUND
    ========================================================= -->

    <section class="schedule-table-section">

        <div class="container">

            <h3 class="schedule-heading">
                Admission Schedule
            </h3>

            <p style="padding: 20px 0; color: #666; font-size: 16px;">
                No important dates or schedules are currently available. Please check back soon.
            </p>

        </div>

    </section>


<?php endif; ?>


<style>

    /* Section Spacing */
    .schedule-table-section {
        padding: 40px 0;
    }

    /* Heading Without Underline */
    .schedule-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        margin: 0 0 20px 0;
        line-height: 1.3;
        border: none;
        text-decoration: none;
    }

    /* Table Styles */
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    /* Header */
    .schedule-table thead tr {
        background-color: #1F3C44;
    }

    .schedule-table thead th {
        padding: 16px 22px;
        border-right: 2px solid #fff;
        text-align: left;
    }

    .schedule-table thead th:last-child {
        border-right: none;
    }

    .schedule-table thead th.col-desc {
        width: 48%;
    }

    .schedule-table thead th.col-date {
        width: 52%;
    }

    .schedule-table thead th p {
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.3px;
    }

    /* Alternating Rows: Row 1 White, Row 2 Yellow (#F4E796), Row 3 White... */
    .schedule-table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    .schedule-table tbody tr:nth-child(even) {
        background-color: #F4E796;
    }

    .schedule-table tbody td {
        padding: 22px 22px;
        border-right: 2px solid #fff;
        vertical-align: middle;
    }

    .schedule-table tbody td:last-child {
        border-right: none;
    }

    /* Cell Typography */
    .schedule-table td.td-desc p {
        color: #1a1a1a;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
        margin: 0;
    }

    .schedule-table td.td-date p {
        color: #184C74;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.5;
        margin: 0;
    }

    /* Hero section styles */
    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
    }

    .about-section p {
        font-family: 'Inter', sans-serif;
        margin: 5px;
        text-align: left;
    }

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

        .schedule-heading {
            font-size: 22px;
        }

        .schedule-table td,
        .schedule-table th {
            padding: 14px 15px;
        }
    }

</style>


<?php include 'footer.php'; ?>