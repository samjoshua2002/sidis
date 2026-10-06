<?php

include 'header.php';
require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| Fetch Active How to Apply Cards with Pagination
|--------------------------------------------------------------------------
*/

$cardsPerPage = 16;
$currentPage  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

try {

    // Count total active cards
    $countStmt = $pdo->query("SELECT COUNT(*) FROM how_to_apply WHERE status = 1");
    $totalCards = (int)$countStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($totalCards / $cardsPerPage));

    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $cardsPerPage;

    // Fetch page items
    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            image,
            link_type,
            link_url,
            attachment,
            display_order
        FROM how_to_apply
        WHERE status = 1
        ORDER BY display_order ASC, id ASC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', $cardsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $cards      = [];
    $totalCards = 0;
    $totalPages = 1;

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
        alt="How to Apply"
    >

    <div class="container">

        <div class="hero-content about-section">

            <h2>
                How to apply
            </h2>

        </div>

    </div>

</section>


<!-- =========================================================
     HOW TO APPLY CARDS SECTION
========================================================= -->

<section class="how-to-apply-section">

    <div class="container">

        <?php if (!empty($cards)): ?>

            <div class="apply-cards-grid">

                <?php foreach ($cards as $item): ?>

                    <?php

                    $title = htmlspecialchars(
                        $item['title'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $image = htmlspecialchars(
                        $item['image'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    /*
                    | Determine Target URL (Link or Attachment)
                    */

                    $targetUrl = '#';

                    if ($item['link_type'] === 'attachment' && !empty($item['attachment'])) {
                        $targetUrl = 'images/attachments/' . rawurlencode($item['attachment']);
                    } elseif (!empty($item['link_url'])) {
                        $targetUrl = htmlspecialchars($item['link_url'], ENT_QUOTES, 'UTF-8');
                    }

                    ?>


                    <div class="apply-card">

                        <a
                            href="<?= $targetUrl ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="apply-card-link"
                        >

                            <!-- CARD IMAGE -->

                            <div class="apply-card-image-wrapper">

                                <img
                                    src="images/<?= $image ?>"
                                    alt="<?= $title ?>"
                                    class="apply-card-img"
                                    loading="lazy"
                                >

                            </div>


                            <!-- CARD TITLE WITH CODEX LINK ICON -->

                            <div class="apply-card-content">

                                <h4 class="apply-card-title">

                                    <span><?= $title ?></span>

                                    <img
                                        src="images/codex_link.png"
                                        alt="Link"
                                        class="codex-link-icon"
                                    >

                                </h4>

                            </div>

                        </a>

                    </div>


                <?php endforeach; ?>

            </div>


            <!-- =========================================================
                 PAGINATION (Visible only if more than 16 cards exist)
            ========================================================= -->

            <?php if ($totalCards > $cardsPerPage): ?>

                <div class="apply-pagination-wrapper">

                    <ul class="apply-pagination">

                        <!-- PREVIOUS BUTTON -->
                        <?php if ($currentPage > 1): ?>
                            <li>
                                <a
                                    href="?page=<?= $currentPage - 1 ?>"
                                    class="apply-page-btn prev-btn"
                                    aria-label="Previous page"
                                >
                                    &#10094; Prev
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <span class="apply-page-btn prev-btn disabled">
                                    &#10094; Prev
                                </span>
                            </li>
                        <?php endif; ?>


                        <!-- PAGE NUMBERS -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <?php if ($i === $currentPage): ?>
                                <li>
                                    <span class="apply-page-btn active">
                                        <?= $i ?>
                                    </span>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a
                                        href="?page=<?= $i ?>"
                                        class="apply-page-btn"
                                    >
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                        <?php endfor; ?>


                        <!-- NEXT BUTTON -->
                        <?php if ($currentPage < $totalPages): ?>
                            <li>
                                <a
                                    href="?page=<?= $currentPage + 1 ?>"
                                    class="apply-page-btn next-btn"
                                    aria-label="Next page"
                                >
                                    Next &#10095;
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <span class="apply-page-btn next-btn disabled">
                                    Next &#10095;
                                </span>
                            </li>
                        <?php endif; ?>

                    </ul>

                </div>

            <?php endif; ?>


        <?php else: ?>

            <p style="text-align: center; color: #666; padding: 50px 0; font-size: 16px;">
                No application details available at the moment.
            </p>

        <?php endif; ?>

    </div>

</section>


<style>

    /* =========================================================
       HERO STYLING
    ========================================================= */

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
        font-size: 28px;
        margin: 0;
    }

    /* =========================================================
       HOW TO APPLY CARDS SECTION
    ========================================================= */

    .how-to-apply-section {
        padding: 60px 0 80px;
        background-color: #ffffff;
    }

    /* Grid layout: 4 columns desktop, 2x2 for tabs, 1 column mobile */
    .apply-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 36px 26px;
    }

    .apply-card {
        background: #ffffff;
        border-radius: 4px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .apply-card:hover {
        transform: translateY(-5px);
    }

    .apply-card-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .apply-card-link:hover {
        text-decoration: none;
    }

    /* Card Image Wrapper (Tall, spacious, and prominent) */
    .apply-card-image-wrapper {
        width: 100%;
        height: 240px;
        overflow: hidden;
        background-color: #f3f4f6;
    
    }

    .apply-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.35s ease;
    }

    .apply-card:hover .apply-card-img {
        transform: scale(1.05);
    }

    /* Card Content & Title */
    .apply-card-content {
        padding: 16px 4px 6px;
    }

    .apply-card-title {
        font-family: 'Lato', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #184C74;
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        line-height: 1.35;
        transition: color 0.2s ease;
    }

    .apply-card:hover .apply-card-title {
        color: #103450;
    }

    /* Custom Codex Link Icon (Prominent, clear size) */
    .codex-link-icon {
        width: 20px;
        height: 20px;
        object-fit: contain;
        display: inline-block;
        vertical-align: middle;
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }

    .apply-card:hover .codex-link-icon {
        transform: scale(1.18);
    }

    /* =========================================================
       PAGINATION STYLING
    ========================================================= */

    .apply-pagination-wrapper {
        margin-top: 55px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .apply-pagination {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .apply-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 42px;
        padding: 0 14px;
        border: 1px solid #d1d5db;
        background-color: #ffffff;
        color: #184C74;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        user-select: none;
    }

    .apply-page-btn:hover:not(.disabled):not(.active) {
        background-color: #f3f7fa;
        border-color: #184C74;
        color: #184C74;
        text-decoration: none;
    }

    .apply-page-btn.active {
        background-color: #184C74;
        border-color: #184C74;
        color: #ffffff;
        cursor: default;
    }

    .apply-page-btn.disabled {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* =========================================================
       RESPONSIVE (Desktop 4, Tabs 2x2, Mobile 1)
    ========================================================= */

    /* Tablet / Tabs (2 columns -> 2x2 grid) */
    @media (max-width: 991px) and (min-width: 577px) {
        .apply-cards-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 30px 22px;
        }

        .apply-card-image-wrapper {
            height: 250px;
        }

        .apply-card-title {
            font-size: 19px;
        }

        .codex-link-icon {
            width: 20px;
            height: 20px;
        }
    }

    /* Mobile (Full width tall card, generous touch size) */
    @media (max-width: 576px) {
        .how-to-apply-section {
            padding: 35px 0 50px;
        }

        .apply-cards-grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .apply-card-image-wrapper {
            height: 260px; /* Taller, prominent card image on mobile */
            border-radius: 8px;
        }

        .apply-card-title {
            font-size: 20px;
            gap: 10px;
        }

        .codex-link-icon {
            width: 22px;
            height: 22px;
        }

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

        .apply-page-btn {
            min-width: 38px;
            height: 38px;
            padding: 0 10px;
            font-size: 13px;
        }
    }

</style>


<?php include 'footer.php'; ?>