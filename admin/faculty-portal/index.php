<?php
include 'header.php';

/*
|--------------------------------------------------------------------------
| FACULTY DASHBOARD DATA
|--------------------------------------------------------------------------
*/

$fid = (int)$faculty_id;

function facCount(PDO $pdo, $table, $fid)
{
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE id = ? OR faculty_id = ?");
        $stmt->execute([$fid, $fid]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/* Patents / books are stored as text: one entry per non-empty line */
function facLines($text)
{
    $text = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", (string)$text)));
    if ($text === '') return 0;
    return count(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text)), 'strlen'));
}

$e = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

$journalCount = facCount($pdo, 'journalpub', $fid);
$confCount    = facCount($pdo, 'confrncepub', $fid);
$otherCount   = facCount($pdo, 'indpublication', $fid);
$patentCount  = facLines($currentFaculty['patent'] ?? '');
$bookCount    = facLines($currentFaculty['books'] ?? '');
$pubTotal     = $journalCount + $confCount + $otherCount;


/*
|--------------------------------------------------------------------------
| PUBLICATIONS BY YEAR (journals / conferences / other)
|--------------------------------------------------------------------------
*/

$byYear = [];
$recent = [];

$sources = [
    'journal'    => ['table' => 'journalpub',     'venue' => 'journal',    'label' => 'Journal',     'link' => 'journals.php'],
    'conference' => ['table' => 'confrncepub',    'venue' => 'conference', 'label' => 'Conference',  'link' => 'conferences.php'],
    'other'      => ['table' => 'indpublication', 'venue' => 'journal',    'label' => 'Publication', 'link' => 'other-publications.php'],
];

