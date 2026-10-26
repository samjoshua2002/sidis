<?php
include 'header.php';

$pub_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmtPub = $pdo->prepare("SELECT * FROM confrncepub WHERE pub_id = :pub_id AND (id = :fac_id OR faculty_id = :fac_id) LIMIT 1");
$stmtPub->execute([':pub_id' => $pub_id, ':fac_id' => $faculty_id]);
$pub = $stmtPub->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
    echo "<div class='content-header'><h1>Conference Proceeding not found</h1><p><a href='conferences.php'>&larr; Back to Conference Proceedings</a></p></div>";
    include 'footer.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = reconstructChunks('title');
    $author     = reconstructChunks('author');
    $conference = reconstructChunks('conference');
    $volume     = reconstructChunks('volume');
    $page       = reconstructChunks('page');
    $doi        = reconstructChunks('doi');
    $doilink    = reconstructChunks('doilink');
    $year       = reconstructChunks('year');

    if (empty($title)) {
        $error = 'Please enter the conference paper title.';
    } else {
        try {
            $stmtUp = $pdo->prepare("
                UPDATE confrncepub SET
                    title = :title,
                    author = :author,
                    conference = :conference,
                    volume = :volume,
                    page = :page,
                    doi = :doi,
                    doilink = :doilink,
                    year = :year
                WHERE pub_id = :pub_id AND (id = :fac_id OR faculty_id = :fac_id)
            ");
            $stmtUp->execute([
                ':title'      => $title,
                ':author'     => $author,
                ':conference' => $conference,
                ':volume'     => $volume,
                ':page'       => $page,
                ':doi'        => $doi,
                ':doilink'    => $doilink,
                ':year'       => $year,
                ':pub_id'     => $pub_id,
                ':fac_id'     => $faculty_id
            ]);

            if (!headers_sent()) {
                header("Location: conferences.php?success=updated");
                exit;
            } else {
                echo "<script>window.location.href = 'conferences.php?success=updated';</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Edit Conference Proceeding / Paper</h1>
        <p>Update conference paper details for your profile.</p>
    </div>
    <div>
        <a href="conferences.php" class="btn btn-secondary" style="background:#f1f5f9; color:#334155; padding:9px 16px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px; border:1px solid #cbd5e1;">
            <i class="fa-solid fa-arrow-left"></i> Back to Conference Proceedings
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background: #fce8e6; color: #c5221f; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="section-box" style="max-width: 900px;">
    <h3><i class="fa-solid fa-pen-to-square"></i> Edit Conference Details</h3>

    <form method="POST" action="" id="pubForm" onsubmit="return preparePubSubmit()">
        <!-- TITLE (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="title" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Paper Title *</label>
            <textarea
                id="title"
                class="form-control"
                rows="3"
                placeholder="Enter complete conference paper title"
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
                placeholder="e.g. Kedarisetty, S. & Manathara, J.G."
                oninput="syncField('author')"
                value="<?= htmlspecialchars($pub['author'] ?? '') ?>"
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="author_chunk_count" name="author_chunk_count" value="0">
            <div id="author_chunks_container"></div>
        </div>

        <!-- CONFERENCE NAME (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="conference" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Conference Name & Location</label>
            <input
                type="text"
                id="conference"
                class="form-control"
                placeholder="e.g. IEEE International Conference on Robotics and Automation (ICRA), London, UK"
                oninput="syncField('conference')"
                value="<?= htmlspecialchars($pub['conference'] ?? '') ?>"
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="conference_chunk_count" name="conference_chunk_count" value="0">
            <div id="conference_chunks_container"></div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom: 20px;">
            <!-- VOLUME -->
            <div class="form-group">
                <label for="volume" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Volume / Proceedings</label>
                <input
                    type="text"
                    id="volume"
                    class="form-control"
                    placeholder="e.g. Vol. 1"
                    oninput="syncField('volume')"
                    value="<?= htmlspecialchars($pub['volume'] ?? '') ?>"
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
                    placeholder="e.g. 88-89"
                    oninput="syncField('page')"
                    value="<?= htmlspecialchars($pub['page'] ?? '') ?>"
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
                    placeholder="e.g. 2021"
                    oninput="syncField('year')"
                    value="<?= htmlspecialchars($pub['year'] ?? '') ?>"
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
                    placeholder="e.g. 10.1109/ICRA.2021.123456"
                    oninput="syncField('doi')"
                    value="<?= htmlspecialchars($pub['doi'] ?? '') ?>"
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
                    placeholder="https://..."
                    oninput="syncField('doilink')"
                    value="<?= htmlspecialchars($pub['doilink'] ?? '') ?>"
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
                >
                <input type="hidden" id="doilink_chunk_count" name="doilink_chunk_count" value="0">
                <div id="doilink_chunks_container"></div>
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:center;">
            <button type="submit" class="btn-portal-primary" style="background:#184C74; color:#fff; padding:11px 24px; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-check"></i> Update Conference Paper
            </button>
            <a href="conferences.php" style="padding:11px 20px; color:#64748b; text-decoration:none;">Cancel</a>
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
    ['title', 'author', 'conference', 'volume', 'page', 'doi', 'doilink', 'year'].forEach(f => syncField(f));
    return true;
}
</script>

<?php include 'footer.php'; ?>
