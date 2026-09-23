<?php include 'header.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">
        <div class="hero-content about-section">
            <h2>Vision</h2>
        </div>
    </div>
</section>

<section class="phd-table-section">
    <div class="container">
        <ul style="text-align: justify;">
            <li>
                <p>To establish the School of Interdisciplinary Studies (SIDiS) at IIT Madras as a globally recognized hub for transformative research, education, and innovation that transcends traditional disciplinary boundaries.</p>
            </li>
            <li>
                <p>SIDiS envisions fostering a dynamic ecosystem where science, engineering, technology, and policy converge to address complex societal and technological challenges.</p>
            </li>
            <li>
                <p>The SIDiS aims to nurture a new generation of engineers, scientists, and technologists equipped with interdisciplinary knowledge, critical thinking, and collaborative skills to develop sustainable and scalable solutions in emerging areas such as advanced materials, energy systems, environmental sustainability, and climate resilience.</p>
            </li>
            <li>
                <p>By promoting strong academic integration, cutting-edge research, and industry engagement, SIDiS strives to drive impactful innovations that contribute to national priorities and global well-being.</p>
            </li>
        </ul>
    </div>
</section>



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