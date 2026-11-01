<?php
/*
|--------------------------------------------------------------------------
| Shared list body for Join Us admin menus
|--------------------------------------------------------------------------
| Included by faculty_recruitment.php ($listPage = 'faculty')
|         and careers.php             ($listPage = 'careers')
|
| Shown grouped the same way as the website:  Title -> Table Heading -> Rows
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/recruitment_helpers.php';

if (!isset($listPage) || !isset(RECRUITMENT_PAGES[$listPage])) {
    header('Location: faculty_recruitment.php');
    exit;
}

$pageLabel = RECRUITMENT_PAGES[$listPage];
$addUrl    = 'addrecruitment.php?page=' . urlencode($listPage);


/*
|--------------------------------------------------------------------------
| FETCH ENTRIES (this page only) and GROUP: title -> heading -> rows
|--------------------------------------------------------------------------
*/

$groups     = [];
$totalCount = 0;

try {

    $stmt = $pdo->prepare("
        SELECT id, title, title_order, heading, heading_order, label, row_type, content, link_url, link_text, display_order, status
        FROM recruitment
        WHERE page = ?
        ORDER BY title_order ASC, title ASC, heading_order ASC, heading ASC, display_order ASC, id ASC
    ");
    $stmt->execute([$listPage]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $t  = $row['title'] !== '' ? $row['title'] : '(No title)';
        $hd = $row['heading'];

        if (!isset($groups[$t])) {
            $groups[$t] = ['first_id' => (int)$row['id'], 'count' => 0, 'headings' => []];
        }
        if (!isset($groups[$t]['headings'][$hd])) {
            $groups[$t]['headings'][$hd] = ['first_id' => (int)$row['id'], 'rows' => []];
        }

        $groups[$t]['headings'][$hd]['rows'][] = $row;
        $groups[$t]['count']++;
        $totalCount++;
    }

} catch (PDOException $e) {
    error_log('Recruitment list error: ' . $e->getMessage());
}

$h = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>

<style>
    /* ---------- Title block ---------- */
    .rec-title-block {
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 22px;
        background: #fff;
    }

    .rec-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #1F3C44;
        color: #fff;
        padding: 14px 18px;
    }

    .rec-title-bar h4 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #fff;
    }

    .rec-title-meta {
        display: flex;
        gap: 8px;
        font-size: 12px;
    }

    .rec-title-meta span {
        background: rgba(255, 255, 255, .14);
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* ---------- Table ---------- */
    .rec-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .rec-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .rec-table col.c-label   { width: 18%; }
    .rec-table col.c-type    { width: 95px; }
    .rec-table col.c-value   { width: auto; }
    .rec-table col.c-status  { width: 95px; }
    .rec-table col.c-actions { width: 170px; }

    .rec-table thead th {
        background: #F5F7FA;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: left;
        padding: 10px 16px;
        border-bottom: 1px solid #E5E7EB;
        white-space: nowrap;
    }

    .rec-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #EEF1F4;
        vertical-align: middle;
        color: #374151;
        font-size: 14px;
        line-height: 1.45;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }

    .rec-table tr.rec-row:hover td {
        background: #F8FAFC;
    }

    /* ---------- Heading row ---------- */
    .rec-heading-row td {
        background: #EEF4F9;
        color: #184C74;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: .03em;
        padding: 9px 16px;
        border-bottom: 1px solid #DCE6EF;
    }

    .rec-heading-row .rec-heading-order {
        font-weight: 500;
        color: #64748B;
        margin-left: 8px;
        letter-spacing: 0;
    }

    /* ---------- Cells ---------- */
    .rec-label {
        font-weight: 600;
        color: #1F2937;
    }

    .rec-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .rec-badge-active { background: #DCFCE7; color: #166534; }
    .rec-badge-off    { background: #F3F4F6; color: #6B7280; }
    .rec-badge-text   { background: #E0ECF7; color: #184C74; }
    .rec-badge-button { background: #ECFCCB; color: #3F6212; }

    .rec-content {
        white-space: pre-line;
    }

    .rec-btn-preview {
        display: inline-block;
        background: #A6E22E;
        color: #000;
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 3px;
        margin-bottom: 4px;
    }

    .rec-url {
        display: block;
        font-size: 12px;
        color: #64748B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-decoration: none;
    }

    .rec-url:hover {
        color: #184C74;
        text-decoration: underline;
    }

    .rec-order {
        color: #64748B;
        font-variant-numeric: tabular-nums;
    }

    .rec-table .table-actions {
        justify-content: flex-start;
    }

    /* ---------- Drag & drop ---------- */
    .drag-handle {
        cursor: grab;
        color: #94A3B8;
        padding: 2px 6px;
        margin-right: 6px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .drag-handle:hover {
        color: #184C74;
        background: rgba(24, 76, 116, .08);
    }

    .rec-title-bar .drag-handle {
        color: rgba(255, 255, 255, .7);
    }

    .rec-title-bar .drag-handle:hover {
        color: #fff;
        background: rgba(255, 255, 255, .15);
    }

    .rec-title-left {
        display: flex;
        align-items: center;
    }

    .sortable-ghost {
        opacity: .35;
    }

    .sortable-chosen td,
    .sortable-chosen .rec-title-bar {
        background: #FFF8D6 !important;
    }

    .rec-sort-disabled .drag-handle {
        visibility: hidden;
    }

    .rec-dnd-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748B;
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 8px;
        padding: 9px 14px;
        margin-top: 16px;
    }

    .rec-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9999;
        padding: 11px 18px;
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
        opacity: 0;
        transform: translateY(10px);
        transition: all .25s ease;
        pointer-events: none;
    }

    .rec-toast.show { opacity: 1; transform: translateY(0); }
    .rec-toast.ok   { background: #166534; }
    .rec-toast.err  { background: #B91C1C; }

    /* ---------- Rename ---------- */
    .rec-rename-btn {
        border: 1px solid #C7D7E6;
        background: #fff;
        color: #184C74;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0;
        padding: 3px 9px;
        border-radius: 5px;
        margin-left: 10px;
        cursor: pointer;
        vertical-align: middle;
    }

    .rec-rename-btn:hover {
        background: #184C74;
        border-color: #184C74;
        color: #fff;
    }

    .rec-rename-light {
        background: transparent;
        border-color: rgba(255, 255, 255, .45);
        color: #fff;
    }

    .rec-rename-light:hover {
        background: #fff;
        border-color: #fff;
        color: #1F3C44;
    }

    .rename-modal-icon {
        background: #E0ECF7 !important;
        color: #184C74 !important;
    }

    .rename-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        margin: 6px 0 4px;
        box-sizing: border-box;
    }

    .rename-input:focus {
        outline: none;
        border-color: #184C74;
        box-shadow: 0 0 0 3px rgba(24, 76, 116, .12);
    }

    .rename-help {
        font-size: 12px;
        color: #6B7280;
        text-align: left;
        margin: 0 0 4px;
    }

    .rec-empty-search {
        display: none;
        text-align: center;
        padding: 30px;
        color: #6B7280;
    }
</style>


<div class="dashboard-content">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2><?= $h($pageLabel) ?></h2>
            <p>Manage the titles, table headings and rows shown on the <?= $h($pageLabel) ?> page.</p>
        </div>

        <div>
            <a href="<?= $h($addUrl) ?>" class="btn-add">+ Add Entry</a>
        </div>
    </div>


    <?php if ($listPage === 'careers'): ?>
        <div class="alert" style="background:#FFF8D6; border:1px solid #F4E796; color:#713F12;">
            <i class="fas fa-person-digging"></i>
            The website Careers page currently shows <strong>"Page Under Construction"</strong>.
            Entries added here will not be visible until it is switched on in <code>careers.php</code>
            (<code>$recruitmentUnderConstruction = false</code>).
        </div>
    <?php endif; ?>


    <!-- SUCCESS MESSAGE -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'added'): ?>
            <div class="alert alert-success">Entry added successfully.</div>
        <?php elseif ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success">Entry updated successfully.</div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">Entry deleted successfully.</div>
        <?php elseif ($_GET['success'] === 'delete_error'): ?>
            <div class="alert alert-danger">Unable to delete the entry. Please try again.</div>
        <?php elseif ($_GET['success'] === 'renamed'): ?>
            <div class="alert alert-success">Renamed successfully. All rows under it were updated.</div>
        <?php elseif ($_GET['success'] === 'rename_exists'): ?>
            <div class="alert alert-danger">Not renamed: another one with that name already exists here.</div>
        <?php elseif ($_GET['success'] === 'rename_invalid'): ?>
            <div class="alert alert-danger">Not renamed: the name must be 1–255 characters.</div>
        <?php elseif ($_GET['success'] === 'rename_error'): ?>
            <div class="alert alert-danger">Unable to rename. Please try again.</div>
        <?php endif; ?>
    <?php endif; ?>


    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3><?= $h($pageLabel) ?> Entries</h3>
                <p>Shown in the same order as the website: Title → Table Heading → Row.</p>
            </div>
        </div>


        <?php if (!empty($groups)): ?>

            <!-- SEARCH -->
            <div class="table-toolbar">
                <div class="table-search">
                    <input type="text" id="recSearch" class="table-search-input" placeholder="Search title, heading, label, text..." autocomplete="off">
                    <span class="table-search-icon">&#128269;</span>
                </div>
                <div id="recSearchCount" class="search-result-count">
                    <?= $totalCount ?> <?= $totalCount === 1 ? 'entry' : 'entries' ?>
                </div>
            </div>


            <div class="rec-dnd-hint">
                <i class="fas fa-arrows-up-down"></i>
                Drag the <i class="fas fa-grip-vertical"></i> handle to re-order titles, table headings (inside a title) and rows (inside a heading). The order is saved automatically.
            </div>

            <div id="recTitles">
            <?php foreach ($groups as $title => $group): ?>

                <div class="rec-title-block" data-id="<?= (int)$group['first_id'] ?>" data-title="<?= $h(mb_strtolower($title)) ?>">

                    <div class="rec-title-bar">
                        <div class="rec-title-left">
                            <span class="drag-handle rec-title-handle" title="Drag to re-order titles"><i class="fas fa-grip-vertical"></i></span>
                            <h4><?= $h($title) ?></h4>
                            <button type="button" class="rec-rename-btn rec-rename-light" title="Rename title"
                                onclick="openRenameModal('title', <?= (int)$group['first_id'] ?>, <?= $h(json_encode($title, $jsonFlags)) ?>)">
                                <i class="fas fa-pen"></i> Rename
                            </button>
                        </div>
                        <div class="rec-title-meta">
                            <span><?= (int)$group['count'] ?> <?= $group['count'] === 1 ? 'row' : 'rows' ?></span>
                        </div>
                    </div>

                    <div class="rec-table-wrap">
                        <table class="rec-table">
                            <colgroup>
                                <col class="c-label">
                                <col class="c-type">
                                <col class="c-value">
                                <col class="c-status">
                                <col class="c-actions">
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Type</th>
                                    <th>Text / Button</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <?php foreach ($group['headings'] as $heading => $hg): ?>
                                <tbody class="rec-heading-group" data-id="<?= (int)$hg['first_id'] ?>" data-heading="<?= $h(mb_strtolower($heading)) ?>">

                                    <tr class="rec-heading-row">
                                        <td colspan="5">
                                            <span class="drag-handle rec-heading-handle" title="Drag to re-order headings"><i class="fas fa-grip-vertical"></i></span>
                                            <?= $h($heading) ?>
                                            <button type="button" class="rec-rename-btn" title="Rename heading"
                                                onclick="openRenameModal('heading', <?= (int)$hg['first_id'] ?>, <?= $h(json_encode($heading, $jsonFlags)) ?>)">
                                                <i class="fas fa-pen"></i> Rename
                                            </button>
                                            <span class="rec-heading-order">· <?= count($hg['rows']) ?> <?= count($hg['rows']) === 1 ? 'row' : 'rows' ?></span>
                                        </td>
                                    </tr>

                                    <?php foreach ($hg['rows'] as $row): ?>
                                        <?php $isButton = $row['row_type'] === 'button'; ?>
                                        <tr class="rec-row" data-id="<?= (int)$row['id'] ?>">

                                            <td class="rec-label">
                                                <span class="drag-handle rec-row-handle" title="Drag to re-order rows"><i class="fas fa-grip-vertical"></i></span><?= $h($row['label']) ?>
                                            </td>

                                            <td>
                                                <?php if ($isButton): ?>
                                                    <span class="rec-badge rec-badge-button">Button</span>
                                                <?php else: ?>
                                                    <span class="rec-badge rec-badge-text">Text</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ($isButton): ?>
                                                    <span class="rec-btn-preview"><?= $h($row['link_text'] !== '' ? $row['link_text'] : 'Click Here') ?></span>
                                                    <a class="rec-url" href="<?= $h($row['link_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= $h($row['link_url']) ?>">
                                                        <?= $h($row['link_url']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <div class="rec-content"><?= $h($row['content']) ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ((int)$row['status'] === 1): ?>
                                                    <span class="rec-badge rec-badge-active">Active</span>
                                                <?php else: ?>
                                                    <span class="rec-badge rec-badge-off">Inactive</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="table-actions">
                                                    <a href="editrecruitment.php?id=<?= (int)$row['id'] ?>" class="btn-edit">Edit</a>
                                                    <button
                                                        type="button"
                                                        class="btn-delete"
                                                        onclick="openDeleteModal(<?= (int)$row['id'] ?>, <?= $h(json_encode($heading . ' – ' . $row['label'], $jsonFlags)) ?>)"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                            <?php endforeach; ?>

                        </table>
                    </div>

                </div>

            <?php endforeach; ?>
            </div>

            <div id="recNoResults" class="rec-empty-search">
                No entries found.
            </div>

        <?php else: ?>

            <div class="table-empty">
                <p>No entries found.</p>
                <a href="<?= $h($addUrl) ?>" class="btn-add">+ Add Entry</a>
            </div>

        <?php endif; ?>

    </div>

</div>


<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon">!</div>

        <h3>Delete Entry?</h3>

        <p>Are you sure you want to delete <strong id="deleteEntryName"></strong>?</p>

        <p class="delete-warning">This action cannot be undone.</p>

        <form method="POST" action="deleterecruitment.php">
            <input type="hidden" name="id" id="deleteEntryId" value="">

            <div class="delete-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>


<!-- RENAME MODAL -->
<div id="renameModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon rename-modal-icon"><i class="fas fa-pen"></i></div>

        <h3 id="renameModalTitle">Rename</h3>

        <form method="POST" action="renamerecruitment.php" onsubmit="return prepareRename()">
            <input type="hidden" name="type" id="renameType" value="">
            <input type="hidden" name="id" id="renameId" value="">

            <input type="text" id="new_name" class="rename-input" maxlength="255" required autocomplete="off">
            <input type="hidden" id="new_name_chunk_count" name="new_name_chunk_count" value="0">
            <div id="new_name_chunks_container"></div>

            <p class="rename-help" id="renameHelp"></p>

            <div class="delete-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeRenameModal()">Cancel</button>
                <button type="submit" class="btn-confirm-delete" style="background:#184C74;">Save</button>
            </div>
        </form>
    </div>
</div>


<div id="recToast" class="rec-toast"></div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>

const REC_PAGE = <?= json_encode($listPage) ?>;

function openDeleteModal(id, name) {
    document.getElementById('deleteEntryId').value = id;
    document.getElementById('deleteEntryName').textContent = name;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}


/* RENAME (title / heading) — new name is sent as chunked Base64 */

function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function syncChunked(fieldId) {
    const visible    = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container  = document.getElementById(fieldId + '_chunks_container');
    if (!visible || !countInput || !container) return;

    const base64 = b64EncodeUtf8(visible.value);
    const chunks = [];
    for (let i = 0; i < base64.length; i += 200) chunks.push(base64.substring(i, i + 200));

    countInput.value = chunks.length;
    container.innerHTML = '';
    chunks.forEach(function (chunk, i) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = fieldId + '_chunk_' + i;
        inp.value = chunk;
        container.appendChild(inp);
    });
}

function openRenameModal(type, id, current) {
    document.getElementById('renameType').value = type;
    document.getElementById('renameId').value   = id;
    document.getElementById('renameModalTitle').textContent = type === 'title' ? 'Rename Title' : 'Rename Table Heading';
    document.getElementById('renameHelp').textContent = type === 'title'
        ? 'All rows under this title are updated. Its position on the page stays the same.'
        : 'All rows under this heading are updated. Its position stays the same.';

    const input = document.getElementById('new_name');
    input.value = current;
    document.getElementById('renameModal').classList.add('show');
    setTimeout(function () { input.focus(); input.select(); }, 50);
}

function closeRenameModal() {
    document.getElementById('renameModal').classList.remove('show');
}

function prepareRename() {
    const input = document.getElementById('new_name');
    input.value = input.value.trim();
    if (input.value === '') return false;
    syncChunked('new_name');
    return true;
}

document.getElementById('renameModal').addEventListener('click', function (event) {
    if (event.target === this) closeRenameModal();
});

document.getElementById('deleteModal').addEventListener('click', function (event) {
    if (event.target === this) closeDeleteModal();
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') { closeDeleteModal(); closeRenameModal(); }
});


/*
| SEARCH — matches the row text, its heading or its title.
| Empty headings and titles are hidden while searching.
*/
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('recSearch');
    const searchCount = document.getElementById('recSearchCount');
    const noResults   = document.getElementById('recNoResults');
    const blocks      = Array.from(document.querySelectorAll('.rec-title-block'));

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        let total = 0;

        blocks.forEach(function (block) {
            const titleHit = term !== '' && block.dataset.title.includes(term);
            let blockVisible = 0;

            block.querySelectorAll('.rec-heading-group').forEach(function (group) {
                const headingHit = titleHit || (term !== '' && group.dataset.heading.includes(term));
                let groupVisible = 0;

                group.querySelectorAll('.rec-row').forEach(function (row) {
                    const show = term === '' || headingHit || row.textContent.toLowerCase().includes(term);
                    row.style.display = show ? '' : 'none';
                    if (show) groupVisible++;
                });

                group.style.display = groupVisible > 0 ? '' : 'none';
                blockVisible += groupVisible;
            });

            block.style.display = blockVisible > 0 ? '' : 'none';
            total += blockVisible;
        });

        searchCount.textContent = total + (total === 1 ? ' entry' : ' entries');
        noResults.style.display = total === 0 ? 'block' : 'none';

        /* No drag & drop while the list is filtered */
        setSortingEnabled(term === '');
    });
});

