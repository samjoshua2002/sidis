<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH SCC MEMBERS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        designation,
        committee_section
    FROM school_consultative_committee
    ORDER BY id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$committeeMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| GET UNIQUE COMMITTEE SECTIONS (for dropdown)
|--------------------------------------------------------------------------
*/

$uniqueSections = [];

foreach ($committeeMembers as $member) {

    $section = trim($member['committee_section'] ?? '');

    if ($section !== '' && !in_array($section, $uniqueSections, true)) {
        $uniqueSections[] = $section;
    }

}

sort($uniqueSections);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>School Consultative Committee (SCC)</h2>

            <p>
                Manage School Consultative Committee members.
            </p>

        </div>


        <div>

            <a
                href="addscc.php"
                class="btn-add"
            >
                + Add SCC Member
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                SCC member added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                SCC member updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                SCC member deleted successfully.
            </div>

        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">
                Unable to delete the SCC member. Please try again.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- SCC CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>SCC Members</h3>

                <p>
                    View, edit or delete School Consultative Committee members.
                </p>

            </div>

        </div>


        <?php if (!empty($committeeMembers)): ?>


            <!-- SEARCH + FILTER DROPDOWN -->

            <div class="table-toolbar">

                <!-- SEARCH (LEFT) -->

                <div class="table-search-wrapper">

                    <div class="table-search">

                        <input
                            type="text"
                            id="sccSearch"
                            class="table-search-input"
                            placeholder="Search SCC members..."
                            autocomplete="off"
                        >

                        <span class="table-search-icon">
                            &#128269;
                        </span>

                    </div>

                    <div
                        id="sccSearchCount"
                        class="search-result-count"
                    ></div>

                </div>


                <!-- FILTER DROPDOWN (RIGHT) -->

                <div class="filter-dropdown-wrapper">

                    <label
                        for="sccFilterDropdown"
                        class="filter-dropdown-label"
                    >
                        Filter:
                    </label>

                    <select
                        id="sccFilterDropdown"
                        class="filter-dropdown"
                    >

                        <option value="">
                            All Sections (<?= count($committeeMembers) ?>)
                        </option>

                        <?php foreach ($uniqueSections as $section): ?>

                            <?php
                            $sectionCount = 0;
                            foreach ($committeeMembers as $member) {
                                if (trim($member['committee_section']) === $section) {
                                    $sectionCount++;
                                }
                            }
                            ?>

                            <option value="<?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?>
                                (<?= $sectionCount ?>)
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="sccTable"
                >

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Designation</th>

                            <th>Committee Section</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="sccTableBody">

                        <?php foreach ($committeeMembers as $member): ?>

                            <tr
                                class="scc-row"
                                data-section="<?= htmlspecialchars(trim($member['committee_section']), ENT_QUOTES, 'UTF-8') ?>"
                            >

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars($member['designation'], ENT_QUOTES, 'UTF-8') ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars($member['committee_section'], ENT_QUOTES, 'UTF-8') ?>

                                </td>


                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editscc.php?id=<?= (int)$member['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$member['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $member['name'],
                                                        JSON_HEX_TAG |
                                                        JSON_HEX_AMP |
                                                        JSON_HEX_APOS |
                                                        JSON_HEX_QUOT
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

            <div
                id="sccNoResults"
                class="table-empty"
                style="display:none;"
            >
                <p>
                    No SCC members found.
                </p>
            </div>


            <!-- PAGINATION -->

            <div
                id="sccPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>

            <div class="table-empty">

                <p>
                    No SCC members found.
                </p>

                <a
                    href="addscc.php"
                    class="btn-add"
                >
                    + Add SCC Member
                </a>

            </div>

        <?php endif; ?>


    </div>

</div>


<!-- STYLES FOR SEARCH + FILTER DROPDOWN -->

<style>

.table-toolbar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

/* SEARCH (left) */
.table-search-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1 1 320px;
    max-width: 400px;
}

/* FILTER DROPDOWN (right) */
.filter-dropdown-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}

.filter-dropdown-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0;
    white-space: nowrap;
}

.filter-dropdown {
    padding: 9px 36px 9px 14px;
    font-size: 14px;
    font-weight: 500;
    color: #1f2937;
    background-color: #fff;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    min-width: 220px;
}

.filter-dropdown:hover {
    border-color: #9ca3af;
}

