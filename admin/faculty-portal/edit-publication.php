<?php
include 'header.php';

$type = isset($_GET['type']) ? trim($_GET['type']) : 'general';
if (!in_array($type, ['journal', 'conference', 'general'], true)) {
    $type = 'general';
}
$pub_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$tableName = 'journalpub';
$venueCol = 'journal';
if ($type === 'conference') {
    $tableName = 'confrncepub';
    $venueCol = 'conference';
} elseif ($type === 'general') {
    $tableName = 'indpublication';
    $venueCol = 'journal';
}

$stmtPub = $pdo->prepare("SELECT * FROM `{$tableName}` WHERE pub_id = :pub_id AND (id = :fac_id OR faculty_id = :fac_id) LIMIT 1");
$stmtPub->execute([':pub_id' => $pub_id, ':fac_id' => $faculty_id]);
$pub = $stmtPub->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
    echo "<div class='content-header'><h1>Publication not found</h1><p><a href='publications.php'>&larr; Back to Publications</a></p></div>";
    include 'footer.php';
    exit;
}

$typeLabels = [
    'journal'    => 'Journal Publication',
    'conference' => 'Conference Proceeding / Paper',
    'general'    => 'Publication'
];

$venueLabels = [
    'journal'    => 'Journal Name',
    'conference' => 'Conference Name & Location',
    'general'    => 'Journal / Venue / Publisher'
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = reconstructChunks('title');
    $author  = reconstructChunks('author');
    $venue   = reconstructChunks('venue');
    $volume  = reconstructChunks('volume');
    $page    = reconstructChunks('page');
    $doi     = reconstructChunks('doi');
    $doilink = reconstructChunks('doilink');
    $year    = reconstructChunks('year');

    if (empty($title)) {
        $error = 'Please enter the publication title.';
    } else {
        try {
            $stmtUp = $pdo->prepare("
                UPDATE `{$tableName}` SET
                    title = :title,
                    author = :author,
                    `{$venueCol}` = :venue,
                    volume = :volume,
                    page = :page,
                    doi = :doi,
                    doilink = :doilink,
                    year = :year
                WHERE pub_id = :pub_id AND (id = :fac_id OR faculty_id = :fac_id)
            ");
            $stmtUp->execute([
                ':title'   => $title,
                ':author'  => $author,
                ':venue'   => $venue,
                ':volume'  => $volume,
                ':page'    => $page,
                ':doi'     => $doi,
                ':doilink' => $doilink,
                ':year'    => $year,
                ':pub_id'  => $pub_id,
                ':fac_id'  => $faculty_id
            ]);

            $targetPage = ($type === 'conference') ? 'conferences.php' : (($type === 'general') ? 'other-publications.php' : 'journals.php');
            if (!headers_sent()) {
                header("Location: {$targetPage}?success=updated");
                exit;
            } else {
                echo "<script>window.location.href = " . json_encode("{$targetPage}?success=updated") . ";</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$backUrl = ($type === 'conference') ? 'conferences.php' : (($type === 'general') ? 'other-publications.php' : 'journals.php');
$backLabel = ($type === 'conference') ? 'Conference Proceedings' : (($type === 'general') ? 'Publications' : 'Journal Publications');
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Edit <?= htmlspecialchars($typeLabels[$type]) ?></h1>
        <p>Update publication details for your profile.</p>
    </div>
    <div>
        <a href="<?= $backUrl ?>" class="btn btn-secondary" style="background:#f1f5f9; color:#334155; padding:9px 16px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px; border:1px solid #cbd5e1;">
            <i class="fa-solid fa-arrow-left"></i> Back to <?= $backLabel ?>
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background: #fce8e6; color: #c5221f; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="section-box" style="max-width: 900px;">
    <h3><i class="fa-solid fa-pen-to-square"></i> Edit Publication Details</h3>

    <form method="POST" action="" id="pubForm" onsubmit="return preparePubSubmit()">
        <!-- TITLE (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="title" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Publication Title *</label>
            <textarea
                id="title"
                class="form-control"
                rows="3"
                placeholder="Enter complete publication title"
                oninput="syncField('title')"
                required
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            ><?= htmlspecialchars($pub['title'] ?? '') ?></textarea>
            <input type="hidden" id="title_chunk_count" name="title_chunk_count" value="0">
            <div id="title_chunks_container"></div>
        </div>

        <!-- AUTHORS (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="author" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Author(s)</label>
            <input
                type="text"
                id="author"
                class="form-control"
                value="<?= htmlspecialchars($pub['author'] ?? '') ?>"
                placeholder="e.g. Kedarisetty, S. & Manathara, J.G."
                oninput="syncField('author')"
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="author_chunk_count" name="author_chunk_count" value="0">
            <div id="author_chunks_container"></div>
        </div>

        <!-- VENUE (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="venue" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;"><?= htmlspecialchars($venueLabels[$type]) ?></label>
            <input
                type="text"
                id="venue"
                class="form-control"
                value="<?= htmlspecialchars($pub[$venueCol] ?? '') ?>"
                placeholder="e.g. Microgravity Science and Technology"
                oninput="syncField('venue')"
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="venue_chunk_count" name="venue_chunk_count" value="0">
            <div id="venue_chunks_container"></div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom: 20px;">
            <!-- VOLUME -->
            <div class="form-group">
                <label for="volume" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Volume / Issue</label>
                <input
                    type="text"
                    id="volume"
                    class="form-control"
                    value="<?= htmlspecialchars($pub['volume'] ?? '') ?>"
                    placeholder="e.g. Vol. 35, Issue 2"
                    oninput="syncField('volume')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="volume_chunk_count" name="volume_chunk_count" value="0">
                <div id="volume_chunks_container"></div>
            </div>

            <!-- PAGE -->
            <div class="form-group">
                <label for="page" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Page(s)</label>
                <input
                    type="text"
                    id="page"
                    class="form-control"
                    value="<?= htmlspecialchars($pub['page'] ?? '') ?>"
                    placeholder="e.g. 104-118"
                    oninput="syncField('page')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="page_chunk_count" name="page_chunk_count" value="0">
                <div id="page_chunks_container"></div>
            </div>

            <!-- YEAR -->
            <div class="form-group">
                <label for="year" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Year / Date</label>
                <input
                    type="text"
                    id="year"
                    class="form-control"
                    value="<?= htmlspecialchars($pub['year'] ?? '') ?>"
                    placeholder="e.g. September 2023 or 2023"
                    oninput="syncField('year')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="year_chunk_count" name="year_chunk_count" value="0">
                <div id="year_chunks_container"></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom: 25px;">
            <!-- DOI -->
            <div class="form-group">
                <label for="doi" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">DOI Identifier</label>
                <input
                    type="text"
                    id="doi"
                    class="form-control"
                    value="<?= htmlspecialchars($pub['doi'] ?? '') ?>"
                    placeholder="e.g. 10.1007/s12217-023-10074-9"
                    oninput="syncField('doi')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="doi_chunk_count" name="doi_chunk_count" value="0">
                <div id="doi_chunks_container"></div>
            </div>

            <!-- DOI LINK -->
            <div class="form-group">
                <label for="doilink" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Direct Link URL</label>
                <input
                    type="text"
                    id="doilink"
                    class="form-control"
                    value="<?= htmlspecialchars($pub['doilink'] ?? '') ?>"
                    placeholder="https://doi.org/10.1007/..."
                    oninput="syncField('doilink')"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="doilink_chunk_count" name="doilink_chunk_count" value="0">
                <div id="doilink_chunks_container"></div>
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:center;">
            <button type="submit" class="btn-portal-primary" style="background:#184C74; color:#fff; padding:11px 24px; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-check"></i> Update Publication
            </button>
            <a href="publications.php" style="padding:11px 20px; color:#64748b; text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<script>
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

function syncField(fieldId) {
    const elem = document.getElementById(fieldId);
    if (!elem) return;
    const val = elem.value || '';
    const encoded = b64EncodeUtf8(val);
    const chunks = splitIntoChunks(encoded, 200);

    const countElem = document.getElementById(fieldId + '_chunk_count');
    const container = document.getElementById(fieldId + '_chunks_container');

    if (countElem) countElem.value = chunks.length;
    if (container) {
        container.innerHTML = '';
        chunks.forEach((chunk, idx) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = fieldId + '_chunk_' + idx;
            input.value = chunk;
            container.appendChild(input);
        });
    }
}

function preparePubSubmit() {
    ['title', 'author', 'venue', 'volume', 'page', 'doi', 'doilink', 'year'].forEach(f => syncField(f));
    return true;
}

// Initial sync on load
document.addEventListener('DOMContentLoaded', function() {
    ['title', 'author', 'venue', 'volume', 'page', 'doi', 'doilink', 'year'].forEach(f => syncField(f));
});
</script>

<?php include 'footer.php'; ?>