/*
| DRAG & DROP RE-ORDER
| Titles   : drag the title bar handle
| Headings : drag the heading handle (inside the same title)
| Rows     : drag the row handle (inside the same heading)
| Only row ids (integers) are sent to reorderrecruitment.php
*/

const recSortables = [];

function showToast(message, ok) {
    const toast = document.getElementById('recToast');
    toast.textContent = message;
    toast.className = 'rec-toast show ' + (ok ? 'ok' : 'err');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
}

function saveOrder(type, ids) {
    const body = new URLSearchParams();
    body.append('page', REC_PAGE);
    body.append('type', type);
    ids.forEach(function (id) { body.append('ids[]', id); });

    fetch('reorderrecruitment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
    })
        .then(function (res) { return res.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
            if (data && data.ok) {
                showToast('Order saved', true);
            } else {
                showToast((data && data.error) ? data.error : 'Unable to save the order', false);
                setTimeout(function () { location.reload(); }, 1500);
            }
        })
        .catch(function () {
            showToast('Network error - order not saved', false);
            setTimeout(function () { location.reload(); }, 1500);
        });
}

function idsOf(container, selector) {
    return Array.from(container.querySelectorAll(':scope > ' + selector)).map(function (el) {
        return el.dataset.id;
    });
}

function setSortingEnabled(enabled) {
    recSortables.forEach(function (s) { s.option('disabled', !enabled); });
    const root = document.getElementById('recTitles');
    if (root) root.classList.toggle('rec-sort-disabled', !enabled);
}

