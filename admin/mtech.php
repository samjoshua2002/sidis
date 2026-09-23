<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH M.TECH STUDENTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        programme,
        roll_number,
        student_name,
        mentor_1,
        mentor_2
    FROM mtech_students
    ORDER BY
        CASE programme
            WHEN 'Interdisciplinary Dual Degree Program (IDDD)' THEN 1
            WHEN 'International Interdisciplinary Masters Program (I2MP)' THEN 2
            WHEN 'Joint Masters Program (JMP)' THEN 3
            ELSE 4
        END,
        id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
            <h2>M.Tech Students</h2>
            <p>Manage M.Tech student details and programme information.</p>
        </div>

        <div>
            <a href="addmtech.php" class="btn-add">+ Add M.Tech Student</a>
        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>
            <div class="alert alert-success">M.Tech student added successfully.</div>

        <?php elseif ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success">M.Tech student updated successfully.</div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">M.Tech student deleted successfully.</div>

        <?php elseif ($_GET['success'] === 'delete_error'): ?>
            <div class="alert alert-danger">Unable to delete the M.Tech student. Please try again.</div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- M.TECH CARD -->

    <div class="dashboard-card">

        <div class="card-header">
            <div>
                <h3>M.Tech Students</h3>
                <p>View, edit or delete M.Tech student records.</p>
            </div>
        </div>


        <?php if (!empty($students)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="mtechSearch"
                        class="table-search-input"
                        placeholder="Search M.Tech students..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">&#128269;</span>

                </div>

                <div id="mtechSearchCount" class="search-result-count"></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table class="data-table" id="mtechTable">

                    <thead>
                        <tr>
                            <th>Programme</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Mentor 1</th>
                            <th>Mentor 2</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="mtechTableBody">

                        <?php foreach ($students as $student): ?>

                            <tr class="mtech-row">

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($student['programme'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student['roll_number'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student['student_name'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student['mentor_1'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student['mentor_2'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <div class="table-actions">

                                        <a href="editmtech.php?id=<?= (int)$student['id'] ?>" class="btn-edit">
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$student['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $student['student_name'],
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

            <div id="mtechNoResults" class="table-empty" style="display:none;">
                <p>No M.Tech students found.</p>
            </div>


            <!-- PAGINATION -->

            <div id="mtechPagination" class="table-pagination"></div>


        <?php else: ?>


            <div class="table-empty">

                <p>No M.Tech students found.</p>

                <a href="addmtech.php" class="btn-add">+ Add M.Tech Student</a>

            </div>


        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     DELETE CONFIRMATION MODAL
     ========================================================= -->

<div id="deleteModal" class="delete-modal">

    <div class="delete-modal-content">

        <div class="delete-modal-icon">!</div>

        <h3>Delete M.Tech Student?</h3>

        <p>
            Are you sure you want to delete
            <strong id="deleteStudentName"></strong>?
        </p>

        <p class="delete-warning">
            This action cannot be undone.
        </p>

        <form method="POST" action="deletemtech.php">

            <input type="hidden" name="id" id="deleteStudentId" value="">

            <div class="delete-modal-actions">

                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">
                    Cancel
                </button>

                <button type="submit" class="btn-confirm-delete">
                    Yes, Delete
                </button>

            </div>

        </form>

    </div>

</div>


<!-- STYLE FOR PAGINATION ELLIPSIS -->

<style>

.pagination-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    font-size: 14px;
    color: #6b7280;
    user-select: none;
    line-height: 1;
    padding: 0 4px;
}

</style>


<script>


/*
|--------------------------------------------------------------------------
| DELETE MODAL
|--------------------------------------------------------------------------
*/

function openDeleteModal(id, name)
{
    document.getElementById('deleteStudentId').value = id;
    document.getElementById('deleteStudentName').textContent = name;
    document.getElementById('deleteModal').classList.add('show');
}


function closeDeleteModal()
{
    document.getElementById('deleteModal').classList.remove('show');
}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/

document.getElementById('deleteModal').addEventListener(
    'click',
    function (event)
    {
        if (event.target === this)
        {
            closeDeleteModal();
        }
    }
);


/*
|--------------------------------------------------------------------------
| ESCAPE KEY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function (event)
    {
        if (event.key === 'Escape')
        {
            closeDeleteModal();
        }
    }
);


/*
|--------------------------------------------------------------------------
| SEARCH + PAGINATION
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function ()
    {

        const rows = Array.from(
            document.querySelectorAll(
                '#mtechTableBody .mtech-row'
            )
        );

        const searchInput = document.getElementById('mtechSearch');
        const pagination  = document.getElementById('mtechPagination');
        const noResults   = document.getElementById('mtechNoResults');
        const searchCount = document.getElementById('mtechSearchCount');

        const rowsPerPage = 5;

        let currentPage  = 1;
        let filteredRows = [...rows];


        /*
        |--------------------------------------------------------------------------
        | DISPLAY TABLE
        |--------------------------------------------------------------------------
        */

        function displayTable()
        {

            rows.forEach(
                function (row)
                {
                    row.style.display = 'none';
                }
            );

            const start = (currentPage - 1) * rowsPerPage;
            const end   = start + rowsPerPage;

            const currentRows = filteredRows.slice(start, end);

            currentRows.forEach(
                function (row)
                {
                    row.style.display = 'table-row';
                }
            );


            /*
            |--------------------------------------------------------------------------
            | SEARCH COUNT
            |--------------------------------------------------------------------------
            */

            if (filteredRows.length === 0)
            {
                searchCount.textContent = '';
                noResults.style.display = 'block';
            }
            else
            {
                searchCount.textContent =
                    filteredRows.length +
                    (filteredRows.length === 1 ? ' student' : ' students');

                noResults.style.display = 'none';
            }


            createPagination();

        }


        /*
        |--------------------------------------------------------------------------
        | HELPER: ADD A PAGE BUTTON
        |--------------------------------------------------------------------------
        */

        function addPageButton(page)
        {

            const pageButton = document.createElement('button');

            pageButton.type = 'button';
            pageButton.className = 'pagination-btn';
            pageButton.textContent = page;

            if (page === currentPage)
            {
                pageButton.classList.add('active');
            }

            pageButton.addEventListener(
                'click',
                function ()
                {
                    currentPage = page;
                    displayTable();
                }
            );

            pagination.appendChild(pageButton);

        }


        /*
        |--------------------------------------------------------------------------
        | HELPER: ADD ELLIPSIS (uses HTML entity to avoid encoding issues)
        |--------------------------------------------------------------------------
        */

        function addEllipsis()
        {

            const ellipsis = document.createElement('span');

            ellipsis.className = 'pagination-ellipsis';
            ellipsis.innerHTML = '&hellip;';

            pagination.appendChild(ellipsis);

        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION (compact, max 5 numbers in window + first + last)
        |--------------------------------------------------------------------------
        |
        | Examples:
        |   Page 1 of 50  ?  ‹ [1] 2 3 … 50 ›
        |   Page 5 of 50  ?  ‹ 1 … 4 [5] 6 … 50 ›
        |   Page 25 of 50 ?  ‹ 1 … 24 [25] 26 … 50 ›
        |   Page 50 of 50 ?  ‹ 1 … 48 49 [50] ›
        |
        */

        function createPagination()
        {

            pagination.innerHTML = '';

            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

            if (totalPages <= 1)
            {
                return;
            }


            /* PREVIOUS BUTTON */

            const previous = document.createElement('button');

            previous.type = 'button';
            previous.className = 'pagination-btn';
            previous.innerHTML = '&#10094;';
            previous.disabled = currentPage === 1;

            previous.addEventListener(
                'click',
                function ()
                {
                    if (currentPage > 1)
                    {
                        currentPage--;
                        displayTable();
                    }
                }
            );

            pagination.appendChild(previous);


            /*
            |--------------------------------------------------------------------------
            | BUILD COMPACT PAGE LIST
            |--------------------------------------------------------------------------
            |
            | Window: current ± 1  (so up to 3 numbers around current)
            | Plus first page and last page
            | Plus ellipsis where gaps exist
            |
            */

            const total      = totalPages;
            const current    = currentPage;
            const windowSize = 1;   // pages on each side of current

            let pages = [1];        // always first

            for (let i = current - windowSize; i <= current + windowSize; i++)
            {
                if (i > 1 && i < total)
                {
                    pages.push(i);
                }
            }

            if (total > 1)
            {
                pages.push(total);  // always last
            }

            // Deduplicate + sort
            pages = [...new Set(pages)].sort((a, b) => a - b);


            /*
            |--------------------------------------------------------------------------
            | RENDER WITH ELLIPSIS BETWEEN GAPS
            |--------------------------------------------------------------------------
            */

            let lastRendered = 0;

            for (let i = 0; i < pages.length; i++)
            {
                const pageNum = pages[i];

                if (lastRendered > 0 && pageNum - lastRendered > 1)
                {
                    addEllipsis();
                }

                addPageButton(pageNum);

                lastRendered = pageNum;
            }


            /* NEXT BUTTON */

            const next = document.createElement('button');

            next.type = 'button';
            next.className = 'pagination-btn';
            next.innerHTML = '&#10095;';
            next.disabled = currentPage === totalPages;

            next.addEventListener(
                'click',
                function ()
                {
                    if (currentPage < totalPages)
                    {
                        currentPage++;
                        displayTable();
                    }
                }
            );

            pagination.appendChild(next);

        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'input',
            function ()
            {

                const searchTerm = this.value.toLowerCase().trim();

                filteredRows = rows.filter(
                    function (row)
                    {
                        return row.textContent.toLowerCase().includes(searchTerm);
                    }
                );

                currentPage = 1;

                displayTable();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL DISPLAY
        |--------------------------------------------------------------------------
        */

        displayTable();

    }
);

</script>


<?php include 'footer.php'; ?>