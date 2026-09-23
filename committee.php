<?php include 'header.php'; ?>
<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">


    <div class="container">
        <div class="hero-content about-section">
            <h2>Organizational Structure</h2>
        </div>


    </div>
</section>



<section class="about-section organizational-structure-section">
    <div class="container">

        <div class="row">

            <!-- LEFT FILTER -->
            <div class="col-md-3 left-committee">

                <div class="sidebar-filter">

                    <a href="#advisory" class="sidebar-link active-link">
                        <p>Advisory Committee</p>
                    </a>

                    <a href="#scc" class="sidebar-link">
                        <p>School Consultative Committee (SCC)</p>
                    </a>

                    <a href="#guidelines" class="sidebar-link">
                        <p>Guidelines</p>
                    </a>

                </div>

            </div>

            <!-- RIGHT CONTENT -->
            <div class="col-md-9 right-committee">

                <!-- Advisory -->
                <div id="advisory" class="committee-section">

                    <h3 class="clusters-side-heading">
                        Advisory Committee
                    </h3>
                    <?php

                    $sql = "
    SELECT
        id,
        name,
        designation
    FROM advisory_committee
    ORDER BY id ASC
";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    $committeeMembers = $stmt->fetchAll();

                    ?>
                    <div class="committee-content">

                        <?php foreach ($committeeMembers as $member): ?>

                            <div class="committee-box">
                                <p>
                                    <strong>
                                        <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>,
                                    <?= htmlspecialchars($member['designation'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

                <!-- SCC -->
                <div id="scc" class="committee-section">

                    <h3 class="clusters-side-heading">
                        School Consultative Committee (SCC)
                    </h3>

                    <?php

                    /*
                    |--------------------------------------------------------------------------
                    | FETCH SCC MEMBERS
                    |--------------------------------------------------------------------------
                    |
                    | Display order:
                    | 1. Main Committee
                    | 2. Members
                    | 3. IDDD Coordinators
                    | 4. I2MP Coordinators
                    |
                    | Within each section, records are ordered by ID.
                    |--------------------------------------------------------------------------
                    */

                    $sql = "
        SELECT
            id,
            name,
            designation,
            committee_section
        FROM school_consultative_committee

        ORDER BY
            CASE committee_section
                WHEN 'Main Committee' THEN 1
                WHEN 'Members' THEN 2
                WHEN 'IDDD Coordinators' THEN 3
                WHEN 'I2MP Coordinators' THEN 4
                ELSE 5
            END,

            id ASC
    ";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    $committeeMembers =
                        $stmt->fetchAll(PDO::FETCH_ASSOC);

                    ?>


                    <div class="committee-content">


                        <?php

                        $currentSection = '';

                        foreach ($committeeMembers as $member):


                            /*
                            |--------------------------------------------------------------------------
                            | CHECK SECTION CHANGE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $currentSection !==
                                $member['committee_section']
                            ):

                                $currentSection =
                                    $member['committee_section'];


                                /*
                                |--------------------------------------------------------------------------
                                | MAIN COMMITTEE
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    $currentSection ===
                                    'Main Committee'
                                ):
                                    ?>

                                    <!-- <div class="committee-title-box">

                                        <p>
                                            <strong>
                                                Main Committee
                                            </strong>
                                        </p>

                                    </div> -->


                                    <?php
                                    /*
                                    |--------------------------------------------------------------------------
                                    | MEMBERS
                                    |--------------------------------------------------------------------------
                                    */

                                elseif (
                                    $currentSection ===
                                    'Members'
                                ):
                                    ?>

                                    <div class="committee-title-box">

                                        <p>
                                            <strong>
                                                Members:
                                            </strong>
                                        </p>

                                    </div>


                                    <?php
                                    /*
                                    |--------------------------------------------------------------------------
                                    | IDDD COORDINATORS
                                    |--------------------------------------------------------------------------
                                    */

                                elseif (
                                    $currentSection ===
                                    'IDDD Coordinators'
                                ):
                                    ?>

                                    <div class="committee-title-box">

                                        <p>
                                            <strong>
                                                IDDD Coordinators
                                            </strong>
                                        </p>

                                    </div>


                                    <?php
                                    /*
                                    |--------------------------------------------------------------------------
                                    | I2MP COORDINATORS
                                    |--------------------------------------------------------------------------
                                    */

                                elseif (
                                    $currentSection ===
                                    'I2MP Coordinators'
                                ):
                                    ?>

                                    <div class="committee-title-box">

                                        <p>
                                            <strong>
                                                I2MP Coordinators
                                            </strong>
                                        </p>

                                    </div>

                                <?php endif; ?>


                            <?php endif; ?>


                            <!-- MEMBER -->

                            <div class="committee-box">

                                <p>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $member['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>


                                    <?php if (
                                        !empty(
                                        $member['designation']
                                    )
                                    ): ?>

                                        ,

                                        <?= htmlspecialchars(
                                            $member['designation'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php endif; ?>

                                </p>

                            </div>


                        <?php endforeach; ?>


                    </div>

                </div>
                <!-- Guidelines -->
<div id="guidelines" class="committee-section">

    <h3 class="clusters-side-heading">
        Guidelines
    </h3>

    <?php

    /*
    |--------------------------------------------------------------------------
    | FETCH GUIDELINES
    |--------------------------------------------------------------------------
    |
    | Keep the four guideline categories in a fixed order.
    |
    | Within each category, records are ordered by ID.
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            title,
            guideline
        FROM guidelines
        ORDER BY
            CASE title
                WHEN 'Norms to propose a new IDDD program' THEN 1
                WHEN 'Norms to invite existing faculty members to SIDiS' THEN 2
                WHEN 'Norms to start a new cluster' THEN 3
                WHEN 'Performance evaluation of existing clusters' THEN 4
                ELSE 5
            END,
            id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $guidelines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>


    <div class="committee-content">

        <?php

        $currentTitle = '';

        $isListOpen = false;


        foreach ($guidelines as $item):


            /*
            |--------------------------------------------------------------------------
            | NEW GUIDELINE CATEGORY
            |--------------------------------------------------------------------------
            */

            if ($currentTitle !== $item['title']):


                /*
                |--------------------------------------------------------------------------
                | CLOSE PREVIOUS LIST
                |--------------------------------------------------------------------------
                */

                if ($isListOpen):

                    ?>

                    </ol>

                    </div>

                    <?php

                    $isListOpen = false;

                endif;


                /*
                |--------------------------------------------------------------------------
                | SET CURRENT TITLE
                |--------------------------------------------------------------------------
                */

                $currentTitle =
                    $item['title'];

                ?>


                <!-- GUIDELINE TITLE -->

                <div class="committee-title-box">

                    <p>

                        <strong>

                            <?= htmlspecialchars(
                                $item['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>:

                        </strong>

                    </p>

                </div>


                <!-- GUIDELINE LIST -->

                <div class="committee-box">

                    <ol>

                <?php

                $isListOpen = true;

            endif;


            /*
            |--------------------------------------------------------------------------
            | GUIDELINE ITEM
            |--------------------------------------------------------------------------
            */

            ?>

            <li>

                <p>

                    <?= htmlspecialchars(
                        $item['guideline'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

            </li>


        <?php endforeach; ?>


        <?php

        /*
        |--------------------------------------------------------------------------
        | CLOSE LAST LIST
        |--------------------------------------------------------------------------
        */

        if ($isListOpen):

            ?>

            </ol>

            </div>

        <?php endif; ?>


    </div>

</div>

            </div>

        </div>

    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const links = document.querySelectorAll(".sidebar-link");
        const sections = document.querySelectorAll(".committee-section");

        let isClickScrolling = false;

        // First active initially
        if (links.length > 0) {
            links[0].classList.add("active-link");
        }

        // Click scroll
        links.forEach(link => {

            link.addEventListener("click", function (e) {

                e.preventDefault();

                isClickScrolling = true;

                // Remove all active
                links.forEach(item => {
                    item.classList.remove("active-link");
                });

                // Add active instantly
                this.classList.add("active-link");

                const targetId = this.getAttribute("href");
                const targetSection = document.querySelector(targetId);

                const offset = 120;

                const topPosition =
                    targetSection.offsetTop - offset;

                window.scrollTo({
                    top: topPosition,
                    behavior: "smooth"
                });

                // Prevent scroll listener glitch
                setTimeout(() => {
                    isClickScrolling = false;
                }, 700);

            });

        });

        // Scroll active state
        window.addEventListener("scroll", function () {

            if (isClickScrolling) return;

            let currentSection = "";

            sections.forEach(section => {

                const sectionTop = section.offsetTop - 180;
                const sectionHeight = section.offsetHeight;

                if (
                    window.scrollY >= sectionTop &&
                    window.scrollY < sectionTop + sectionHeight
                ) {
                    currentSection = section.getAttribute("id");
                }

            });

            if (currentSection !== "") {

                links.forEach(link => {

                    if (link.getAttribute("href") === "#" + currentSection) {
                        link.classList.add("active-link");
                    } else {
                        link.classList.remove("active-link");
                    }

                });

            }

        });

    });
</script>


<style>
    .organizational-structure-section {
        padding: 60px 0;
    }

    .sidebar-filter {
        background-color: #184C74;
        padding: 10px;
        border-radius: 8px;
        position: sticky;
        top: 175px;
    }

    .sidebar-link {
        display: block;
        background-color: #fff;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 8px;
        text-decoration: none;
    }

    .sidebar-link p {
        margin: 0;
        color: #000;
    }

    .active-link {
        background-color: #F4E796;
    }

    .committee-section {
        margin-bottom: 60px;
    }

    .committee-content {
        border-top: 1px solid #184C74;
        padding-top: 10px;
    }

    .committee-box {
        background-color: #f2f2f2;
        padding: 8px 5px;
        margin-bottom: 5px;
    }

    .committee-title-box {
        background-color: #184C74;
        padding: 8px 5px;
        margin-bottom: 5px;
        border-radius: 4px;
    }

    .committee-title-box p {
        color: #fff;
        margin: 0;
    }

    .committee-box p {
        margin: 0;
    }

    @media (max-width: 768px) {

        .sidebar-filter {
            position: relative;
            top: 0;
            margin-bottom: 30px;
        }

    }

    .faculty-card {
        padding-bottom: 20px;
        border-bottom: 1px solid #000;
    }

    @media (max-width: 768px) {
        .faculty-card {
            padding-bottom: 20px;
            border-bottom: 1px solid #000;
            flex-direction: column;
        }
    }

    .clusters-side-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        padding-bottom: 10px;
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

    @media (max-width: 1024px) {

        .left-committee,
        .right-committee {
            width: 100%;
        }

        .left-committee {
            margin-bottom: 30px;
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