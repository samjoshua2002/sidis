<?php

include 'header.php';

require_once __DIR__ . '/config.php';


/*
|--------------------------------------------------------------------------
| FETCH CONTACT DETAILS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        school_name,
        institution_name,
        address,
        email,
        phone,
        map_embed_url
    FROM contact
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute();

$contact = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">
        <div class="hero-content about-section">
            <h2 style="color: #fff;">Contact</h2>
        </div>
    </div>
</section>


<style>
    /* ---------------------------------------------------------
       HERO
    --------------------------------------------------------- */

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
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
    }


    /* ---------------------------------------------------------
       CONTACT SECTION
    --------------------------------------------------------- */

    .contact-section {
        padding: 60px 0;
        background: #fff;
    }


    /* ---------------------------------------------------------
       CONTACT INFORMATION CARD
    --------------------------------------------------------- */

    .contact-info-card {
        border: 1px solid #d5d5d5;
        background: #fff;
        height: 100%;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.10);
    }

    .contact-info-header {
        background: linear-gradient(to bottom,
                #1F3C44 0%,
                #184C74 100%);

        padding: 25px 30px;
        color: #fff;
    }

    .contact-info-header h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .contact-info-header p {
        margin: 7px 0 0;
        font-size: 15px;
        opacity: 0.9;
    }


    /* ---------------------------------------------------------
       CONTACT ITEMS
    --------------------------------------------------------- */

    .contact-item {
        position: relative;
        padding: 25px 65px 25px 30px;
        border-bottom: 1px solid #d5d5d5;
    }

    .contact-item:last-child {
        border-bottom: none;
    }

    .contact-item h4 {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 600;
        color: #111;
    }

    .contact-item p {
        margin: 0;
        font-size: 17px;
        line-height: 1.7;
        color: #18212b;
    }

    .contact-item a {
        color: #18212b;
        text-decoration: none;
    }

    .contact-item a:hover {
        color: #184C74;
    }


    /* ---------------------------------------------------------
       ICONS
    --------------------------------------------------------- */

    .contact-icon {
        position: absolute;
        right: 30px;
        top: 32px;
        font-size: 25px;
        color: #184C74;
    }


    /* ---------------------------------------------------------
       MAP
    --------------------------------------------------------- */

    .contact-map {
        width: 100%;
        height: 100%;
        min-height: 480px;
        border: 0;
        display: block;
    }

    .map-wrapper {
        height: 100%;
        min-height: 480px;
        overflow: hidden;
        border: 1px solid #d5d5d5;
    }


    /* ---------------------------------------------------------
       RESPONSIVE
    --------------------------------------------------------- */

    @media (max-width: 991px) {

        .contact-info-card {
            margin-bottom: 30px;
        }

        .contact-map,
        .map-wrapper {
            min-height: 400px;
        }
    }


    @media (max-width: 576px) {

        .contact-section {
            padding: 40px 0;
        }

        .contact-info-header {
            padding: 20px;
        }

        .contact-info-header h3 {
            font-size: 21px;
        }

        .contact-item {
            padding: 22px 55px 22px 20px;
        }

        .contact-item h4 {
            font-size: 20px;
        }

        .contact-item p {
            font-size: 15px;
        }

        .contact-icon {
            right: 20px;
            top: 28px;
            font-size: 22px;
        }

        .contact-map,
        .map-wrapper {
            min-height: 350px;
        }
    }
</style>


<!-- CONTACT SECTION -->
<section class="contact-section">

    <div class="container">

        <div class="row g-4 align-items-stretch">


    <!-- LEFT : CONTACT DETAILS -->

    <div class="col-lg-5">

        <div class="contact-info-card">


            <!-- HEADER -->

            <div class="contact-info-header">

                <h3>
                    <?= htmlspecialchars($contact['school_name']) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($contact['institution_name']) ?>
                </p>

            </div>


            <!-- ADDRESS -->

            <div class="contact-item">

                <i class="fas fa-map-marker-alt contact-icon"></i>

                <h4>Address</h4>

                <p>
                    <?= nl2br(htmlspecialchars($contact['address'])) ?>
                </p>

            </div>


            <!-- E-MAIL -->

            <div class="contact-item">

                <i class="fas fa-envelope contact-icon"></i>

                <h4>E-Mail</h4>

                <p>

                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                        <?= htmlspecialchars($contact['email']) ?>
                    </a>

                </p>

            </div>


            <!-- PHONE -->

            <div class="contact-item">

                <i class="fas fa-phone-alt contact-icon"></i>

                <h4>Phone</h4>

                <p>

                    <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                        <?= htmlspecialchars($contact['phone']) ?>
                    </a>

                </p>

            </div>


        </div>

    </div>


    <!-- RIGHT : GOOGLE MAP -->

    <div class="col-lg-7">

        <div class="map-wrapper">

            <iframe
                class="contact-map"
                src="<?= htmlspecialchars($contact['map_embed_url']) ?>"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin">
            </iframe>

        </div>

    </div>


</div>

    </div>

</section>


<?php include 'footer.php'; ?>