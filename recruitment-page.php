<?php
/*
|--------------------------------------------------------------------------
| Shared renderer for "Join Us" pages (Faculty Recruitment / Careers)
|--------------------------------------------------------------------------
|
| Before including this file, the calling page must set:
|   $recruitmentPage   -> 'faculty' or 'careers'  (recruitment.page column)
|   $recruitmentTitle  -> hero title, e.g. 'Faculty Recruitment'
|   $recruitmentIntro  -> sub-heading, e.g. 'IIT Madras – Faculty Recruitment'
|
| All rows come from the single `recruitment` table, grouped by `heading`.
|
*/

include 'header.php';
require_once __DIR__ . '/config.php';

$recruitmentPage  = $recruitmentPage  ?? 'faculty';
$recruitmentTitle = $recruitmentTitle ?? 'Faculty Recruitment';
$recruitmentIntro = $recruitmentIntro ?? '';
$recruitmentUnderConstruction = $recruitmentUnderConstruction ?? false;

/* $blocks[title][heading][] = row   (Title -> Table Heading -> Rows) */
$blocks = [];

try {
    $stmt = $pdo->prepare("
        SELECT title, heading, label, row_type, content, link_url, link_text
        FROM recruitment
        WHERE page = ? AND status = 1
        ORDER BY title_order ASC, title ASC, heading_order ASC, heading ASC, display_order ASC, id ASC
    ");
    $stmt->execute([$recruitmentPage]);

    foreach ($stmt->fetchAll() as $row) {
        $heading = trim($row['heading'] ?? '');
        if ($heading === '') {
            continue;
        }
        $title = trim($row['title'] ?? '');
        if ($title === '') {
            $title = $recruitmentIntro;   // fallback for rows without a title
        }
        $blocks[$title][$heading][] = $row;
    }
} catch (PDOException $e) {
    error_log('Recruitment fetch error: ' . $e->getMessage());
    $blocks = [];
}

if (!function_exists('recruitmentSafeUrl')) {
    /* Allow only http(s), mailto and relative links (blocks javascript: etc.) */
    function recruitmentSafeUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(https?://|mailto:)#i', $url) || !preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) {
            return $url;
        }
        return '';
    }
}

$e = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
?>

<!-- HERO -->
<section class="hero">
    <img src="images/about-banner.png" class="hero-img" style="height: auto;" alt="<?= $e($recruitmentTitle) ?>">
    <div class="container">
        <div class="hero-content about-section">
            <h2><?= $e($recruitmentTitle) ?></h2>
        </div>
    </div>
</section>

<section class="recruitment-section">
    <div class="container">

        <?php if ($recruitmentUnderConstruction): ?>

            <div class="recruitment-construction">
                <div class="recruitment-construction-icon">
                    <i class="fa-solid fa-person-digging"></i>
                </div>
                <h3>Page Under Construction</h3>
                <p>We are working on this page. Career opportunities at SIDiS will be published here soon.</p>
                <p>Meanwhile, you can view current <a href="faculty-recruitment.php">Faculty Recruitment</a> openings.</p>
            </div>

        <?php elseif (!empty($blocks)): ?>

            <?php foreach ($blocks as $title => $sections): ?>
                <div class="recruitment-block">

                    <?php if ($title !== ''): ?>
                        <h3 class="clusters-side-heading"><?= $e($title) ?></h3>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="recruitment-table">
                            <?php $rowIndex = 0; ?>
                            <?php foreach ($sections as $heading => $rows): ?>
                                <tbody>
                                    <tr class="recruitment-heading">
                                        <th colspan="2"><?= $e($heading) ?></th>
                                    </tr>

                                    <?php foreach ($rows as $row): ?>
                                        <?php
                                        $rowIndex++;
                                        $isButton = ($row['row_type'] ?? 'text') === 'button';
                                        $url      = $isButton ? recruitmentSafeUrl($row['link_url'] ?? '') : '';
                                        $linkText = trim($row['link_text'] ?? '') !== '' ? $row['link_text'] : 'Click Here';
                                        $content  = trim($row['content'] ?? '');
                                        $external = preg_match('#^https?://#i', $url);
                                        ?>
                                        <tr class="<?= $rowIndex % 2 === 1 ? 'row-highlight' : '' ?>">
                                            <td class="recruitment-label"><?= $e($row['label']) ?></td>
                                            <td class="recruitment-value">
                                                <?php if ($isButton && $url !== ''): ?>
                                                    <a href="<?= $e($url) ?>" class="recruitment-btn"
                                                        <?= $external ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                                        <?= $e($linkText) ?>
                                                    </a>
                                                <?php elseif (!$isButton && $content !== ''): ?>
                                                    <p><?= nl2br($e($content)) ?></p>
                                                <?php else: ?>
                                                    <p>-</p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php endforeach; ?>
                        </table>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php else: ?>

            <div class="recruitment-empty">
                <i class="fa-regular fa-folder-open"></i>
                <p>There are no openings at the moment. Please check back later.</p>
            </div>

        <?php endif; ?>

    </div>
</section>

<style>
    .recruitment-section {
        padding: 50px 0 70px;
    }

    .recruitment-block + .recruitment-block {
        margin-top: 50px;
    }

    .recruitment-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recruitment-table tbody {
        border-top: 4px solid #fff;
    }

    .recruitment-heading th {
        background-color: #1F3C44;
        color: #fff;
        font-family: 'Lato', sans-serif;
        font-size: 17px;
        font-weight: 600;
        padding: 12px 18px;
    }

    .recruitment-table td {
        padding: 22px 18px;
        vertical-align: middle;
        background-color: #fff;
    }

    .recruitment-table tr.row-highlight td {
        background-color: #F4E796;
    }

    .recruitment-label {
        width: 31%;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: #000;
        border-right: 2px solid #fff;
    }

    .recruitment-value p {
        margin: 0;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #184C74;
    }

    .recruitment-value p + .recruitment-btn {
        margin-top: 10px;
    }

    .recruitment-btn {
        display: inline-block;
        background-color: #A6E22E;
        color: #000;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        padding: 6px 14px;
        text-decoration: none;
        border-radius: 2px;
        transition: background-color .2s ease;
    }

    .recruitment-btn:hover {
        background-color: #8cc81f;
        color: #000;
    }

    .recruitment-empty {
        text-align: center;
        padding: 50px 20px;
        color: #6B7280;
        font-family: 'Inter', sans-serif;
    }

    .recruitment-construction {
        max-width: 640px;
        margin: 10px auto;
        text-align: center;
        padding: 50px 30px;
        border: 1px dashed #CBD5E1;
        border-radius: 10px;
        background: #FAFBFC;
        font-family: 'Inter', sans-serif;
    }

    .recruitment-construction-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background: #F4E796;
        color: #1F3C44;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
    }

    .recruitment-construction h3 {
        font-family: 'Lato', sans-serif;
        color: #184C74;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .recruitment-construction p {
        color: #4B5563;
        font-size: 15px;
        margin: 6px 0;
    }

    .recruitment-construction a {
        color: #184C74;
        font-weight: 600;
    }

    .recruitment-empty i {
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

    .clusters-side-heading {
        font-family: 'Lato', sans-serif;
        font-size: 26px;
        color: #184C74;
        font-weight: 600;
        padding-bottom: 15px;
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
            left: 0px;
        }

        .recruitment-label {
            width: 38%;
        }

        .recruitment-table td {
            padding: 16px 12px;
        }
    }
</style>

<?php include 'footer.php'; ?>