.filter-dropdown:focus {
    outline: none;
    border-color: #1e3a5f;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

/* Mobile: stack vertically */
@media (max-width: 640px) {

    .table-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .table-search-wrapper {
        max-width: 100%;
    }

    .filter-dropdown-wrapper {
        justify-content: flex-start;
    }

    .filter-dropdown {
        width: 100%;
        min-width: 0;
    }

}

</style>


<!-- =========================================================
     DELETE CONFIRMATION MODAL
     ========================================================= -->

<div
    id="deleteModal"
    class="delete-modal"
>

    <div class="delete-modal-content">

        <div class="delete-modal-icon">
            !
        </div>

        <h3>
            Delete SCC Member?
        </h3>

        <p>

            Are you sure you want to delete
            <strong id="deleteMemberName"></strong>?

        </p>

        <p class="delete-warning">
            This action cannot be undone.
        </p>

        <form
            method="POST"
            action="deletescc.php"
        >

            <input
                type="hidden"
                name="id"
                id="deleteMemberId"
                value=""
            >

            <div class="delete-modal-actions">

                <button
                    type="button"
                    class="btn-cancel"
                    onclick="closeDeleteModal()"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn-confirm-delete"
                >
                    Yes, Delete
                </button>

            </div>

        </form>

    </div>

</div>


<script>


/*
|--------------------------------------------------------------------------
| DELETE MODAL
|--------------------------------------------------------------------------
*/

function openDeleteModal(id, name) {

    document.getElementById('deleteMemberId').value = id;

    document.getElementById('deleteMemberName').textContent = name;

    document.getElementById('deleteModal').classList.add('show');

}


function closeDeleteModal() {

    document.getElementById('deleteModal').classList.remove('show');

}


document.getElementById('deleteModal').addEventListener('click', function (event) {

    if (event.target === this) {
        closeDeleteModal();
    }

});


document.addEventListener('keydown', function (event) {

    if (event.key === 'Escape') {
        closeDeleteModal();
    }

});


/*
|--------------------------------------------------------------------------
| SEARCH + FILTER DROPDOWN + PAGINATION
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    const rows = Array.from(
        document.querySelectorAll('#sccTableBody .scc-row')
    );

    const searchInput    = document.getElementById('sccSearch');
    const filterDropdown = document.getElementById('sccFilterDropdown');
    const pagination     = document.getElementById('sccPagination');
    const noResults      = document.getElementById('sccNoResults');
    const searchCount    = document.getElementById('sccSearchCount');

    const rowsPerPage = 5;

    let currentPage  = 1;
    let activeFilter = '';
    let searchTerm   = '';
    let filteredRows = [...rows];


    /*
    |--------------------------------------------------------------------------
    | APPLY FILTER + SEARCH
    |--------------------------------------------------------------------------
    */

    function applyFilters() {

        filteredRows = rows.filter(function (row) {

            const rowSection = (row.dataset.section || '').toLowerCase();
            const rowText    = row.textContent.toLowerCase();

            const matchesFilter =
                activeFilter === '' ||
                rowSection === activeFilter.toLowerCase();

            const matchesSearch =
                searchTerm === '' ||
                rowText.includes(searchTerm);

            return matchesFilter && matchesSearch;

        });

        currentPage = 1;

        displayTable();

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY TABLE
    |--------------------------------------------------------------------------
    */

    function displayTable() {

        rows.forEach(function (row) {
            row.style.display = 'none';
        });

        const start = (currentPage - 1) * rowsPerPage;
        const end   = start + rowsPerPage;

        const currentRows = filteredRows.slice(start, end);

        currentRows.forEach(function (row) {
            row.style.display = 'table-row';
        });


        /*
        |--------------------------------------------------------------------------
        | SEARCH COUNT
        |--------------------------------------------------------------------------
        */

        if (filteredRows.length === 0) {

            searchCount.textContent = '';
            noResults.style.display = 'block';

        } else {

            searchCount.textContent =
                filteredRows.length +
                (filteredRows.length === 1 ? ' member' : ' members');

            noResults.style.display = 'none';

        }

        createPagination();

    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    function createPagination() {

        pagination.innerHTML = '';

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        if (totalPages <= 1) {
            return;
        }


        /* PREVIOUS */

        const previous = document.createElement('button');
        previous.type  = 'button';
        previous.className = 'pagination-btn';
        previous.innerHTML = '&#10094;';
        previous.disabled = currentPage === 1;

        previous.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                displayTable();
            }
        });

        pagination.appendChild(previous);


        /* PAGE NUMBERS */

        for (let page = 1; page <= totalPages; page++) {

            const pageButton = document.createElement('button');
            pageButton.type  = 'button';
            pageButton.className = 'pagination-btn';
            pageButton.textContent = page;

            if (page === currentPage) {
                pageButton.classList.add('active');
            }

            pageButton.addEventListener('click', function () {
                currentPage = page;
                displayTable();
            });

            pagination.appendChild(pageButton);

        }


        /* NEXT */

        const next = document.createElement('button');
        next.type  = 'button';
        next.className = 'pagination-btn';
        next.innerHTML = '&#10095;';
        next.disabled = currentPage === totalPages;

        next.addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                displayTable();
            }
        });

        pagination.appendChild(next);

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH INPUT
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener('input', function () {

        searchTerm = this.value.toLowerCase().trim();

        applyFilters();

    });


    /*
    |--------------------------------------------------------------------------
    | FILTER DROPDOWN
    |--------------------------------------------------------------------------
    */

    if (filterDropdown) {

        filterDropdown.addEventListener('change', function () {

            activeFilter = this.value || '';

            applyFilters();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL DISPLAY
    |--------------------------------------------------------------------------
    */

    displayTable();

});

</script>


<?php include 'footer.php'; ?>