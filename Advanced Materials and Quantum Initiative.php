<?php include 'header.php'; ?>
<!-- HERO SECTION -->
<section class="hero">
    <img src="images/amqi-banner.png" class="hero-img" style="height: auto;">


    <div class="container">
        <div class="hero-content about-section">
            <h2>Advanced Materials and Quantum Initiative</h2>
        </div>


    </div>
</section>

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

<section class="about-section">
    <div class="container">

        <!-- Academic Programmes -->
        <h3 class="clusters-side-heading">Academic programmes</h3>

        <p><strong>Interdisciplinary Dual Degree (IDDD) &amp; International Interdisciplinary Masters Programme
                (I2MP)</strong></p>

        <ul>
            <li>
                <p>Advanced Materials and Nanotechnology (IDDD+I2MP)</p>
            </li>
            <li>
                <p>Quantum Science and Technology (IDDD+I2MP)</p>
            </li>
        </ul>

        <!-- Associated Faculty -->
        <h3 class="clusters-side-heading mt-4">Co-ordinators Name</h3>

        <div class="row mt-3 faculty-row">

            <!-- Faculty 1 -->
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center faculty-card">

                    <img src="images/abhishek.png" alt="Abhishek Misra">

                    <div class="faculty-content ms-4">
                        <h3 class="clusters-side-heading">Abhishek Misra</h3>

                        <p><strong>IDDD Coordinator</strong></p>

                        <p>
                            Advanced Materials and Nanotechnology
                        </p>

                        <p><strong>Associate Professor</strong></p>

                        <p>
                            Department of Physics, IIT Madras
                        </p>

                        <p>
                            <i class="fa fa-envelope"></i>
                            abhishek.misra@iitm.ac.in
                        </p>
                    </div>

                </div>
            </div>

            <!-- Faculty 2 -->
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center faculty-card">

                    <img src="images/prabha.png" alt="Prabha Mandayam">

                    <div class="faculty-content ms-4">
                        <h3 class="clusters-side-heading">Prabha Mandayam</h3>

                        <p><strong>IDDD Coordinator</strong></p>

                        <p>
                            Quantum Science and Technology
                        </p>

                        <p><strong>Associate Professor</strong></p>

                        <p>
                            Department of Physics, IIT Madras
                        </p>

                        <p>
                            <i class="fa fa-envelope"></i>
                            prabhamd@iitm.ac.in
                        </p>
                    </div>

                </div>
            </div>

            <!-- Faculty 3 -->
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center faculty-card">

                    <img src="images/praveen.png" alt="Vidya Praveen Bhallamudi">

                    <div class="faculty-content ms-4">
                        <h3 class="clusters-side-heading">Vidya Praveen Bhallamudi</h3>

                        <p><strong>I2MP Coordinator</strong></p>

                        <p>
                            Advanced Materials and Nanotechnology
                        </p>

                        <p><strong>Assistant Professor</strong></p>

                        <p>
                            Department of Physics, IIT Madras
                        </p>

                        <p>
                            <i class="fa fa-envelope"></i>
                            praveen.bhallamudi@iitm.ac.in
                        </p>
                    </div>

                </div>
            </div>

        </div>
</section>


<style>
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

<?php include 'footer.php'; ?>