<?php
/*
|--------------------------------------------------------------------------
| Admin > Facilities > Central Facilities (list, drag & drop order, delete)
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/account_helpers.php';   // CSRF + chunked Base64 helpers

const FACILITY_IMG_DIR = __DIR__ . '/../images/facilities/';

/* Remove an image file only when no other row still uses it */
function facilityRemoveImageIfUnused(PDO $pdo, $image, $exceptId = 0)
{
    $image = basename((string)$image);
    if ($image === '') return;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE image = ? AND id <> ?");
    $stmt->execute([$image, (int)$exceptId]);
    if ((int)$stmt->fetchColumn() === 0 && is_file(FACILITY_IMG_DIR . $image)) {
        @unlink(FACILITY_IMG_DIR . $image);
    }
}


/*
|--------------------------------------------------------------------------
| AJAX: save drag & drop order (integer ids only)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    header('Content-Type: application/json; charset=utf-8');

    if (!accountCsrfValid()) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Session expired. Please reload.']);
        exit;
    }

    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids) || !$ids || count($ids) > 500) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'No items.']);
        exit;
    }
    foreach ($ids as $id) {
        if (!ctype_digit((string)$id)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid id.']);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();
        $upd = $pdo->prepare("UPDATE facilities SET display_order = ? WHERE id = ? AND category = 'central'");
        foreach (array_values($ids) as $i => $id) {
            $upd->execute([$i + 1, (int)$id]);
        }
        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Facility reorder error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Unable to save the order.']);
    }
    exit;
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = $_POST['id'] ?? '';
    if (!accountCsrfValid() || !ctype_digit((string)$id)) {
        header('Location: central_facilities.php?success=delete_error');
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT image, pdf_file FROM facilities WHERE id = ? AND category = 'central'");
        $stmt->execute([(int)$id]);
        $del = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($del) {
            $pdo->prepare("DELETE FROM facilities WHERE id = ?")->execute([(int)$id]);
            facilityRemoveImageIfUnused($pdo, $del['image'], (int)$id);

            // PDF: remove when no other row uses it
            $pdf = basename((string)$del['pdf_file']);
            if ($pdf !== '') {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM facilities WHERE pdf_file = ?");
                $chk->execute([$pdf]);
                if ((int)$chk->fetchColumn() === 0 && is_file(FACILITY_IMG_DIR . 'docs/' . $pdf)) {
                    @unlink(FACILITY_IMG_DIR . 'docs/' . $pdf);
                }
            }
        }
        header('Location: central_facilities.php?success=deleted');
    } catch (PDOException $e) {
        error_log('Facility delete error: ' . $e->getMessage());
        header('Location: central_facilities.php?success=delete_error');
    }
    exit;
}


/*
|--------------------------------------------------------------------------
| List
|--------------------------------------------------------------------------
*/
$items = [];
try {
    $items = $pdo->query("
        SELECT id, name, description, image, status, link_type, link_url, pdf_file
        FROM facilities
        WHERE category = 'central'
        ORDER BY display_order ASC, id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Facility list error: ' . $e->getMessage());
}

$csrf = accountCsrfToken();
$h = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

include 'header.php';
?>

<style>
    .fc-list { display: flex; flex-direction: column; gap: 12px; }

    .fc-item {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
        border: 1px solid #e5eaf0;
        border-radius: 14px;
        padding: 12px 16px 12px 10px;
    }

    .fc-item:hover { border-color: #cfdbe7; }

    .fc-handle {
        cursor: grab;
        color: #94a3b8;
        padding: 8px 6px;
        border-radius: 6px;
    }

    .fc-handle:hover { color: #184C74; background: #f1f5f9; }

    .fc-thumb {
        width: 120px;
        height: 76px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        background: #eef3f8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 22px;
    }

    .fc-body { flex: 1; min-width: 0; }
    .fc-body strong { display: block; color: #184C74; font-size: 15px; }

    .fc-body p {
        margin: 4px 0 0;
        font-size: 13px;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .fc-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 8px;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        text-decoration: none;
        vertical-align: middle;
    }

    .fc-link.pdf { background: #fee2e2; color: #b91c1c; }

    .fc-badge { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }
    .fc-on  { background: #dcfce7; color: #166534; }
    .fc-off { background: #f1f5f9; color: #64748b; }

    .fc-hint {
        display: flex;
        gap: 8px;
        align-items: center;
        font-size: 13px;
        color: #64748b;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 9px 14px;
        margin-bottom: 14px;
    }

    .sortable-ghost { opacity: .35; }
    .sortable-chosen { background: #FFF8D6 !important; }

    .fc-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9999;
        padding: 11px 18px;
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
        opacity: 0;
        transform: translateY(10px);
        transition: all .25s ease;
        pointer-events: none;
    }

    .fc-toast.show { opacity: 1; transform: none; }
    .fc-toast.ok { background: #166534; }
    .fc-toast.err { background: #b91c1c; }

    @media (max-width: 700px) {
        .fc-item { flex-wrap: wrap; }
        .fc-thumb { width: 96px; height: 62px; }
    }
</style>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Central Facilities</h2>
            <p>Equipment shown on the Facilities &gt; Central Facilities page.</p>
        </div>
        <div>
            <a href="facility_edit.php" class="btn-add">+ Add Equipment</a>
        </div>
    </div>

    <?php $s = $_GET['success'] ?? ''; ?>
    <?php if ($s === 'added'): ?><div class="alert alert-success">Equipment added successfully.</div>
    <?php elseif ($s === 'updated'): ?><div class="alert alert-success">Equipment updated successfully.</div>
    <?php elseif ($s === 'deleted'): ?><div class="alert alert-success">Equipment deleted successfully.</div>
    <?php elseif ($s === 'delete_error'): ?><div class="alert alert-danger">Unable to delete. Please try again.</div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>Equipment (<?= count($items) ?>)</h3>
                <p>Shown on the website in this order. Only Active items are shown.</p>
            </div>
        </div>

        <?php if (!empty($items)): ?>
            <div class="fc-hint"><i class="fas fa-grip-vertical"></i> Drag the handle to change the order. It is saved automatically.</div>

            <div class="fc-list" id="fcList">
                <?php foreach ($items as $it): ?>
                    <?php $img = trim((string)$it['image']); $hasImg = $img !== '' && is_file(FACILITY_IMG_DIR . $img); ?>
                    <div class="fc-item" data-id="<?= (int)$it['id'] ?>">
                        <span class="fc-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>

                        <?php if ($hasImg): ?>
                            <img class="fc-thumb" src="../images/facilities/<?= $h($img) ?>" alt="">
                        <?php else: ?>
                            <span class="fc-thumb"><i class="fas fa-microscope"></i></span>
                        <?php endif; ?>

                        <div class="fc-body">
                            <strong>
                                <?= $h($it['name']) ?>
                                <?php if ($it['link_type'] === 'url' && $it['link_url'] !== ''): ?>
                                    <a href="<?= $h($it['link_url']) ?>" target="_blank" rel="noopener" class="fc-link" title="<?= $h($it['link_url']) ?>"><i class="fas fa-arrow-up-right-from-square"></i> Link</a>
                                <?php elseif ($it['link_type'] === 'pdf' && $it['pdf_file'] !== ''): ?>
                                    <a href="../images/facilities/docs/<?= $h($it['pdf_file']) ?>" target="_blank" rel="noopener" class="fc-link pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                                <?php endif; ?>
                            </strong>
                            <p><?= $h($it['description']) ?></p>
                        </div>

                        <span class="fc-badge <?= (int)$it['status'] === 1 ? 'fc-on' : 'fc-off' ?>"><?= (int)$it['status'] === 1 ? 'Active' : 'Inactive' ?></span>

                        <div class="table-actions">
                            <a href="facility_edit.php?id=<?= (int)$it['id'] ?>" class="btn-edit">Edit</a>
                            <button type="button" class="btn-delete"
                                onclick="openDeleteModal(<?= (int)$it['id'] ?>, <?= $h(json_encode($it['name'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="table-empty">
                <p>No equipment added yet.</p>
                <a href="facility_edit.php" class="btn-add">+ Add Equipment</a>
            </div>
        <?php endif; ?>
    </div>

</div>


<!-- DELETE MODAL -->
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon">!</div>
        <h3>Delete Equipment?</h3>
        <p>Are you sure you want to delete <strong id="deleteName"></strong>?</p>
        <p class="delete-warning">This action cannot be undone.</p>
        <form method="POST" action="central_facilities.php">
            <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId" value="">
            <div class="delete-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<div id="fcToast" class="fc-toast"></div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteModal').classList.add('show');
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); }
document.getElementById('deleteModal').addEventListener('click', function (e) { if (e.target === this) closeDeleteModal(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDeleteModal(); });

function fcToast(msg, ok) {
    var t = document.getElementById('fcToast');
    t.textContent = msg;
    t.className = 'fc-toast show ' + (ok ? 'ok' : 'err');
    clearTimeout(fcToast.timer);
    fcToast.timer = setTimeout(function () { t.classList.remove('show'); }, 2200);
}

(function () {
    var list = document.getElementById('fcList');
    if (!list || typeof Sortable === 'undefined') return;

    Sortable.create(list, {
        handle: '.fc-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function (evt) {
            if (evt.oldIndex === evt.newIndex) return;
            var body = new URLSearchParams();
            body.append('action', 'reorder');
            body.append('csrf', <?= json_encode($csrf) ?>);
            list.querySelectorAll('.fc-item').forEach(function (el) { body.append('ids[]', el.dataset.id); });

            fetch('central_facilities.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
                .then(function (d) {
                    if (d && d.ok) fcToast('Order saved', true);
                    else { fcToast((d && d.error) || 'Unable to save the order', false); setTimeout(function () { location.reload(); }, 1500); }
                })
                .catch(function () { fcToast('Network error - order not saved', false); setTimeout(function () { location.reload(); }, 1500); });
        }
    });
})();
</script>

<?php include 'footer.php'; ?>