document.addEventListener('DOMContentLoaded', function () {

    const root = document.getElementById('recTitles');
    if (!root || typeof Sortable === 'undefined') return;

    const common = {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen'
    };

    /* Titles */
    recSortables.push(Sortable.create(root, Object.assign({}, common, {
        handle: '.rec-title-handle',
        draggable: '.rec-title-block',
        onEnd: function (evt) {
            if (evt.oldIndex === evt.newIndex) return;
            saveOrder('titles', idsOf(root, '.rec-title-block'));
        }
    })));

    root.querySelectorAll('.rec-table').forEach(function (table) {

        /* Headings inside one title */
        recSortables.push(Sortable.create(table, Object.assign({}, common, {
            handle: '.rec-heading-handle',
            draggable: '.rec-heading-group',
            onEnd: function (evt) {
                if (evt.oldIndex === evt.newIndex) return;
                saveOrder('headings', idsOf(table, '.rec-heading-group'));
            }
        })));

        /* Rows inside one heading */
        table.querySelectorAll('.rec-heading-group').forEach(function (group) {
            recSortables.push(Sortable.create(group, Object.assign({}, common, {
                handle: '.rec-row-handle',
                draggable: '.rec-row',
                onEnd: function (evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    saveOrder('rows', idsOf(group, '.rec-row'));
                }
            })));
        });
    });
});

</script>

<?php include 'footer.php'; ?>