foreach ($sources as $key => $src) {
    try {
        $stmt = $pdo->prepare("
            SELECT title, `{$src['venue']}` AS venue, year, created_at
            FROM `{$src['table']}`
            WHERE id = ? OR faculty_id = ?
        ");
        $stmt->execute([$fid, $fid]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $yr = preg_match('/(19|20)\d{2}/', (string)$row['year'], $m) ? (int)$m[0] : null;

            if ($yr) {
                $byYear[$yr][$key] = ($byYear[$yr][$key] ?? 0) + 1;
            }

            $recent[] = [
                'type'  => $key,
                'label' => $src['label'],
                'link'  => $src['link'],
                'title' => $row['title'],
                'venue' => $row['venue'],
                'year'  => $yr,
                'yearText' => $row['year'],
                'created' => $row['created_at'],
            ];
        }
    } catch (PDOException $ex) {
        // table missing -> skip
    }
}

ksort($byYear);
$years = array_keys($byYear);
$yearChart = [
    'labels'     => array_map('strval', $years),
    'journal'    => array_map(fn($y) => $byYear[$y]['journal'] ?? 0, $years),
    'conference' => array_map(fn($y) => $byYear[$y]['conference'] ?? 0, $years),
    'other'      => array_map(fn($y) => $byYear[$y]['other'] ?? 0, $years),
];

usort($recent, function ($a, $b) {
    $c = ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
    return $c !== 0 ? $c : strcmp((string)$b['created'], (string)$a['created']);
});
$recent = array_slice($recent, 0, 5);


/*
|--------------------------------------------------------------------------
| PROFILE COMPLETENESS
|--------------------------------------------------------------------------
*/

$f = $currentFaculty;
$filled = function (...$keys) use ($f) {
    foreach ($keys as $k) {
        if (trim(strip_tags((string)($f[$k] ?? ''))) !== '' && trim((string)$f[$k]) !== '#') return true;
    }
    return false;
};

$hasRealPhoto = !empty($f['image']) && strpos($facPhoto, 'dummy.png') === false;

$checks = [
    ['label' => 'Profile photo',          'ok' => $hasRealPhoto,                                 'link' => 'profile.php'],
    ['label' => 'Contact (email, office)', 'ok' => $filled('email') && $filled('office', 'phone'), 'link' => 'profile.php'],
    ['label' => 'Education & degrees',    'ok' => $filled('phd', 'mast', 'bach'),               'link' => 'education.php'],
    ['label' => 'Biography',              'ok' => $filled('about1'),                             'link' => 'biography.php'],
    ['label' => 'Research interests',     'ok' => $filled('resrch'),                             'link' => 'biography.php'],
    ['label' => 'Experience & roles',     'ok' => $filled('Experience', 'admnres', 'prfsnmem'),  'link' => 'experience.php'],
    ['label' => 'Courses taught',         'ok' => $filled('crstaught'),                          'link' => 'teaching.php'],
    ['label' => 'Students guided',        'ok' => $filled('student', 'studentlink'),             'link' => 'teaching.php'],
    ['label' => 'Publications',           'ok' => $pubTotal > 0,                                 'link' => 'journals.php'],
    ['label' => 'Scholar / lab links',    'ok' => $filled('scopuslink', 'lablink'),              'link' => 'profile.php'],
];
$doneCount = count(array_filter($checks, fn($c) => $c['ok']));
$pct = (int)round($doneCount / count($checks) * 100);

$isPersonal = ($f['page_type'] ?? '') === 'personal' && !empty($f['personal_page']) && $f['personal_page'] !== '#';
$fullName   = trim(($f['name'] ?? '') . (!empty($f['lname']) ? ' ' . $f['lname'] : ''));
$jsonFlags  = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

<style>
    .fdash {
        --navy: #184C74;
        --lime: #A7E747;
        --ink: #0f172a;
        --ink-2: #475569;
        --ink-3: #94a3b8;
        --line: #e7ecf2;
        --soft: #f6f8fb;
        --series-1: #2a78d6;   /* validated categorical slots */
        --series-2: #eb6834;
        --series-3: #1baf7a;
        --good: #16a34a;

        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        color: var(--ink);
    }

    .fdash *, .fdash *::before, .fdash *::after { box-sizing: border-box; }

    /* ---------- Hero ---------- */
    .f-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 28px 30px;
        color: #fff;
        background:
            radial-gradient(900px 300px at 100% -20%, rgba(167, 231, 71, .22), transparent 60%),
            radial-gradient(600px 260px at -10% 120%, rgba(56, 189, 248, .16), transparent 60%),
            linear-gradient(135deg, #184C74 0%, #102636 100%);
        box-shadow: 0 18px 40px -18px rgba(24, 76, 116, .55);
        margin-bottom: 22px;
    }

    .f-hero::after {
        content: "";
        position: absolute;
        right: -60px;
        top: -60px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        border: 40px solid rgba(255, 255, 255, .05);
    }

    .f-hero-top {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 20px;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .f-hero-id {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .f-hero-id img {
        width: 78px;
        height: 78px;
        border-radius: 18px;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, .25);
        flex-shrink: 0;
        background: #fff;
    }

    .f-eyebrow {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--lime);
        margin-bottom: 4px;
    }

    .f-hero h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #fff;
    }

    .f-hero-sub {
        margin-top: 4px;
        font-size: 14px;
        color: rgba(255, 255, 255, .8);
    }

    .f-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(167, 231, 71, .16);
        color: #d9f99d;
        border: 1px solid rgba(167, 231, 71, .35);
    }

    .f-badge.ext {
        background: rgba(56, 189, 248, .14);
        color: #bae6fd;
        border-color: rgba(56, 189, 248, .35);
    }

    .f-clock {
        text-align: right;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 14px;
        padding: 12px 16px;
        min-width: 180px;
    }

    .f-clock strong {
        display: block;
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        font-variant-numeric: tabular-nums;
    }

    .f-clock span {
        font-size: 12px;
        color: rgba(255, 255, 255, .75);
    }

    .f-actions {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .f-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 15px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #fff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .16);
        transition: background .2s ease, transform .2s ease;
    }

    .f-action:hover {
        background: rgba(255, 255, 255, .2);
        color: #fff;
        transform: translateY(-1px);
    }

    .f-action.primary {
        background: var(--lime);
        border-color: var(--lime);
        color: #14310a;
    }

    .f-action.primary:hover {
        background: #b8f05f;
        color: #14310a;
    }

    /* ---------- KPI ---------- */
    .f-kpis {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .f-kpi {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 18px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid var(--line);
        text-decoration: none;
        color: inherit;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .f-kpi:hover {
        transform: translateY(-3px);
        border-color: #cfdbe7;
        box-shadow: 0 14px 28px -16px rgba(15, 23, 42, .25);
        color: inherit;
    }

    .f-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .f-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eaf2f9;
        color: var(--navy);
        font-size: 16px;
    }

    .f-kpi-go {
        color: var(--ink-3);
        font-size: 12px;
        transition: transform .2s ease, color .2s ease;
    }

    .f-kpi:hover .f-kpi-go {
        color: var(--navy);
        transform: translateX(3px);
    }

    .f-kpi-value {
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -.03em;
        font-variant-numeric: tabular-nums;
    }

    .f-kpi-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-2);
    }

    /* ---------- Cards ---------- */
    .f-grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 16px;
        margin-bottom: 22px;
    }

    .f-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 20px;
        min-width: 0;
    }

    .f-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
    }

    .f-card-head h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--ink);
    }

    .f-card-head p {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--ink-2);
    }

    .f-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--navy);
        text-decoration: none;
        white-space: nowrap;
    }

    .f-link:hover { text-decoration: underline; }

    /* Chart */
    .f-legend {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 12px;
        color: var(--ink-2);
        margin-bottom: 10px;
    }

    .f-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .f-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }

    .f-chart { position: relative; height: 250px; }

    .f-toggle {
        border: 1px solid var(--line);
        background: var(--soft);
        color: var(--ink-2);
        font-size: 11px;
        font-weight: 600;
        padding: 4px 9px;
        border-radius: 7px;
        cursor: pointer;
        font-family: inherit;
    }

    .f-toggle:hover { color: var(--navy); }

    .f-table {
        display: none;
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .f-table th, .f-table td {
        text-align: left;
        padding: 8px 10px;
        border-bottom: 1px solid var(--line);
    }

    .f-table th { background: var(--soft); color: var(--ink-2); font-size: 12px; }
    .f-table .num { text-align: right; font-variant-numeric: tabular-nums; }

    .show-table .f-chart, .show-table .f-legend { display: none; }
    .show-table .f-table { display: table; }

    .f-empty {
        text-align: center;
        padding: 46px 20px;
        color: var(--ink-3);
        font-size: 13px;
    }

    .f-empty i {
        font-size: 30px;
        display: block;
        margin-bottom: 10px;
        color: #cbd5e1;
    }

    /* Completeness */
    .f-ring-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 10px;
    }

    .f-ring {
        --p: 0;
        width: 78px;
        height: 78px;
        border-radius: 50%;
        flex-shrink: 0;
        background:
            radial-gradient(closest-side, #fff 76%, transparent 77% 100%),
            conic-gradient(var(--good) calc(var(--p) * 1%), #e8edf2 0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
    }

    .f-ring-row p {
        margin: 0;
        font-size: 13px;
        color: var(--ink-2);
        line-height: 1.5;
    }

    .f-checks {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .f-checks a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        border-bottom: 1px dashed var(--line);
        font-size: 13px;
        color: var(--ink);
        text-decoration: none;
    }

    .f-checks li:last-child a { border-bottom: none; }
    .f-checks a:hover { color: var(--navy); }

    .f-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .f-pill.ok   { background: #dcfce7; color: #166534; }
    .f-pill.warn { background: #fef3c7; color: #92400e; }

    /* Recent publications */
    .f-feed {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .f-feed li {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px dashed var(--line);
    }

    .f-feed li:last-child { border-bottom: none; }

    .f-feed-ico {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: #eaf2f9;
        color: var(--navy);
    }

    .f-feed-body { min-width: 0; }

    .f-feed-body strong {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
    }

    .f-feed-body small {
        display: block;
        font-size: 11.5px;
        color: var(--ink-3);
        margin-top: 3px;
    }

    .f-tag {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 5px;
        background: var(--soft);
        color: var(--ink-2);
        margin-right: 4px;
    }

    /* Shortcuts */
    .f-short {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .f-short a {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid var(--line);
        background: #fff;
        text-decoration: none;
        color: var(--ink);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .f-short a:hover {
        transform: translateY(-2px);
        border-color: #cfdbe7;
        box-shadow: 0 12px 24px -18px rgba(15, 23, 42, .3);
        color: var(--ink);
    }

    .f-short i {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--soft);
        color: var(--navy);
        transition: background .2s ease, color .2s ease;
    }

    .f-short a:hover i { background: var(--navy); color: #fff; }

    .f-short strong { display: block; font-size: 13.5px; }
    .f-short small { display: block; font-size: 11.5px; color: var(--ink-3); margin-top: 2px; line-height: 1.45; }

    @media (prefers-reduced-motion: no-preference) {
        .f-rise { opacity: 0; transform: translateY(10px); animation: fRise .5s ease forwards; }
        .f-rise:nth-child(2) { animation-delay: .05s; }
        .f-rise:nth-child(3) { animation-delay: .10s; }
        .f-rise:nth-child(4) { animation-delay: .15s; }
        .f-rise:nth-child(5) { animation-delay: .20s; }
        @keyframes fRise { to { opacity: 1; transform: none; } }
    }

    @media (max-width: 1300px) {
        .f-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 1100px) {
        .f-grid { grid-template-columns: 1fr; }
        .f-short { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 700px) {
        .f-hero { padding: 22px; }
        .f-hero h1 { font-size: 21px; }
        .f-clock { text-align: left; }
        .f-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .f-short { grid-template-columns: 1fr; }
    }
</style>


<div class="fdash">

    <!-- HERO -->
    <section class="f-hero">
        <div class="f-hero-top">
            <div class="f-hero-id">
                <img src="<?= $e($facPhoto) ?>" alt="">
                <div style="min-width:0;">
                    <div class="f-eyebrow"><span id="fGreeting">Welcome</span></div>
                    <h1><?= $e($fullName) ?></h1>
                    <div class="f-hero-sub">
                        <?= $e($f['designation'] ?: 'Faculty') ?><?= !empty($f['department']) ? ' · ' . $e($f['department']) : '' ?>
                    </div>
                    <?php if ($isPersonal): ?>
                        <span class="f-badge ext"><i class="fa-solid fa-arrow-up-right-from-square"></i> External personal page</span>
                    <?php else: ?>
                        <span class="f-badge"><i class="fa-solid fa-circle-check"></i> SIDIS inner page active</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="f-clock">
                <strong id="fTime">--:--</strong>
                <span id="fDate"></span>
            </div>
        </div>

        <div class="f-actions">
            <a href="profile.php" class="f-action primary"><i class="fa-solid fa-pen"></i> Edit Profile</a>
            <a href="add-journal.php" class="f-action"><i class="fa-solid fa-book-open"></i> Add Journal Article</a>
            <a href="add-conference.php" class="f-action"><i class="fa-solid fa-users-line"></i> Add Conference</a>
            <?php if ($isPersonal): ?>
                <a href="<?= $e($f['personal_page']) ?>" target="_blank" rel="noopener" class="f-action"><i class="fa-solid fa-globe"></i> Open Personal Page</a>
            <?php else: ?>
                <a href="../../innerfaculty.php?id=<?= $fid ?>" target="_blank" rel="noopener" class="f-action"><i class="fa-solid fa-globe"></i> View Live Page</a>
            <?php endif; ?>
        </div>
    </section>


    <!-- KPI TILES -->
    <section class="f-kpis">
        <a href="journals.php" class="f-kpi f-rise">
            <div class="f-kpi-top"><span class="f-kpi-icon"><i class="fa-solid fa-book-open"></i></span><i class="fa-solid fa-arrow-right f-kpi-go"></i></div>
            <div class="f-kpi-value" data-count="<?= $journalCount ?>"><?= $journalCount ?></div>
            <div class="f-kpi-label">Journal Articles</div>
        </a>
        <a href="conferences.php" class="f-kpi f-rise">
            <div class="f-kpi-top"><span class="f-kpi-icon"><i class="fa-solid fa-users-line"></i></span><i class="fa-solid fa-arrow-right f-kpi-go"></i></div>
            <div class="f-kpi-value" data-count="<?= $confCount ?>"><?= $confCount ?></div>
            <div class="f-kpi-label">Conferences</div>
        </a>
        <a href="other-publications.php" class="f-kpi f-rise">
            <div class="f-kpi-top"><span class="f-kpi-icon"><i class="fa-solid fa-file-lines"></i></span><i class="fa-solid fa-arrow-right f-kpi-go"></i></div>
            <div class="f-kpi-value" data-count="<?= $otherCount ?>"><?= $otherCount ?></div>
            <div class="f-kpi-label">Other Publications</div>
        </a>
        <a href="patents.php" class="f-kpi f-rise">
            <div class="f-kpi-top"><span class="f-kpi-icon"><i class="fa-solid fa-stamp"></i></span><i class="fa-solid fa-arrow-right f-kpi-go"></i></div>
            <div class="f-kpi-value" data-count="<?= $patentCount ?>"><?= $patentCount ?></div>
            <div class="f-kpi-label">Patents</div>
        </a>
        <a href="books.php" class="f-kpi f-rise">
            <div class="f-kpi-top"><span class="f-kpi-icon"><i class="fa-solid fa-book"></i></span><i class="fa-solid fa-arrow-right f-kpi-go"></i></div>
            <div class="f-kpi-value" data-count="<?= $bookCount ?>"><?= $bookCount ?></div>
            <div class="f-kpi-label">Books &amp; Chapters</div>
        </a>
    </section>


    <!-- CHART + COMPLETENESS -->
    <section class="f-grid">

        <div class="f-card" id="fCardYear">
            <div class="f-card-head">
                <div>
                    <h4>Publications by year</h4>
                    <p><?= $pubTotal ?> <?= $pubTotal === 1 ? 'publication' : 'publications' ?> in total</p>
                </div>
                <?php if (!empty($years)): ?>
                    <button type="button" class="f-toggle" data-target="fCardYear">Table</button>
                <?php endif; ?>
            </div>

            <?php if (!empty($years)): ?>
                <div class="f-legend">
                    <span><i style="background:var(--series-1)"></i> Journals</span>
                    <span><i style="background:var(--series-2)"></i> Conferences</span>
                    <span><i style="background:var(--series-3)"></i> Other</span>
                </div>
                <div class="f-chart"><canvas id="fChartYear" role="img" aria-label="Publications by year"></canvas></div>

                <table class="f-table">
                    <thead><tr><th>Year</th><th class="num">Journals</th><th class="num">Conferences</th><th class="num">Other</th></tr></thead>
                    <tbody>
                        <?php foreach ($yearChart['labels'] as $i => $y): ?>
                            <tr>
                                <td><?= $e($y) ?></td>
                                <td class="num"><?= $yearChart['journal'][$i] ?></td>
                                <td class="num"><?= $yearChart['conference'][$i] ?></td>
                                <td class="num"><?= $yearChart['other'][$i] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="f-empty">
                    <i class="fa-solid fa-chart-column"></i>
                    No publications with a year yet.<br>
                    <a href="add-journal.php" class="f-link">Add your first journal article</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="f-card">
            <div class="f-card-head">
                <div>
                    <h4>Profile completeness</h4>
                    <p>What visitors see on your page</p>
                </div>
            </div>

            <div class="f-ring-row">
                <div class="f-ring" style="--p: <?= $pct ?>;"><?= $pct ?>%</div>
                <p><strong><?= $doneCount ?> of <?= count($checks) ?></strong> sections filled.<br>
                    <?= $doneCount === count($checks) ? 'Your profile is complete.' : 'Fill the highlighted sections.' ?></p>
            </div>

            <ul class="f-checks">
                <?php foreach ($checks as $c): ?>
                    <li>
                        <a href="<?= $e($c['link']) ?>">
                            <?= $e($c['label']) ?>
                            <?php if ($c['ok']): ?>
                                <span class="f-pill ok"><i class="fa-solid fa-check"></i> Done</span>
                            <?php else: ?>
                                <span class="f-pill warn"><i class="fa-solid fa-plus"></i> Add</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </section>


    <!-- RECENT + SHORTCUTS -->
    <section class="f-grid" style="grid-template-columns: 1fr 1.3fr;">

        <div class="f-card">
            <div class="f-card-head">
                <div>
                    <h4>Recent publications</h4>
                    <p>Latest by year</p>
                </div>
                <a href="journals.php" class="f-link">Manage</a>
            </div>

            <?php if (!empty($recent)): ?>
                <ul class="f-feed">
                    <?php foreach ($recent as $r): ?>
                        <?php $ico = $r['type'] === 'journal' ? 'fa-book-open' : ($r['type'] === 'conference' ? 'fa-users-line' : 'fa-file-lines'); ?>
                        <li>
                            <span class="f-feed-ico"><i class="fa-solid <?= $ico ?>"></i></span>
                            <div class="f-feed-body">
                                <strong><?= $e($r['title']) ?></strong>
                                <small>
                                    <span class="f-tag"><?= $e($r['label']) ?></span>
                                    <?= $e(trim(($r['venue'] ?? '') . ($r['yearText'] !== '' ? ' · ' . $r['yearText'] : ''), ' ·')) ?>
                                </small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="f-empty"><i class="fa-solid fa-book-open"></i>No publications yet.</div>
            <?php endif; ?>
        </div>

        <div class="f-card">
            <div class="f-card-head">
                <div>
                    <h4>Manage your sections</h4>
                    <p>Everything shown on your faculty page</p>
                </div>
            </div>

            <div class="f-short">
                <a href="profile.php"><i class="fa-solid fa-id-card"></i><span><strong>Profile &amp; Contact</strong><small>Name, photo, office, links, page type</small></span></a>
                <a href="education.php"><i class="fa-solid fa-graduation-cap"></i><span><strong>Education</strong><small>Bachelor's, Master's, Ph.D.</small></span></a>
                <a href="biography.php"><i class="fa-solid fa-microscope"></i><span><strong>Biography &amp; Research</strong><small>Bio, interests, lab group</small></span></a>
                <a href="experience.php"><i class="fa-solid fa-briefcase"></i><span><strong>Experience &amp; Roles</strong><small>Background, roles, memberships</small></span></a>
                <a href="teaching.php"><i class="fa-solid fa-chalkboard-user"></i><span><strong>Teaching &amp; Students</strong><small>Courses, students guided</small></span></a>
                <a href="settings.php"><i class="fa-solid fa-gear"></i><span><strong>Settings</strong><small>Username &amp; password</small></span></a>
            </div>
        </div>

    </section>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function () {

    /* Greeting + clock (viewer's local time) */
    function tick() {
        var now = new Date();
        var h = now.getHours();
        document.getElementById('fGreeting').textContent =
            h < 12 ? 'Good morning' : (h < 17 ? 'Good afternoon' : 'Good evening');
        document.getElementById('fTime').textContent =
            now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        document.getElementById('fDate').textContent =
            now.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    tick();
    setInterval(tick, 30000);

    /* Count-up */
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce) {
        document.querySelectorAll('.fdash [data-count]').forEach(function (el) {
            var target = parseInt(el.dataset.count, 10) || 0;
            if (target === 0) return;
            var start = null;
            el.textContent = '0';
            requestAnimationFrame(function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / 800, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            });
        });
    }

    /* Chart / table toggle */
    document.querySelectorAll('.f-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var on = document.getElementById(btn.dataset.target).classList.toggle('show-table');
            btn.textContent = on ? 'Chart' : 'Table';
        });
    });

    var canvas = document.getElementById('fChartYear');
    if (!canvas) return;

    if (typeof Chart === 'undefined') {
        document.getElementById('fCardYear').classList.add('show-table');
        document.querySelector('.f-toggle').style.display = 'none';
        return;
    }

    var css = getComputedStyle(document.querySelector('.fdash'));
    var S = ['--series-1', '--series-2', '--series-3'].map(function (v) { return css.getPropertyValue(v).trim(); });

    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = css.getPropertyValue('--ink-2').trim();

    var bar = { borderRadius: 4, borderSkipped: 'start', maxBarThickness: 26 };
    var data = <?= json_encode($yearChart, $jsonFlags) ?>;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                Object.assign({ label: 'Journals',    data: data.journal,    backgroundColor: S[0] }, bar),
                Object.assign({ label: 'Conferences', data: data.conference, backgroundColor: S[1] }, bar),
                Object.assign({ label: 'Other',       data: data.other,      backgroundColor: S[2] }, bar)
            ]
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            datasets: { bar: { categoryPercentage: 0.6, barPercentage: 0.85 } },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8, boxWidth: 8, boxHeight: 8, boxPadding: 4 }
            },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' }, border: { display: false } }
            }
        }
    });

})();
</script>

<?php include 'footer.php'; ?>
