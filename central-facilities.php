<?php
/*
|--------------------------------------------------------------------------
| Facilities > Central Facilities
|--------------------------------------------------------------------------
| Equipment cards (image left, name + description right) from `facilities`
| where category = 'central'. Managed in Admin > Facilities > Central Facilities.
*/

include 'header.php';
require_once __DIR__ . '/config.php';

$facilityItems = [];

try {
    $stmt = $pdo->prepare("
        SELECT id, name, description, image, link_type, link_url, pdf_file
        FROM facilities
        WHERE category = 'central' AND status = 1
        ORDER BY display_order ASC, id ASC
    ");
    $stmt->execute();
    $facilityItems = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Facilities fetch error: ' . $e->getMessage());
}

$e = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

/* Blank line = new paragraph; single line break kept inside a paragraph */
function facilityParagraphs($text)
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", (string)$text));
    if ($text === '') return [];
    return array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', $text)), 'strlen'));
}
?>

<!-- HERO -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;" alt="Central Facilities">
    <div class="container">
        <div class="hero-content about-section">
            <h2>Central Facilities</h2>
        </div>
    </div>
</section>

<section class="facility-section">
    <div class="container">

        <?php if (!empty($facilityItems)): ?>

            <div class="facility-list">
                <?php foreach ($facilityItems as $item): ?>
                    <?php
                    $img = trim((string)$item['image']);
                    $imgPath = $img !== '' && file_exists(__DIR__ . '/images/facilities/' . $img) ? 'images/facilities/' . $img : '';

                    // Optional title link: external URL (http/https only) or uploaded PDF
                    $href = '';
                    $linkKind = '';
                    if ($item['link_type'] === 'url' && preg_match('#^https?://#i', (string)$item['link_url'])) {
                        $href = $item['link_url'];
                        $linkKind = 'url';
                    } elseif ($item['link_type'] === 'pdf' && $item['pdf_file'] !== ''
                        && is_file(__DIR__ . '/images/facilities/docs/' . basename($item['pdf_file']))) {
                        $href = 'images/facilities/docs/' . basename($item['pdf_file']);
                        $linkKind = 'pdf';
                    }
                    ?>
                    <article class="facility-card">
                        <div class="facility-media">
                            <?php if ($href !== ''): ?><a href="<?= $e($href) ?>" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true"><?php endif; ?>
                            <?php if ($imgPath !== ''): ?>
                                <img src="<?= $e($imgPath) ?>" alt="<?= $e($item['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="facility-noimg"><i class="fa-solid fa-microscope"></i></div>
                            <?php endif; ?>
                            <?php if ($href !== ''): ?></a><?php endif; ?>
                        </div>
                        <div class="facility-body">
                            <h3>
                                <?php if ($href !== ''): ?>
                                    <a href="<?= $e($href) ?>" target="_blank" rel="noopener noreferrer" class="facility-title-link">
                                        <?= $e($item['name']) ?>
                                        <?php if ($linkKind === 'pdf'): ?>
                                            <i class="fa-solid fa-file-pdf" title="Opens PDF"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-arrow-up-right-from-square" title="Opens in a new tab"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <?= $e($item['name']) ?>
                                <?php endif; ?>
                            </h3>
                            <?php foreach (facilityParagraphs($item['description']) as $para): ?>
                                <p><?= nl2br($e($para)) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <div class="facility-empty">
                <i class="fa-solid fa-microscope"></i>
                <p>Facility details will be published here soon.</p>
            </div>

        <?php endif; ?>

    </div>
</section>

<style>
    .facility-section {
        padding: 50px 0 70px;
    }

    .facility-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .facility-card {
        display: flex;
        gap: 26px;
        align-items: flex-start;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 14px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
    }

    .facility-media {
        flex: 0 0 300px;
        max-width: 300px;
    }

    .facility-media img,
    .facility-noimg {
        width: 100%;
        aspect-ratio: 16 / 10;
        object-fit: cover;
        border-radius: 4px;
        display: block;
    }

    .facility-noimg {
        background: #eef3f8;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
    }

    .facility-body {
        flex: 1;
        min-width: 0;
        padding-top: 8px;
    }

    .facility-body h3 {
        font-family: 'Lato', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #184C74;
        margin: 0 0 10px;
    }

    .facility-title-link {
        color: #184C74;
        text-decoration: none;
    }

    .facility-title-link:hover {
        text-decoration: underline;
    }

    .facility-title-link i {
        font-size: 13px;
        margin-left: 6px;
        opacity: .7;
        vertical-align: 2px;
    }

    .facility-title-link .fa-file-pdf {
        color: #dc2626;
        opacity: 1;
    }

    .facility-media a {
        display: block;
    }

    /* Same text style as vision.php (site default: 16px Inter, 1.5, #212529) */
    .facility-body p {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        line-height: 1.5;
        color: #212529;
        margin: 0 0 10px;
        text-align: justify;
        overflow-wrap: anywhere;
    }

    .facility-body p:last-child {
        margin-bottom: 0;
    }

    .facility-empty {
        text-align: center;
        padding: 50px 20px;
        color: #6B7280;
        font-family: 'Inter', sans-serif;
    }

    .facility-empty i {
        font-size: 34px;
        margin-bottom: 12px;
        display: block;
        color: #9CA3AF;
    }

    .hero-content {
        top: 35%;
        background-color: #1F3C44;
        padding: 5px 20px;
    }

    .hero-content h2 {
        color: #fff;
    }

    @media (max-width: 768px) {
        .facility-card {
            flex-direction: column;
            gap: 14px;
        }

        .facility-media {
            flex: none;
            max-width: 100%;
            width: 100%;
        }

        .facility-body {
            padding-top: 0;
        }

        .hero-content h2 {
            font-size: 22px;
        }

        .hero-img {
            height: 150px !important;
        }

        .hero-content {
            top: 70% !important;
            left: 0px;
        }
    }
</style>

<?php include 'footer.php'; ?>
