<?php
include 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = reconstructChunks('title');
    $author  = reconstructChunks('author');
    $journal = reconstructChunks('journal');
    $volume  = reconstructChunks('volume');
    $page    = reconstructChunks('page');
    $doi     = reconstructChunks('doi');
    $doilink = reconstructChunks('doilink');
    $year    = reconstructChunks('year');

    if (empty($title)) {
        $error = 'Please enter the journal article title.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO journalpub (id, faculty_id, type, title, author, journal, volume, page, doi, doilink, year)
                VALUES (:id, :faculty_id, 'journal', :title, :author, :journal, :volume, :page, :doi, :doilink, :year)
            ");
            $stmt->execute([
                ':id'         => $faculty_id,
                ':faculty_id' => $faculty_id,
                ':title'      => $title,
                ':author'     => $author,
                ':journal'    => $journal,
                ':volume'     => $volume,
                ':page'       => $page,
                ':doi'        => $doi,
                ':doilink'    => $doilink,
                ':year'       => $year
            ]);

            if (!headers_sent()) {
                header("Location: journals.php?success=added");
                exit;
            } else {
                echo "<script>window.location.href = 'journals.php?success=added';</script>";
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
        <h1>Add Journal Publication</h1>
        <p>Add a new peer-reviewed journal paper to your inner profile.</p>
    </div>
    <div>
        <a href="journals.php" class="btn btn-secondary" style="background:#f1f5f9; color:#334155; padding:9px 16px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px; border:1px solid #cbd5e1;">
            <i class="fa-solid fa-arrow-left"></i> Back to Journal Publications
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background: #fce8e6; color: #c5221f; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="section-box" style="max-width: 900px;">
    <h3><i class="fa-solid fa-plus-circle"></i> Journal Article Information</h3>

    <form method="POST" action="" id="pubForm" onsubmit="return preparePubSubmit()">
        <!-- TITLE (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="title" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Article Title *</label>
            <textarea
                id="title"
                class="form-control"
                rows="3"
                placeholder="Enter complete journal article title"
                oninput="syncField('title')"
                required
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            ></textarea>
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
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="author_chunk_count" name="author_chunk_count" value="0">
            <div id="author_chunks_container"></div>
        </div>

        <!-- JOURNAL NAME (chunked Base64) -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="journal" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Journal Name</label>
            <input
                type="text"
                id="journal"
                class="form-control"
                placeholder="e.g. Microgravity Science and Technology / ACS Applied Materials"
                oninput="syncField('journal')"
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"
            >
            <input type="hidden" id="journal_chunk_count" name="journal_chunk_count" value="0">
            <div id="journal_chunks_container"></div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom: 20px;">
            <!-- VOLUME -->
            <div class="form-group">
                <label for="volume" style="display:block; font-weight:600; margin-bottom:6px; font-size:14px;">Volume / Issue</label>
                <input
                    type="text"
                    id="volume"
                    class="form-control"
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
                    placeholder="e.g. 2023 or September 2023"
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
                <i class="fa-solid fa-check"></i> Save Journal Article
            </button>
            <a href="journals.php" style="padding:11px 20px; color:#64748b; text-decoration:none;">Cancel</a>
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
    ['title', 'author', 'journal', 'volume', 'page', 'doi', 'doilink', 'year'].forEach(f => syncField(f));
    return true;
}
</script>

<?php include 'footer.php'; ?>
