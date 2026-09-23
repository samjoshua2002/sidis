<?php include 'header.php'; ?>

<!-- HERO SECTION -->
<?php
$stmt = $pdo->prepare("
    SELECT title, subtitle, image
    FROM hero_section
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute();

$hero = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php if ($hero): ?>

    <section class="hero">
        <img src="images/<?= htmlspecialchars($hero['image'], ENT_QUOTES, 'UTF-8') ?>" class="hero-img"
            alt="<?= htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="container">
            <div class="hero-content">
                <h1>
                    <?= htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8') ?><br>
                    <?= htmlspecialchars($hero['subtitle'], ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>
        </div>
    </section>

<?php endif; ?>

<!-- Announcement Card -->
<!-- <div class="announcement-card">
            <h6>
                News &amp; Announcements
            </h6>
            <p>MS & PhD applications close on 30 March 2026</p>
            <p>Item 02</p>
            <p>Item 03 in adipiscing gravida placerat eget maecenas feugiat facilisi praesent tempor.</p>
            <div class="d-flex justify-content-center">
                <button class="btn btn-success btn-sm">View all</button>
            </div>
        </div> -->

<!-- <section class="about-section">
    <div class="container">
        <h2>A Mission defined by Possibility</h2>
        <p style="padding-top: 10px;">The School of Interdisciplinary Studies, IIT Madras, unites international
            expertise across specialisations to facilitate collaboration, innovation and education. The Indian Institute
            of Technology Madras (IITM) fosters interdisciplinary research on various emerging areas to enable advanced
            solutions for cutting-edge technologies as well as unconventional sustainability solutions, including
            multi-functional materials, energy conversion, environment, ecological, and climate systems. IIT Madras
            strives to address the growing need to create a new generation of engineers and technologists with
            interdisciplinary skillsets that cut across traditional disciplinary boundaries and the ability to work in
            interdisciplinary domains. SIDiS will strive to foster research and technology development in the areas
            identified.

            SIDiS was founded to aspire to excellence in interdisciplinary research, education and training by promoting
            stronger scientific interactions among IITM faculty. SIDiS serves as a platform to enable these objectives
            through cutting-edge research across intersections of multiple domains, including applied science,
            engineering, technology, and policy. Based on the ongoing interdisciplinary activities, seven clusters have
            been formed.</p>

        <p>
            SIDiS was founded to aspire to excellence in
            interdisciplinary research, education and training by promoting stronger scientific interactions among
            IITM faculty. Therefore, SIDiS serves as a platform to enable the aforementioned objectives through
            cutting-edge research across intersections of multiple domains, including applied science, engineering,
            technology, and policy. Based on the ongoing interdisciplinary activities, seven clusters have been
            formed.</p>
    </div>
</section> -->

<?php
$stmt = $pdo->prepare("
    SELECT title, iframe_url
    FROM message_from_director
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute();

$message = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="about-section" style="padding-bottom: 0;">
    <div class="container">
        <div class="row align-items-center">

            <div class="section-heading">
                <?php if ($message): ?>

                    <h2 style="padding-bottom: 15px;">
                        <?= htmlspecialchars($message['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>

                    <iframe src="<?= htmlspecialchars($message['iframe_url'], ENT_QUOTES, 'UTF-8') ?>" width="100%"
                        height="600" style="border:0;" allow="autoplay">
                    </iframe>

                <?php endif; ?>
            </div>

        </div>
    </div>
</section>
<?php
$stmt = $pdo->prepare("
    SELECT description, name, designation, image
    FROM head_message
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute();

$headMessage = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php if ($headMessage): ?>

    <section class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">

                <!-- Left Content -->
                <div class="col-md-7">
                    <div class="section-heading">

                        <h2>Message from Head, SIDiS</h2>

                        <div style="padding-top: 15px;">
                            <p>
                                <?= nl2br(
                                    htmlspecialchars(
                                        $headMessage['description'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ) ?>
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Right Image -->
                <div class="col-md-5 text-center">

                    <?php if (!empty($headMessage['image'])): ?>

                        <img src="images/<?= htmlspecialchars(
                            $headMessage['image'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>" alt="<?= htmlspecialchars(
                             $headMessage['name'],
                             ENT_QUOTES,
                             'UTF-8'
                         ) ?>" class="img-fluid rounded shadow">

                    <?php endif; ?>


                    <h5 class="mt-3 mb-1">
                        <?= htmlspecialchars(
                            $headMessage['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h5>


                    <p class="text-muted mb-0" style="text-align: center;">
                        <?= htmlspecialchars(
                            $headMessage['designation'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                </div>

            </div>
        </div>
    </section>

<?php endif; ?>
<?php

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        link,
        image
    FROM news_events
    WHERE status = 1
    ORDER BY created_at DESC
");

$stmt->execute();

$newsEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="about-section" style="padding-top: 0;">

    <div class="container">

        <div class="section-heading">

            <h2>Latest News & Events</h2>

            <p>
                Stories about people, research, and innovation.
            </p>

        </div>


        <div class="news-carousel-wrapper">

            <button class="news-arrow prev" type="button">
                &#10094;
            </button>


            <div class="news-carousel">

                <div class="news-track">


                    <?php foreach ($newsEvents as $news): ?>

                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | CHECK WHETHER THE CONTENT IS A PDF
                        |--------------------------------------------------------------------------
                        */

                        $content = $news['link'] ?? '';

                        $isAttachment = false;

                        if (
                            !empty($content) &&
                            preg_match('/\.pdf$/i', $content)
                        ) {

                            $isAttachment = true;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CREATE THE CORRECT URL
                        |--------------------------------------------------------------------------
                        */

                        if ($isAttachment) {

                            $contentUrl =
                                'images/attachments/' .
                                rawurlencode($content);

                        } else {

                            $contentUrl = $content;

                        }

                        ?>


                        <div class="news-item">

                            <a href="<?= htmlspecialchars(
                                $contentUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" target="_blank" rel="noopener noreferrer" style="color:#000; text-decoration:none;">

                                <div class="news-card">


                                    <!-- IMAGE -->

                                    <div class="news-image">

                                        <img src="images/<?= htmlspecialchars(
                                            $news['image'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>" alt="<?= htmlspecialchars(
                                             $news['title'],
                                             ENT_QUOTES,
                                             'UTF-8'
                                         ) ?>">

                                    </div>


                                    <!-- CONTENT -->

                                    <div class="news-content">

                                        <h3 style="color:#000; text-decoration:none;">
                                            <?= htmlspecialchars(
                                                $news['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </h3>

                                    </div>


                                </div>

                            </a>

                        </div>


                    <?php endforeach; ?>


                </div>

            </div>


            <button class="news-arrow next" type="button">
                &#10095;
            </button>


        </div>

    </div>

</section>

<script>
    const track = document.querySelector(".news-track");
    const items = document.querySelectorAll(".news-item");

    let index = 0;
    let visible = getVisible();

    function getVisible() {
        if (window.innerWidth < 768) return 1;
        if (window.innerWidth < 992) return 2;
        return 3;
    }

    function updateCarousel() {

        visible = getVisible();

        const maxIndex = items.length - visible;

        if (index > maxIndex)
            index = maxIndex;

        const move = (100 / visible) * index;

        track.style.transform = `translateX(-${move}%)`;
    }

    document.querySelector(".next").onclick = function () {

        visible = getVisible();

        if (index < items.length - visible)
            index++;
        else
            index = 0;

        updateCarousel();
    };

    document.querySelector(".prev").onclick = function () {

        visible = getVisible();

        if (index > 0)
            index--;
        else
            index = items.length - visible;

        updateCarousel();
    };

    let auto = setInterval(() => {
        document.querySelector(".next").click();
    }, 3000);

    document.querySelector(".news-carousel-wrapper").addEventListener("mouseenter", () => {
        clearInterval(auto);
    });

    document.querySelector(".news-carousel-wrapper").addEventListener("mouseleave", () => {
        auto = setInterval(() => {
            document.querySelector(".next").click();
        }, 3000);
    });

    window.addEventListener("resize", updateCarousel);

    updateCarousel();
</script>



<style>
    .news-carousel-wrapper,
    .research-carousel-wrapper {
        position: relative;
        padding: 30px 0;
    }

    .news-carousel,
    .research-carousel {
        overflow: hidden;
    }

    .news-track,
    .research-track {
        display: flex;
        transition: transform .5s ease;
    }

    .news-item {
        flex: 0 0 33.3333%;
        padding: 0 12px;
        box-sizing: border-box;
    }

    .news-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 50%;
        background: #184C74;
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        z-index: 10;
    }

    .news-arrow.prev {
        left: -50px;
    }

    .news-arrow.next {
        right: -50px;
    }

    @media (max-width: 991px) {
        .news-item {
            flex: 0 0 50%;
        }
    }

    @media (max-width: 767px) {
        .news-item {
            flex: 0 0 100%;
        }

        .news-arrow.prev {
            left: 0;
        }

        .news-arrow.next {
            right: 0;
        }
    }
</style>






<?php
$stmt = $pdo->prepare("
    SELECT id, title, date_text, link, image
    FROM announcements
    WHERE status = 1
    ORDER BY id DESC
");
$stmt->execute();

$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="about-section" style="background-color: #184C74;">

    <div class="container" style="padding-top: 30px; padding-bottom: 30px;">

        <h2 style="color: #fff;">
            Announcements
        </h2>

        <p style="color: #fff;">
            More public events
        </p>


        <div class="announcement-box">

            <?php foreach ($announcements as $announcement): ?>

                <?php

                /*
                |--------------------------------------------------------------------------
                | CHECK CONTENT TYPE
                |--------------------------------------------------------------------------
                */

                $content =
                    $announcement['link'] ?? '';

                $isPdf = false;

                if (
                    !empty($content) &&
                    preg_match('/\.pdf$/i', $content)
                ) {

                    $isPdf = true;

                }


                /*
                |--------------------------------------------------------------------------
                | BUILD URL
                |--------------------------------------------------------------------------
                */

                if ($isPdf) {

                    $contentUrl =
                        'images/attachments/' .
                        rawurlencode($content);

                } else {

                    $contentUrl =
                        $content;

                }

                ?>



                <div class="announcement-item">


                    <!-- =================================================
                         ANNOUNCEMENT CONTENT
                         ================================================= -->

                    <div class="news-content">

                        <h3>

                            <?php if (!empty($content)): ?>

                                <a href="<?= htmlspecialchars(
                                    $contentUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>" target="_blank" rel="noopener noreferrer" style="
                                        color: #000;
                                        text-decoration: none;
                                    ">

                                    <?= htmlspecialchars(
                                        $announcement['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </a>

                            <?php else: ?>

                                <?= htmlspecialchars(
                                    $announcement['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            <?php endif; ?>

                        </h3>


                        <p>

                            <?= htmlspecialchars(
                                $announcement['date_text'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>


                        <?php if ($isPdf): ?>

                            <span style="
                                    font-size: 13px;
                                    color: #184C74;
                                ">
                                PDF Attachment
                            </span>

                        <?php endif; ?>


                    </div>



                    <!-- =================================================
                         IMAGE
                         ================================================= -->

                    <div class="announcement-image">

                        <?php if (!empty($announcement['image'])): ?>

                            <img src="images/<?= htmlspecialchars(
                                $announcement['image'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" alt="<?= htmlspecialchars(
                                 $announcement['title'],
                                 ENT_QUOTES,
                                 'UTF-8'
                             ) ?>">

                        <?php endif; ?>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>

    </div>

</section>











<?php
$stmt = $pdo->prepare("
    SELECT id, title, link, image
    FROM featured_research
    WHERE status = 1
    ORDER BY created_at DESC
");
$stmt->execute();

$featuredResearch = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="about-section">

    <div class="container">

        <div class="section-heading">
            <h2>Featured Research</h2>
        </div>

        <div class="research-carousel-wrapper">

            <button class="news-arrow prev">&#10094;</button>

            <div class="news-carousel">

                <div class="research-track">

                    <?php foreach ($featuredResearch as $research): ?>

                        <?php
                        /*
                        |--------------------------------------------------------------------------
                        | CHECK WHETHER CONTENT IS A PDF
                        |--------------------------------------------------------------------------
                        */

                        $content = $research['link'] ?? '';

                        $isPdf = (
                            !empty($content) &&
                            preg_match('/\.pdf$/i', $content)
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | BUILD CONTENT URL
                        |--------------------------------------------------------------------------
                        */

                        if ($isPdf) {

                            $researchUrl =
                                'images/attachments/' .
                                rawurlencode($content);

                        } else {

                            $researchUrl =
                                $content;

                        }
                        ?>


                        <div class="news-item">

                            <?php if (!empty($researchUrl)): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        $researchUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    style="
                                        color:#000;
                                        text-decoration:none;
                                    "
                                >

                            <?php endif; ?>


                                    <div class="news-card">

                                        <div class="news-image">

                                            <img
                                                src="images/<?= htmlspecialchars(
                                                    $research['image'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $research['title'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >

                                        </div>


                                        <div class="news-content">

                                            <h3
                                                style="
                                                    color:#000;
                                                    text-decoration:none;
                                                "
                                            >

                                                <?= htmlspecialchars(
                                                    $research['title'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </h3>


                                            <?php if ($isPdf): ?>

                                                <span
                                                    style="
                                                        font-size:13px;
                                                        color:#184C74;
                                                    "
                                                >
                                                    View PDF
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>


                            <?php if (!empty($researchUrl)): ?>

                                </a>

                            <?php endif; ?>


                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <button class="news-arrow next">&#10095;</button>

        </div>

    </div>

</section>


<script>
    function initCarousel(wrapperSelector, trackSelector) {

        const wrapper = document.querySelector(wrapperSelector);
        const track = wrapper.querySelector(trackSelector);
        const items = wrapper.querySelectorAll(".news-item");
        const prev = wrapper.querySelector(".prev");
        const next = wrapper.querySelector(".next");

        let index = 0;
        let visible = getVisible();

        function getVisible() {
            if (window.innerWidth < 768) return 1;
            if (window.innerWidth < 992) return 2;
            return 3;
        }

        function update() {

            visible = getVisible();

            const max = items.length - visible;

            if (index > max)
                index = max;

            track.style.transform =
                `translateX(-${(100 / visible) * index}%)`;
        }

        next.onclick = function () {

            visible = getVisible();

            index = (index < items.length - visible) ? index + 1 : 0;

            update();
        };

        prev.onclick = function () {

            visible = getVisible();

            index = (index > 0) ? index - 1 : items.length - visible;

            update();
        };

        let auto = setInterval(() => next.click(), 3000);

        wrapper.addEventListener("mouseenter", () => clearInterval(auto));

        wrapper.addEventListener("mouseleave", () => {
            auto = setInterval(() => next.click(), 3000);
        });

        window.addEventListener("resize", update);

        update();
    }

    // Latest News
    initCarousel(".news-carousel-wrapper", ".news-track");

    // Featured Research
    initCarousel(".research-carousel-wrapper", ".research-track");
</script>



<!-- <section class="about-section" style="padding-top: 0">
    <div class="container">

        <div class="section-heading">
            <h2>Quick Links</h2>
        </div>

        <div class="row g-4" style="padding-top: 20px;">

            
            <div class="col-md-4">
                <div class="quick-link-card">

                    <div class="quick-link-border"></div>

                    <div class="news-content" style="padding: 0px;">
                        <h3>Quick Links</h3>

                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>

                </div>
            </div>

       
            <div class="col-md-4">
                <div class="quick-link-card">

                    <div class="quick-link-border"></div>

                    <div class="news-content" style="padding: 0px;">
                        <h3>Quick Links</h3>

                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>

                </div>
            </div>

 
            <div class="col-md-4">
                <div class="quick-link-card">

                    <div class="quick-link-border"></div>

                    <div class="news-content" style="padding: 0px;">
                        <h3>Quick Links</h3>

                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section> -->

<?php include 'footer.php'; ?>