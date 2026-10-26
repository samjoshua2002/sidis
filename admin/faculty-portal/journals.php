<?php
include 'header.php';

$success = '';
$error = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $success = 'Journal publication added successfully!';
    elseif ($_GET['success'] === 'updated') $success = 'Journal publication updated successfully!';
    elseif ($_GET['success'] === 'deleted') $success = 'Journal publication deleted successfully!';
}

// Handle Delete Journal Publication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_pub'])) {
    $delId = (int)reconstructChunks('delete_pub_id');

    if ($delId > 0) {
        try {
            $stmtDel = $pdo->prepare("DELETE FROM journalpub WHERE pub_id = :pub_id AND (id = :fac_id OR faculty_id = :fac_id)");
            $stmtDel->execute([':pub_id' => $delId, ':fac_id' => $faculty_id]);
            if (!headers_sent()) {
                header("Location: journals.php?success=deleted");
                exit;
            } else {
                echo "<script>window.location.href = 'journals.php?success=deleted';</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch journals for this faculty
$stmtJournals = $pdo->prepare("SELECT * FROM journalpub WHERE (id = :id OR faculty_id = :id) ORDER BY year DESC, pub_id DESC");
$stmtJournals->execute([':id' => $faculty_id]);
$journals = $stmtJournals->fetchAll(PDO::FETCH_ASSOC);

// Counts for tabs
$stmtConfCount = $pdo->prepare("SELECT COUNT(*) FROM confrncepub WHERE (id = :id OR faculty_id = :id)");
$stmtConfCount->execute([':id' => $faculty_id]);
$confCount = (int)$stmtConfCount->fetchColumn();

$stmtIndCount = $pdo->prepare("SELECT COUNT(*) FROM indpublication WHERE (id = :id OR faculty_id = :id)");
$stmtIndCount->execute([':id' => $faculty_id]);
$indCount = (int)$stmtIndCount->fetchColumn();
?>

<!-- Clean Page Header -->
<div class="content-header">
    <div class="content-header-title">
        <h1>Journal Publications</h1>
        <p>Manage peer-reviewed journal articles, research papers, and scientific letters shown on your inner profile.</p>
    </div>
    <div>
        <a href="add-journal.php" class="btn btn-primary" style="background:#184C74; color:#fff; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 6px rgba(24,76,116,0.25);">
            <i class="fa-solid fa-plus"></i> Add Journal Article
        </a>
    </div>
</div>

<!-- Publications Segmented Tabs Navigation -->
<div class="pub-nav-tabs">
    <a href="journals.php" class="pub-nav-tab active">
        <i class="fa-solid fa-book-open"></i>
        <span>Journal Articles</span>
        <span class="pub-tab-badge"><?= count($journals) ?></span>
    </a>
    <a href="conferences.php" class="pub-nav-tab">
        <i class="fa-solid fa-users-line"></i>
        <span>Conference Proceedings</span>
        <?php if ($confCount > 0): ?>
            <span class="pub-tab-badge"><?= $confCount ?></span>
        <?php endif; ?>
    </a>
    <a href="patents.php" class="pub-nav-tab">
        <i class="fa-solid fa-stamp"></i>
        <span>Patents</span>
    </a>
    <a href="books.php" class="pub-nav-tab">
        <i class="fa-solid fa-book"></i>
        <span>Books & Chapters</span>
    </a>
    <a href="other-publications.php" class="pub-nav-tab">
        <i class="fa-solid fa-file-lines"></i>
        <span>Publications</span>
        <?php if ($indCount > 0): ?>
            <span class="pub-tab-badge"><?= $indCount ?></span>
        <?php endif; ?>
    </a>
</div>

<style>
.pub-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    margin-bottom: 30px;
}
.pub-table-header {
    padding: 16px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #fafbfc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.pub-table-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15.5px;
    font-weight: 700;
    color: #1e293b;
}
.pub-table-title i {
    color: #184C74;
    font-size: 17px;
}
.pub-count-pill {
    background: #f1f5f9;
    color: #475569;
    padding: 2px 9px;
    border-radius: 12px;
    font-size: 11.5px;
    font-weight: 700;
    border: 1px solid #e2e8f0;
}
.pub-table-subtitle {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}
.pub-table-responsive {
    width: 100%;
    overflow-x: auto;
}
.pub-modern-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0;
}
.pub-modern-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 13px 18px;
    border-bottom: 1px solid #e2e8f0;
}
.pub-modern-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}
.pub-modern-table tbody tr:last-child {
    border-bottom: none;
}
.pub-modern-table tbody tr:hover {
    background: #fbfcfe;
}
.pub-modern-table tbody td {
    padding: 15px 18px;
    vertical-align: top;
    font-size: 13.5px;
    color: #334155;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.pub-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 14px;
    line-height: 1.45;
    margin-bottom: 4px;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.pub-author {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.4;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    margin-top: 4px;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.pub-doi-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    color: #184C74;
    background: #f0f7ff;
    padding: 3px 8px;
    border-radius: 5px;
    border: 1px solid #bfdbfe;
    text-decoration: none;
    margin-top: 6px;
    font-family: monospace;
    font-weight: 600;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    word-break: break-all;
    transition: all 0.15s ease;
}
.pub-doi-badge:hover {
    background: #184C74;
    color: #ffffff;
    border-color: #184C74;
}
.pub-venue-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 13px;
    line-height: 1.4;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.pub-meta-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}
