<?php include 'header.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;">

    <div class="container">
        <div class="hero-content about-section">
            <h2>Staff</h2>
        </div>
    </div>
</section>
<style>
    td>p>a {
        color: #000;
    }

    .hero {
        position: relative;
        margin-top: 150px;
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


<?php

$staffSql = "
    SELECT
        id,
        name,
        designation,
        image
    FROM staff
    WHERE status = 1
    ORDER BY display_order ASC, name ASC
";

$staffStmt = $pdo->prepare($staffSql);
$staffStmt->execute();

$staff = $staffStmt->fetchAll();

?>
<section class="staff-section">
    <div class="container">

        <div class="staff-grid">

            <?php foreach ($staff as $member): ?>

                <div class="faculty-card">

                    <div class="faculty-card-image">
                        <img src="images/staff/<?php echo htmlspecialchars($member['image']); ?>"
                            alt="<?php echo htmlspecialchars($member['name']); ?>">
                    </div>

                    <div class="faculty-card-content">

                        <h3>
                            <?php echo htmlspecialchars($member['name']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($member['designation']); ?>
                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<style>
    .staff-section {
        padding: 60px 0;
    }

    .staff-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .faculty-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.10);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .faculty-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.15);
    }

    .faculty-card-image {
        width: 100%;
        height: 280px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .faculty-card-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .faculty-card-content {
        padding: 20px;
        text-align: center;
    }

    .faculty-card-content h3 {
        color: #184C74;
        font-family: 'Lato', sans-serif;
        font-size: 21px;
        font-weight: 600;
        margin: 0 0 8px;
    }

    .faculty-card-content p {
        color: #555;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        margin: 0;
    }

    @media (max-width: 991px) {
        .staff-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .staff-grid {
            grid-template-columns: 1fr;
        }

        .faculty-card-image {
            height: 300px;
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
</style>

<?php include 'footer.php'; ?>