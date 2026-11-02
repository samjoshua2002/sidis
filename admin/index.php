<?php

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| SAFE QUERY HELPERS
|--------------------------------------------------------------------------
*/

function getTableCount($pdo, $table, $where = '')
{
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`" . ($where !== '' ? " WHERE $where" : ''));
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function getRows($pdo, $sql)
{
    try {
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/* Sort "Jan 2025", "Jul 2025" ... chronologically, unknown formats last */
function sortBatches(array $batches)
{
    usort($batches, function ($a, $b) {
        $da = DateTime::createFromFormat('!M Y', $a);
        $db = DateTime::createFromFormat('!M Y', $b);
        if ($da && $db) return $da <=> $db;
        if ($da) return -1;
        if ($db) return 1;
        return strcasecmp($a, $b);
    });
    return $batches;
}

$e = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};


/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$facultyCount      = getTableCount($pdo, 'faculty', 'status = 1');
$staffCount        = getTableCount($pdo, 'staff');
$msCount           = getTableCount($pdo, 'ms');
$phdCount          = getTableCount($pdo, 'phd_scholars');
$mtechCount        = getTableCount($pdo, 'mtech_students');
$clusterCount      = getTableCount($pdo, 'clusters');
$newsCount         = getTableCount($pdo, 'news_events');
$announcementCount = getTableCount($pdo, 'announcements');
$researchCount     = getTableCount($pdo, 'featured_research');
$recruitmentCount  = getTableCount($pdo, 'recruitment', "page IN ('faculty', 'postdoc', 'staff') AND status = 1");
$userCount         = getTableCount($pdo, 'users');
$studentTotal      = $msCount + $phdCount + $mtechCount;


/*
|--------------------------------------------------------------------------
| FACULTY BREAKDOWN (same rules as the website navigation)
|--------------------------------------------------------------------------
*/

$facultyBreakdown = ['Core' => 0, 'Associate' => 0, 'Adjunct' => 0, 'PoP' => 0];

foreach (getRows($pdo, "
    SELECT
        SUM(top_order = 1) AS core,
        SUM(top_order = 2) AS adjunct,
        SUM(top_order = 3) AS pop,
        SUM(top_order = 0 AND cluster_id IS NOT NULL AND cluster_id <> '') AS associate
    FROM faculty WHERE status = 1
") as $r) {
    $facultyBreakdown = [
        'Core'      => (int)$r['core'],
        'Associate' => (int)$r['associate'],
        'Adjunct'   => (int)$r['adjunct'],
        'PoP'       => (int)$r['pop'],
    ];
}

$facultySub = [];
foreach ($facultyBreakdown as $k => $v) {
    if ($v > 0) $facultySub[] = $v . ' ' . $k;
}


/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

/* Intake by batch: M.S. vs Ph.D. */
$msBatch  = [];
$phdBatch = [];
foreach (getRows($pdo, "SELECT TRIM(batch) b, COUNT(*) c FROM ms WHERE TRIM(batch) <> '' GROUP BY TRIM(batch)") as $r) {
    $msBatch[$r['b']] = (int)$r['c'];
}
foreach (getRows($pdo, "SELECT TRIM(batch) b, COUNT(*) c FROM phd_scholars WHERE TRIM(batch) <> '' GROUP BY TRIM(batch)") as $r) {
    $phdBatch[$r['b']] = (int)$r['c'];
}
$batchLabels = sortBatches(array_values(array_unique(array_merge(array_keys($msBatch), array_keys($phdBatch)))));

$batchChart = [
    'labels' => $batchLabels,
    'ms'     => array_map(fn($b) => $msBatch[$b] ?? 0, $batchLabels),
    'phd'    => array_map(fn($b) => $phdBatch[$b] ?? 0, $batchLabels),
];

/* Student mix */
$mixChart = [
    'labels' => ['M.Tech', 'M.S.', 'Ph.D.'],
    'values' => [$mtechCount, $msCount, $phdCount],
];

/* M.Tech programmes: short label from "(...)" */
$programmeChart = ['labels' => [], 'full' => [], 'values' => []];
foreach (getRows($pdo, "
    SELECT TRIM(programme) p, COUNT(*) c
    FROM mtech_students
    WHERE TRIM(programme) <> ''
    GROUP BY TRIM(programme)
    ORDER BY c DESC
") as $r) {
    $short = preg_match('/\(([^)]+)\)\s*$/', $r['p'], $m) ? $m[1] : $r['p'];
    $programmeChart['labels'][] = $short;
    $programmeChart['full'][]   = $r['p'];
    $programmeChart['values'][] = (int)$r['c'];
}


/*
|--------------------------------------------------------------------------
| RECENT CONTENT
|--------------------------------------------------------------------------
*/

$recentNews = getRows($pdo, "SELECT title, created_at, status FROM news_events ORDER BY created_at DESC, id DESC LIMIT 5");
$recentAnn  = getRows($pdo, "SELECT title, date_text, created_at, status FROM announcements ORDER BY created_at DESC, id DESC LIMIT 5");
$dates      = getRows($pdo, "SELECT schedule_name, description, date_schedule FROM important_dates ORDER BY id ASC LIMIT 5");


/*
|--------------------------------------------------------------------------
| WEBSITE CONTENT STATUS
|--------------------------------------------------------------------------
*/

$health = [
    ['label' => 'Hero banner',          'ok' => getTableCount($pdo, 'hero_section') > 0,          'link' => 'hero.php'],
    ['label' => "Director's message",   'ok' => getTableCount($pdo, 'message_from_director') > 0, 'link' => 'director-message.php'],
    ['label' => "Head's message",       'ok' => getTableCount($pdo, 'head_message') > 0,          'link' => 'head-message.php'],
    ['label' => 'News & events',        'ok' => $newsCount > 0,                                   'link' => 'news.php'],
    ['label' => 'Announcements',        'ok' => $announcementCount > 0,                           'link' => 'announcements.php'],
    ['label' => 'Featured research',    'ok' => $researchCount > 0,                               'link' => 'research.php'],
    ['label' => 'Faculty recruitment',  'ok' => $recruitmentCount > 0,                            'link' => 'faculty_recruitment.php'],
    ['label' => 'Contact details',      'ok' => getTableCount($pdo, 'contact') > 0,               'link' => 'contact.php'],
];
$healthOk  = count(array_filter($health, fn($h) => $h['ok']));
$healthPct = (int)round($healthOk / max(count($health), 1) * 100);

$fmtDate = function ($d) {
    $t = strtotime((string)$d);
    return $t ? date('d M Y', $t) : '';
};

$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

<style>

/* =========================================================
   MODERN DASHBOARD
========================================================= */

.dash {
    --navy:        #184C74;
    --navy-deep:   #1F3C44;
    --lime:        #A7E747;
    --sun:         #F4E796;
    --ink:         #0f172a;
    --ink-2:       #475569;
    --ink-3:       #94a3b8;
    --line:        #e7ecf2;
    --card:        #ffffff;
    --soft:        #f6f8fb;
    --series-1:    #2a78d6;   /* validated categorical slot 1 */
    --series-2:    #eb6834;   /* validated categorical slot 2 */
    --good:        #16a34a;
    --warn:        #d97706;

    font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
    color: var(--ink);
    width: 100%;
}

.dash *,
.dash *::before,
.dash *::after { box-sizing: border-box; }

/* ---------- Hero ---------- */
.dash-hero {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    padding: 30px 32px;
    color: #fff;
    background:
        radial-gradient(900px 300px at 100% -20%, rgba(167, 231, 71, .22), transparent 60%),
        radial-gradient(600px 260px at -10% 120%, rgba(244, 231, 150, .18), transparent 60%),
        linear-gradient(135deg, #184C74 0%, #1F3C44 100%);
    box-shadow: 0 18px 40px -18px rgba(24, 76, 116, .55);
    margin-bottom: 24px;
}

.dash-hero::after {
    content: "";
    position: absolute;
    right: -60px;
    top: -60px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    border: 40px solid rgba(255, 255, 255, .05);
}

.dash-hero-top {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
}

.dash-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--lime);
    margin-bottom: 8px;
}

.dash-hero h1 {
    margin: 0 0 6px;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -.02em;
    color: #fff;
}

.dash-hero p {
    margin: 0;
    color: rgba(255, 255, 255, .78);
    font-size: 14px;
    max-width: 560px;
    line-height: 1.6;
}

.dash-clock {
    text-align: right;
    background: rgba(255, 255, 255, .08);
    border: 1px solid rgba(255, 255, 255, .14);
    backdrop-filter: blur(6px);
    border-radius: 14px;
    padding: 12px 16px;
    min-width: 190px;
}

.dash-clock strong {
    display: block;
    font-size: 24px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #fff;
}

.dash-clock span {
    font-size: 12px;
    color: rgba(255, 255, 255, .75);
}

.dash-actions {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.dash-action {
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

.dash-action:hover {
    background: rgba(255, 255, 255, .2);
    transform: translateY(-1px);
    color: #fff;
}

.dash-action.primary {
    background: var(--lime);
    border-color: var(--lime);
    color: #14310a;
}

.dash-action.primary:hover {
    background: #b8f05f;
    color: #14310a;
}

/* ---------- Section head ---------- */
.dash-section {
    margin-bottom: 24px;
}

.dash-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}

.dash-head h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--ink);
    letter-spacing: -.01em;
}

.dash-head p {
    margin: 3px 0 0;
    font-size: 13px;
    color: var(--ink-2);
}

/* ---------- KPI tiles ---------- */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
}

.kpi {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px;
    border-radius: 16px;
    background: var(--card);
    border: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.kpi:hover {
    transform: translateY(-3px);
    border-color: #cfdbe7;
    box-shadow: 0 14px 28px -16px rgba(15, 23, 42, .25);
}

.kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: var(--navy);
    background: #eaf2f9;
}

.kpi-go {
    color: var(--ink-3);
    font-size: 12px;
    transition: transform .2s ease, color .2s ease;
}

.kpi:hover .kpi-go {
    color: var(--navy);
    transform: translateX(3px);
}

.kpi-value {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
}

.kpi-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-2);
}

.kpi-sub {
    font-size: 12px;
    color: var(--ink-3);
    margin-top: -6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ---------- Cards ---------- */
.dash-grid-2 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 16px;
}

.dash-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 20px;
    min-width: 0;
}

.card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 14px;
}

.card-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--ink);
}

.card-head p {
    margin: 3px 0 0;
    font-size: 12px;
    color: var(--ink-2);
}

.card-link {
    font-size: 12px;
    font-weight: 600;
    color: var(--navy);
    text-decoration: none;
    white-space: nowrap;
}

.card-link:hover { text-decoration: underline; }

/* ---------- Charts ---------- */
.chart-box {
    position: relative;
    height: 260px;
}

.chart-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 12px;
    color: var(--ink-2);
    margin-bottom: 10px;
}

.chart-legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.chart-legend i {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    display: inline-block;
}

.view-toggle {
    border: 1px solid var(--line);
    background: var(--soft);
    color: var(--ink-2);
    font-size: 11px;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 7px;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
}

.view-toggle:hover {
    color: var(--navy);
    border-color: #cfdbe7;
}

.chart-table {
    display: none;
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.chart-table th,
.chart-table td {
    text-align: left;
    padding: 8px 10px;
    border-bottom: 1px solid var(--line);
}

.chart-table th {
    color: var(--ink-2);
    font-weight: 600;
    font-size: 12px;
    background: var(--soft);
}

.chart-table td.num,
.chart-table th.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.show-table .chart-box,
.show-table .chart-legend { display: none; }

.show-table .chart-table { display: table; }

/* ---------- Lists ---------- */
.feed {
    list-style: none;
    margin: 0;
    padding: 0;
}

.feed li {
    display: flex;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px dashed var(--line);
}

.feed li:last-child { border-bottom: none; }

.feed-dot {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: var(--navy);
    background: #eaf2f9;
}

.feed-dot.sun {
    background: #fbf5cf;
    color: #7a5b00;
}

.feed-dot.lime {
    background: #eef9dd;
    color: #2f5d07;
}

.feed-body {
    min-width: 0;
}

.feed-body strong {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    color: var(--ink);
}

.feed-body small {
    display: block;
    font-size: 11px;
    color: var(--ink-3);
    margin-top: 3px;
}

.feed-empty {
    font-size: 13px;
    color: var(--ink-3);
    padding: 14px 0;
}

/* ---------- Health ---------- */
.health-ring {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
}

.ring {
    --p: 0;
    width: 74px;
    height: 74px;
    border-radius: 50%;
    flex-shrink: 0;
    background:
        radial-gradient(closest-side, #fff 76%, transparent 77% 100%),
        conic-gradient(var(--good) calc(var(--p) * 1%), #e8edf2 0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    font-weight: 800;
    color: var(--ink);
}

.health-ring p {
    margin: 0;
    font-size: 13px;
    color: var(--ink-2);
    line-height: 1.5;
}

.health-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.health-list li a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed var(--line);
    font-size: 13px;
    color: var(--ink);
    text-decoration: none;
}

.health-list li:last-child a { border-bottom: none; }

.health-list li a:hover { color: var(--navy); }

.pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
}

.pill.ok   { background: #dcfce7; color: #166534; }
.pill.warn { background: #fef3c7; color: #92400e; }

/* ---------- Shortcuts ---------- */
.shortcut-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
}

.shortcut {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 16px;
    border-radius: 14px;
    background: var(--card);
    border: 1px solid var(--line);
    text-decoration: none;
    color: var(--ink);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.shortcut:hover {
    transform: translateY(-3px);
    border-color: #cfdbe7;
    box-shadow: 0 14px 28px -18px rgba(15, 23, 42, .28);
    color: var(--ink);
}

.shortcut i {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--soft);
    color: var(--navy);
    font-size: 15px;
    transition: background .2s ease, color .2s ease;
}

.shortcut:hover i {
    background: var(--navy);
    color: #fff;
}

.shortcut strong {
    font-size: 13px;
    font-weight: 700;
}

.shortcut small {
    font-size: 11px;
    color: var(--ink-3);
    line-height: 1.45;
    margin-top: -6px;
}

/* ---------- Entrance animation ---------- */
@media (prefers-reduced-motion: no-preference) {
    .reveal {
        opacity: 0;
        transform: translateY(10px);
        animation: dashIn .5s ease forwards;
    }

    .reveal:nth-child(2) { animation-delay: .05s; }
    .reveal:nth-child(3) { animation-delay: .10s; }
    .reveal:nth-child(4) { animation-delay: .15s; }
    .reveal:nth-child(5) { animation-delay: .20s; }
    .reveal:nth-child(6) { animation-delay: .25s; }

    @keyframes dashIn {
        to { opacity: 1; transform: none; }
    }
}

/* ---------- Responsive ---------- */
@media (max-width: 1400px) {
    .kpi-grid      { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .shortcut-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

@media (max-width: 1100px) {
    .dash-grid-2,
    .dash-grid-3 { grid-template-columns: 1fr; }
}

@media (max-width: 700px) {
    .dash-hero      { padding: 22px; }
    .dash-hero h1   { font-size: 22px; }
    .dash-clock     { text-align: left; }
    .kpi-grid       { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .shortcut-grid  { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

</style>


<div class="dash">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="dash-hero">

        <div class="dash-hero-top">
            <div>
                <div class="dash-eyebrow"><i class="fas fa-circle" style="font-size:7px;"></i> SIDiS Administration</div>
                <h1><span id="dashGreeting">Welcome</span>, Admin</h1>
                <p>Here's what's on the School of Interdisciplinary Studies website today: people, students, content and recruitment at a glance.</p>
            </div>

            <div class="dash-clock" aria-live="off">
                <strong id="dashTime">--:--</strong>
                <span id="dashDate"></span>
            </div>
        </div>

        <div class="dash-actions">
            <a href="addnews.php" class="dash-action primary"><i class="fas fa-plus"></i> Add News</a>
            <a href="addann.php" class="dash-action"><i class="fas fa-bullhorn"></i> New Announcement</a>
            <a href="addfaculty.php" class="dash-action"><i class="fas fa-user-plus"></i> Add Faculty</a>
            <a href="addrecruitment.php?page=faculty" class="dash-action"><i class="fas fa-briefcase"></i> Recruitment Entry</a>
            <a href="../index.php" target="_blank" rel="noopener" class="dash-action"><i class="fas fa-arrow-up-right-from-square"></i> View Website</a>
        </div>

    </section>


    <!-- =====================================================
         KPI TILES
    ====================================================== -->

    <section class="dash-section">

        <div class="kpi-grid">

            <a href="faculty.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $facultyCount ?>"><?= $facultyCount ?></div>
                <div class="kpi-label">Active Faculty</div>
                <div class="kpi-sub" title="<?= $e(implode(' · ', $facultySub)) ?>"><?= $e(implode(' · ', $facultySub) ?: '—') ?></div>
            </a>

            <a href="ms.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-user-graduate"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $studentTotal ?>"><?= $studentTotal ?></div>
                <div class="kpi-label">Students</div>
                <div class="kpi-sub"><?= $msCount ?> M.S · <?= $phdCount ?> Ph.D · <?= $mtechCount ?> M.Tech</div>
            </a>

            <a href="staff.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-user-tie"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $staffCount ?>"><?= $staffCount ?></div>
                <div class="kpi-label">Staff</div>
                <div class="kpi-sub">Administrative &amp; technical</div>
            </a>

            <a href="clusters.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-layer-group"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $clusterCount ?>"><?= $clusterCount ?></div>
                <div class="kpi-label">Research Clusters</div>
                <div class="kpi-sub"><?= $researchCount ?> featured research items</div>
            </a>

            <a href="news.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-newspaper"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $newsCount + $announcementCount ?>"><?= $newsCount + $announcementCount ?></div>
                <div class="kpi-label">News &amp; Notices</div>
                <div class="kpi-sub"><?= $newsCount ?> news · <?= $announcementCount ?> announcements</div>
            </a>

            <a href="faculty_recruitment.php" class="kpi reveal">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-briefcase"></i></span>
                    <i class="fas fa-arrow-right kpi-go"></i>
                </div>
                <div class="kpi-value" data-count="<?= $recruitmentCount ?>"><?= $recruitmentCount ?></div>
                <div class="kpi-label">Recruitment Rows</div>
                <div class="kpi-sub">Live on Careers pages</div>
            </a>

        </div>

    </section>


    <!-- =====================================================
         CHARTS
    ====================================================== -->

    <section class="dash-section">

        <div class="dash-grid-2">

            <!-- Batch intake (2 series) -->
            <div class="card" id="cardBatch">
                <div class="card-head">
                    <div>
                        <h4>Intake by batch</h4>
                        <p>M.S. and Ph.D. students per admission batch</p>
                    </div>
                    <button type="button" class="view-toggle" data-target="cardBatch">Table</button>
                </div>

                <div class="chart-legend">
                    <span><i style="background:var(--series-1)"></i> M.S.</span>
                    <span><i style="background:var(--series-2)"></i> Ph.D.</span>
                </div>

                <div class="chart-box"><canvas id="chartBatch" aria-label="Intake by batch chart" role="img"></canvas></div>

                <table class="chart-table">
                    <thead><tr><th>Batch</th><th class="num">M.S.</th><th class="num">Ph.D.</th></tr></thead>
                    <tbody>
                        <?php foreach ($batchChart['labels'] as $i => $b): ?>
                            <tr><td><?= $e($b) ?></td><td class="num"><?= $batchChart['ms'][$i] ?></td><td class="num"><?= $batchChart['phd'][$i] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Student mix (1 series) -->
            <div class="card" id="cardMix">
                <div class="card-head">
                    <div>
                        <h4>Student mix</h4>
                        <p><?= $studentTotal ?> students across programmes</p>
                    </div>
                    <button type="button" class="view-toggle" data-target="cardMix">Table</button>
                </div>

                <div class="chart-box"><canvas id="chartMix" aria-label="Student mix chart" role="img"></canvas></div>

                <table class="chart-table">
                    <thead><tr><th>Programme</th><th class="num">Students</th></tr></thead>
                    <tbody>
                        <?php foreach ($mixChart['labels'] as $i => $l): ?>
                            <tr><td><?= $e($l) ?></td><td class="num"><?= $mixChart['values'][$i] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </section>


    <!-- =====================================================
         PROGRAMMES + STATUS + DATES
    ====================================================== -->

    <section class="dash-section">

        <div class="dash-grid-3">

            <!-- M.Tech programmes -->
            <div class="card" id="cardProg">
                <div class="card-head">
                    <div>
                        <h4>M.Tech students by programme</h4>
                        <p><?= $mtechCount ?> students</p>
                    </div>
                    <button type="button" class="view-toggle" data-target="cardProg">Table</button>
                </div>

                <div class="chart-box" style="height:220px;"><canvas id="chartProg" aria-label="M.Tech programmes chart" role="img"></canvas></div>

                <table class="chart-table">
                    <thead><tr><th>Programme</th><th class="num">Students</th></tr></thead>
                    <tbody>
                        <?php foreach ($programmeChart['full'] as $i => $p): ?>
                            <tr><td><?= $e($p) ?></td><td class="num"><?= $programmeChart['values'][$i] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Website content status -->
            <div class="card">
                <div class="card-head">
                    <div>
                        <h4>Website content status</h4>
                        <p>Key sections that need content</p>
                    </div>
                </div>

                <div class="health-ring">
                    <div class="ring" style="--p: <?= $healthPct ?>;"><?= $healthPct ?>%</div>
                    <p><strong><?= $healthOk ?> of <?= count($health) ?></strong> sections have content.<br>
                        <?= $healthOk === count($health) ? 'Everything is in place.' : 'Fill the highlighted sections.' ?></p>
                </div>

                <ul class="health-list">
                    <?php foreach ($health as $item): ?>
                        <li>
                            <a href="<?= $e($item['link']) ?>">
                                <?= $e($item['label']) ?>
                                <?php if ($item['ok']): ?>
                                    <span class="pill ok"><i class="fas fa-check"></i> Ready</span>
                                <?php else: ?>
                                    <span class="pill warn"><i class="fas fa-triangle-exclamation"></i> Empty</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Important dates -->
            <div class="card">
                <div class="card-head">
                    <div>
                        <h4>Important dates</h4>
                        <p>Shown on the Admissions page</p>
                    </div>
                    <a href="important_dates.php" class="card-link">Manage</a>
                </div>

                <?php if (!empty($dates)): ?>
                    <ul class="feed">
                        <?php foreach ($dates as $d): ?>
                            <li>
                                <span class="feed-dot sun"><i class="fas fa-calendar-day"></i></span>
                                <div class="feed-body">
                                    <strong><?= $e($d['description'] !== '' ? $d['description'] : $d['schedule_name']) ?></strong>
                                    <small><?= $e($d['date_schedule']) ?> · <?= $e($d['schedule_name']) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="feed-empty">No dates added yet.</div>
                <?php endif; ?>
            </div>

        </div>

    </section>


    <!-- =====================================================
         RECENT CONTENT
    ====================================================== -->

    <section class="dash-section">

        <div class="dash-grid-2" style="grid-template-columns: 1fr 1fr;">

            <div class="card">
                <div class="card-head">
                    <div>
                        <h4>Latest news &amp; events</h4>
                        <p>Most recently added</p>
                    </div>
                    <a href="news.php" class="card-link">View all</a>
                </div>

                <?php if (!empty($recentNews)): ?>
                    <ul class="feed">
                        <?php foreach ($recentNews as $n): ?>
                            <li>
                                <span class="feed-dot"><i class="fas fa-newspaper"></i></span>
                                <div class="feed-body">
                                    <strong><?= $e($n['title']) ?></strong>
                                    <small>
                                        Added <?= $e($fmtDate($n['created_at'])) ?>
                                        <?php if ((string)($n['status'] ?? '1') === '0'): ?> · <em>Hidden</em><?php endif; ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="feed-empty">No news yet. <a href="addnews.php">Add the first one</a>.</div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-head">
                    <div>
                        <h4>Announcements</h4>
                        <p>Most recently added</p>
                    </div>
                    <a href="announcements.php" class="card-link">View all</a>
                </div>

                <?php if (!empty($recentAnn)): ?>
                    <ul class="feed">
                        <?php foreach ($recentAnn as $a): ?>
                            <li>
                                <span class="feed-dot lime"><i class="fas fa-bullhorn"></i></span>
                                <div class="feed-body">
                                    <strong><?= $e($a['title']) ?></strong>
                                    <small>
                                        <?= $e($a['date_text'] !== '' ? $a['date_text'] : $fmtDate($a['created_at'])) ?>
                                        <?php if ((string)($a['status'] ?? '1') === '0'): ?> · <em>Hidden</em><?php endif; ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="feed-empty">No announcements yet. <a href="addann.php">Add one</a>.</div>
                <?php endif; ?>
            </div>

        </div>

    </section>


    <!-- =====================================================
         SHORTCUTS
    ====================================================== -->

    <section class="dash-section">

        <div class="dash-head">
            <div>
                <h3>Manage the website</h3>
                <p>Jump straight to any section.</p>
            </div>
        </div>

        <div class="shortcut-grid">
            <a href="hero.php" class="shortcut"><i class="fas fa-image"></i><strong>Hero Section</strong><small>Homepage banner</small></a>
            <a href="director-message.php" class="shortcut"><i class="fas fa-user-pen"></i><strong>Director's Message</strong><small>Homepage message</small></a>
            <a href="head-message.php" class="shortcut"><i class="fas fa-comment-dots"></i><strong>Head's Message</strong><small>Homepage message</small></a>
            <a href="counter.php" class="shortcut"><i class="fas fa-chart-simple"></i><strong>Key Statistics</strong><small>Homepage counters</small></a>
            <a href="research.php" class="shortcut"><i class="fas fa-flask"></i><strong>Featured Research</strong><small>Homepage research</small></a>
            <a href="committee.php" class="shortcut"><i class="fas fa-sitemap"></i><strong>Organization</strong><small>Committee, SCC, guidelines</small></a>
            <a href="important_dates.php" class="shortcut"><i class="fas fa-calendar-days"></i><strong>Admissions</strong><small>Dates &amp; how to apply</small></a>
            <a href="phd.php" class="shortcut"><i class="fas fa-graduation-cap"></i><strong>Ph.D. Scholars</strong><small><?= $phdCount ?> records</small></a>
            <a href="mtech.php" class="shortcut"><i class="fas fa-book-open-reader"></i><strong>M.Tech Students</strong><small><?= $mtechCount ?> records</small></a>
            <a href="faculty_recruitment.php" class="shortcut"><i class="fas fa-briefcase"></i><strong>Careers</strong><small>Faculty Recruitment</small></a>
            <a href="contact.php" class="shortcut"><i class="fas fa-location-dot"></i><strong>Contact</strong><small>Address &amp; map</small></a>
            <a href="users.php" class="shortcut"><i class="fas fa-users-gear"></i><strong>Users &amp; Logins</strong><small><?= $userCount ?> accounts</small></a>
        </div>

    </section>


</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function () {

    /* ---------- Greeting + live clock (viewer's local time) ---------- */
    function tick() {
        var now = new Date();
        var h = now.getHours();
        var greet = h < 12 ? 'Good morning' : (h < 17 ? 'Good afternoon' : 'Good evening');
        document.getElementById('dashGreeting').textContent = greet;
        document.getElementById('dashTime').textContent =
            now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        document.getElementById('dashDate').textContent =
            now.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    tick();
    setInterval(tick, 30000);

    /* ---------- Count-up on KPI numbers ---------- */
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce) {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseInt(el.dataset.count, 10) || 0;
            if (target === 0) return;
            var start = null;
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / 900, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            }
            el.textContent = '0';
            requestAnimationFrame(step);
        });
    }

    /* ---------- Chart / table toggle ---------- */
    document.querySelectorAll('.view-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = document.getElementById(btn.dataset.target);
            var on = card.classList.toggle('show-table');
            btn.textContent = on ? 'Chart' : 'Table';
        });
    });

    if (typeof Chart === 'undefined') {
        /* CDN blocked: fall back to the tables */
        document.querySelectorAll('.view-toggle').forEach(function (btn) {
            document.getElementById(btn.dataset.target).classList.add('show-table');
            btn.style.display = 'none';
        });
        return;
    }

    /* ---------- Shared chart styling ---------- */
    var css    = getComputedStyle(document.querySelector('.dash'));
    var S1     = css.getPropertyValue('--series-1').trim();
    var S2     = css.getPropertyValue('--series-2').trim();
    var INK2   = css.getPropertyValue('--ink-2').trim();
    var GRID   = '#eef2f6';

    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = INK2;

    var tooltip = {
        backgroundColor: '#0f172a',
        padding: 10,
        cornerRadius: 8,
        titleFont: { weight: '600' },
        displayColors: true,
        boxWidth: 8,
        boxHeight: 8,
        boxPadding: 4
    };

    var bar = {
        borderRadius: 4,
        borderSkipped: 'start',   /* rounded data-end, square at baseline */
        borderColor: '#ffffff',
        borderWidth: { top: 0, right: 0, bottom: 0, left: 0 },
        maxBarThickness: 28
    };

    /* Intake by batch — grouped bars, 2 series, legend rendered in HTML */
    var batch = <?= json_encode($batchChart, $jsonFlags) ?>;
    new Chart(document.getElementById('chartBatch'), {
        type: 'bar',
        data: {
            labels: batch.labels,
            datasets: [
                Object.assign({ label: 'M.S.',  data: batch.ms,  backgroundColor: S1 }, bar),
                Object.assign({ label: 'Ph.D.', data: batch.phd, backgroundColor: S2 }, bar)
            ]
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            datasets: { bar: { categoryPercentage: 0.6, barPercentage: 0.85 } },
            plugins: { legend: { display: false }, tooltip: tooltip },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: GRID }, border: { display: false } }
            }
        }
    });

    /* Student mix — single series horizontal bars */
    var mix = <?= json_encode($mixChart, $jsonFlags) ?>;
    new Chart(document.getElementById('chartMix'), {
        type: 'bar',
        data: {
            labels: mix.labels,
            datasets: [Object.assign({ label: 'Students', data: mix.values, backgroundColor: S1 }, bar)]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: Object.assign({}, tooltip, { displayColors: false }) },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: GRID }, border: { display: false } },
                y: { grid: { display: false }, border: { display: false } }
            }
        }
    });

    /* M.Tech programmes — single series horizontal bars, full name in tooltip */
    var prog = <?= json_encode($programmeChart, $jsonFlags) ?>;
    new Chart(document.getElementById('chartProg'), {
        type: 'bar',
        data: {
            labels: prog.labels,
            datasets: [Object.assign({ label: 'Students', data: prog.values, backgroundColor: S1 }, bar)]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: Object.assign({}, tooltip, {
                    displayColors: false,
                    callbacks: { title: function (items) { return prog.full[items[0].dataIndex]; } }
                })
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: GRID }, border: { display: false } },
                y: { grid: { display: false }, border: { display: false } }
            }
        }
    });

})();
</script>


<?php

require_once __DIR__ . '/footer.php';

?>
