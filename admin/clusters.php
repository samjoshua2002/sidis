<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

function resolveAdminThumb($path) {
    if (empty($path)) return '../images/frame 3.png';
    $path = trim($path);
    if (strpos($path, 'images/') === 0) {
        return '../' . $path;
    }
    if (strpos($path, '/') === 0) {
        return $path;
    }
    if (file_exists(__DIR__ . '/../images/clusters/' . $path)) {
        return '../images/clusters/' . $path;
    }
    if (file_exists(__DIR__ . '/../images/' . $path)) {
        return '../images/' . $path;
    }
    return '../images/clusters/' . $path;
}

/*
|--------------------------------------------------------------------------
| FETCH CLUSTERS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        cluster_name,
        card_image,
        page_type,
        website_url,
        display_order,
        status
    FROM clusters
    WHERE status = 1
    ORDER BY display_order ASC, id ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clusters = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="dashboard-content">
    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2>Clusters</h2>
            <p>Manage clusters displayed on the website.</p>
        </div>
        <div>
            <a href="addcluster.php" class="btn-add">
                + Add Cluster
            </a>
        </div>
    </div>

    <!-- SUCCESS MESSAGE -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'added'): ?>
            <div class="alert alert-success">Cluster added successfully.</div>
        <?php elseif ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success">Cluster updated successfully.</div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">Cluster deleted successfully.</div>
        <?php elseif ($_GET['success'] === 'delete_error'): ?>
            <div class="alert alert-danger">Unable to delete the cluster. Please try again.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- CLUSTERS CARD -->
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>All Clusters</h3>
                <p>View, edit or delete clusters.</p>
            </div>
        </div>

        <?php if (!empty($clusters)): ?>
            <!-- SEARCH -->
            <div class="table-toolbar">
                <div class="table-search">
                    <input
                        type="text"
                        id="clusterSearch"
                        class="table-search-input"
                        placeholder="Search clusters..."
                        autocomplete="off"
                    >
                    <span class="table-search-icon">&#128269;</span>
                </div>
                <div id="clusterSearchCount" class="search-result-count"></div>
            </div>

            <!-- TABLE -->
            <div class="table-wrapper">
                <table class="data-table" id="clusterTable">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Card</th>
                            <th>Cluster Name</th>
                            <th style="width: 140px;">Type</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="clusterTableBody">
                        <?php foreach ($clusters as $cluster): ?>
                            <?php
                            $cardImgThumb = resolveAdminThumb($cluster['card_image']);
                            $isExternal = ($cluster['page_type'] === 'website' && !empty($cluster['website_url']));
                            ?>

                            <tr class="cluster-row">
                                <!-- CARD IMAGE -->
                                <td>
                                    <img
                                        src="<?= htmlspecialchars($cardImgThumb, ENT_QUOTES, 'UTF-8') ?>"
                                        alt=""
                                        style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                                    >
                                </td>

                                <!-- CLUSTER NAME -->
                                <td>
                                    <strong style="color: #184C74; font-size: 15px;">
                                        <?= htmlspecialchars($cluster['cluster_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <?php if ($isExternal): ?>
                                        <div style="font-size: 12px; margin-top: 3px;">
                                            <a href="<?= htmlspecialchars($cluster['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="color: #047857;">
                                                <?= htmlspecialchars($cluster['website_url'], ENT_QUOTES, 'UTF-8') ?> &#8599;
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- TYPE -->
                                <td>
                                    <?php if ($isExternal): ?>
                                        <span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 500;">
                                            External Link
                                        </span>
                                    <?php else: ?>
                                        <span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #f0fdf4; color: #15803d; font-size: 12px; font-weight: 500;">
                                            Inner Page
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS -->
                                <td>
                                    <div class="table-actions">
                                        <a
                                            href="editcluster.php?id=<?= (int)$cluster['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$cluster['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $cluster['cluster_name'],
                                                        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                                                    ),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            )"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- NO SEARCH RESULTS -->
            <div id="clusterNoResults" class="table-empty" style="display:none;">
                <p>No clusters found.</p>
            </div>

            <!-- PAGINATION -->
            <div id="clusterPagination" class="table-pagination"></div>

        <?php else: ?>
            <div class="table-empty">
                <p>No clusters found.</p>
                <a href="addcluster.php" class="btn-add">+ Add Cluster</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-icon">!</div>
        <h3>Delete Cluster?</h3>
        <p>Are you sure you want to delete this cluster?</p>
        <p><strong id="deleteClusterName"></strong></p>
        <p class="delete-warning">This action cannot be undone.</p>
        <form method="POST" action="deletecluster.php">
            <input type="hidden" name="id" id="deleteClusterId" value="">
            <div class="delete-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteClusterId').value = id;
    document.getElementById('deleteClusterName').textContent = name;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal').addEventListener('click', function(event) {
    if (event.target === this) closeDeleteModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeDeleteModal();
});

document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('#clusterTableBody .cluster-row'));
    const searchInput = document.getElementById('clusterSearch');
    const pagination = document.getElementById('clusterPagination');
    const noResults = document.getElementById('clusterNoResults');
    const searchCount = document.getElementById('clusterSearchCount');

    const rowsPerPage = 10;
    let currentPage = 1;
    let filteredRows = [...rows];

    function displayTable() {
        rows.forEach(r => r.style.display = 'none');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.slice(start, end).forEach(r => r.style.display = 'table-row');

        if (filteredRows.length === 0) {
            searchCount.textContent = '';
            noResults.style.display = 'block';
        } else {
            searchCount.textContent = filteredRows.length + (filteredRows.length === 1 ? ' cluster' : ' clusters');
            noResults.style.display = 'none';
        }
        createPagination();
    }

    function createPagination() {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (totalPages <= 1) return;

        const previous = document.createElement('button');
        previous.type = 'button';
        previous.className = 'pagination-btn';
        previous.innerHTML = '&#10094;';
        previous.disabled = currentPage === 1;
        previous.addEventListener('click', function() {
            if (currentPage > 1) { currentPage--; displayTable(); }
        });
        pagination.appendChild(previous);

        for (let page = 1; page <= totalPages; page++) {
            const pageButton = document.createElement('button');
            pageButton.type = 'button';
            pageButton.className = 'pagination-btn';
            pageButton.textContent = page;
            if (page === currentPage) pageButton.classList.add('active');
            pageButton.addEventListener('click', function() {
                currentPage = page;
                displayTable();
            });
            pagination.appendChild(pageButton);
        }

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'pagination-btn';
        next.innerHTML = '&#10095;';
        next.disabled = currentPage === totalPages;
        next.addEventListener('click', function() {
            if (currentPage < totalPages) { currentPage++; displayTable(); }
        });
        pagination.appendChild(next);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            filteredRows = rows.filter(r => r.textContent.toLowerCase().includes(term));
            currentPage = 1;
            displayTable();
        });
    }

    displayTable();
});
</script>

<?php include 'footer.php'; ?>