.pub-tag {
    font-size: 11px;
    color: #475569;
    background: #f1f5f9;
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    font-weight: 500;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.pub-year-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #eff6ff;
    color: #1e40af;
    font-weight: 700;
    font-size: 12px;
    border-radius: 6px;
    border: 1px solid #dbeafe;
    word-break: break-word;
    overflow-wrap: anywhere;
    max-width: 100%;
}
.pub-action-btn-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.btn-pub-edit {
    padding: 6px 12px;
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #bae6fd;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.btn-pub-edit:hover {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
.btn-pub-delete {
    padding: 6px 10px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.btn-pub-delete:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
}
</style>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-circle-check" style="font-size:16px; color:#15803d;"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:16px; color:#dc2626;"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div class="pub-table-card">
    <div class="pub-table-header">
        <div class="pub-table-title">
            <i class="fa-solid fa-book-open"></i>
            <span>Published Journal Articles</span>
            <span class="pub-count-pill"><?= count($journals) ?> Total</span>
        </div>
        <div class="pub-table-subtitle">
            Shown under "Journals" section in your inner faculty profile
        </div>
    </div>

    <?php if (empty($journals)): ?>
        <div style="text-align:center; padding: 45px 20px; background:#f8fafc;">
            <i class="fa-solid fa-book-open" style="font-size:36px; color:#94a3b8; margin-bottom:12px;"></i>
            <h4 style="font-size:16px; color:#334155; margin-bottom:6px;">No Journal Articles Added Yet</h4>
            <p style="color:#64748b; font-size:13.5px; margin-bottom:18px;">Add your peer-reviewed publications so they appear on your SIDIS inner page.</p>
            <a href="add-journal.php" class="btn btn-primary" style="background:#184C74; color:#fff; padding:8px 18px; border-radius:6px; text-decoration:none; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-plus"></i> Add Your First Journal Article
            </a>
        </div>
    <?php else: ?>
        <div class="pub-table-responsive">
            <table class="pub-modern-table">
                <thead>
                    <tr>
                        <th style="width: 48%;">Title & Authors</th>
                        <th style="width: 25%;">Journal / Details</th>
                        <th style="width: 12%; text-align: center;">Year</th>
                        <th style="width: 15%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($journals as $pub): ?>
                        <tr>
                            <!-- Title & Authors -->
                            <td>
                                <div class="pub-title">
                                    <?= htmlspecialchars($pub['title']) ?>
                                </div>
                                <?php if (!empty($pub['author'])): ?>
                                    <div class="pub-author">
                                        <i class="fa-solid fa-user-pen" style="font-size: 11px; margin-top: 3px; color: #94a3b8;"></i>
                                        <span><?= htmlspecialchars($pub['author']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($pub['doi'])): ?>
                                    <?php 
                                    $doiLink = !empty($pub['doilink']) ? $pub['doilink'] : 'https://doi.org/' . ltrim($pub['doi'], '/');
                                    ?>
                                    <div>
                                        <a href="<?= htmlspecialchars($doiLink) ?>" target="_blank" class="pub-doi-badge" title="Open DOI Link">
                                            <i class="fa-solid fa-link" style="font-size: 10px;"></i>
                                            <span>DOI: <?= htmlspecialchars($pub['doi']) ?></span>
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 9px; opacity: 0.7;"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Journal & Details -->
                            <td>
                                <div class="pub-venue-name">
                                    <?= htmlspecialchars($pub['journal'] ?? '—') ?>
                                </div>
                                <?php 
                                $volText = trim((string)($pub['volume'] ?? ''));
                                $pageText = trim((string)($pub['page'] ?? ''));
                                ?>
                                <?php if ($volText !== '' || $pageText !== ''): ?>
                                    <div class="pub-meta-tags">
                                        <?php if ($volText !== ''): ?>
                                            <span class="pub-tag">
                                                <?= (stripos($volText, 'vol') === 0) ? htmlspecialchars($volText) : 'Vol. ' . htmlspecialchars($volText) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($pageText !== ''): ?>
                                            <span class="pub-tag">
                                                <?= (stripos($pageText, 'pp') === 0 || stripos($pageText, 'p.') === 0) ? htmlspecialchars($pageText) : 'pp. ' . htmlspecialchars($pageText) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Year -->
                            <td style="text-align: center;">
                                <span class="pub-year-badge">
                                    <?= htmlspecialchars($pub['year'] ?? '—') ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div class="pub-action-btn-group">
                                    <a href="edit-journal.php?id=<?= (int)$pub['pub_id'] ?>" class="btn-pub-edit" title="Edit Article">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <button type="button" onclick="confirmDeletePub(<?= (int)$pub['pub_id'] ?>, <?= htmlspecialchars(json_encode($pub['title'])) ?>)" class="btn-pub-delete" title="Delete Article">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Delete Confirmation -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#ffffff; border-radius:10px; padding:24px 28px; max-width:480px; width:90%; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; color:#dc2626; font-size:18px;">
                <i class="fa-solid fa-trash"></i>
            </div>
            <div>
                <h4 style="margin:0; font-size:17px; color:#0f172a;">Delete Journal Article?</h4>
                <p style="margin:0; font-size:12px; color:#64748b;">This action cannot be undone.</p>
            </div>
        </div>
        <p id="deletePubTitle" style="font-size:13.5px; color:#334155; margin-bottom:20px; padding:10px 14px; background:#f8fafc; border-radius:6px; border-left:3px solid #dc2626; word-break:break-word;"></p>
        
        <form method="POST" action="" id="deletePubForm" onsubmit="return submitDeletePub()">
            <input type="hidden" name="action_delete_pub" value="1">
            <input type="hidden" id="del_pub_id_raw" value="">
            <input type="hidden" id="del_pub_id_chunk_count" name="delete_pub_id_chunk_count" value="0">
            <div id="del_pub_id_chunks"></div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeDeleteModal()" style="padding:8px 16px; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding:8px 16px; background:#dc2626; color:#ffffff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">
                    Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDeletePub(id, title) {
    document.getElementById('deletePubTitle').textContent = title;
    document.getElementById('del_pub_id_raw').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function submitDeletePub() {
    const id = document.getElementById('del_pub_id_raw').value;
    const b64 = b64EncodeUtf8(id);
    const chunks = splitIntoChunks(b64, 200);
    document.getElementById('del_pub_id_chunk_count').value = chunks.length;

    const container = document.getElementById('del_pub_id_chunks');
    container.innerHTML = '';
    chunks.forEach((chunk, i) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `delete_pub_id_chunk_${i}`;
        input.value = chunk;
        container.appendChild(input);
    });
    return true;
}
</script>

<?php include 'footer.php'; ?>
