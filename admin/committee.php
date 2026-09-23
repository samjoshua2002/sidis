<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH COMMITTEE MEMBERS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        designation
    FROM advisory_committee
    ORDER BY id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$committeeMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

            <h2>Advisory Committee</h2>

            <p>
                Manage advisory committee members.
            </p>

        </div>

        <div>

            <a href="addcommittee.php" class="btn-add">
                + Add Committee Member
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Committee member added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Committee member updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Committee member deleted successfully.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- COMMITTEE CARD -->

    <div class="dashboard-card">

        <div class="card-header">

            <div>

                <h3>Committee Members</h3>

                <p>
                    View, edit or delete advisory committee members.
                </p>

            </div>

        </div>


        <?php if (!empty($committeeMembers)): ?>

            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="committeeSearch"
                        class="table-search-input"
                        placeholder="Search committee members..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>

                <div
                    id="committeeSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="committeeTable"
                >

                    <thead>

                        <tr>

                            <!-- <th>ID</th> -->

                            <th>Name</th>

                            <th>Designation</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="committeeTableBody">

                        <?php foreach ($committeeMembers as $member): ?>

                            <tr class="committee-row">

                                <!-- ID -->

                                <!-- <td>

                                    <span class="table-id">

                                        <?= (int)$member['id'] ?>

                                    </span>

                                </td> -->


                                <!-- NAME -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $member['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- DESIGNATION -->

                                <td>

                                    <?= htmlspecialchars(
                                        $member['designation'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editcommittee.php?id=<?= (int)$member['id'] ?>"
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
                id="committeeNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No committee members found.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="committeePagination"
                class="table-pagination"
            ></div>


        <?php else: ?>

            <div class="table-empty">

                <p>
                    No advisory committee members found.
                </p>

                <a
                    href="addcommittee.php"
                    class="btn-add"
                >
                    + Add Committee Member
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>


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
            Delete Committee Member?
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
            action="deletecommittee.php"
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

function openDeleteModal(id, name)
{
    document.getElementById('deleteMemberId').value = id;

    document.getElementById('deleteMemberName').textContent = name;

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
                '#committeeTableBody .committee-row'
            )
        );


        const searchInput =
            document.getElementById('committeeSearch');


        const pagination =
            document.getElementById('committeePagination');


        const noResults =
            document.getElementById('committeeNoResults');


        const searchCount =
            document.getElementById('committeeSearchCount');


        const rowsPerPage = 5;


        let currentPage = 1;


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


            const start =
                (currentPage - 1) * rowsPerPage;


            const end =
                start + rowsPerPage;


            const currentRows =
                filteredRows.slice(start, end);


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
                    (
                        filteredRows.length === 1
                            ? ' member'
                            : ' members'
                    );

                noResults.style.display = 'none';
            }


            createPagination();
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        function createPagination()
        {

            pagination.innerHTML = '';


            const totalPages =
                Math.ceil(
                    filteredRows.length / rowsPerPage
                );


            if (totalPages <= 1)
            {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS
            |--------------------------------------------------------------------------
            */

            const previous =
                document.createElement('button');


            previous.type = 'button';

            previous.className =
                'pagination-btn';


            previous.innerHTML =
                '&#10094;';


            previous.disabled =
                currentPage === 1;


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
            | PAGE NUMBERS
            |--------------------------------------------------------------------------
            */

            for (
                let page = 1;
                page <= totalPages;
                page++
            )
            {

                const pageButton =
                    document.createElement('button');


                pageButton.type = 'button';


                pageButton.className =
                    'pagination-btn';


                pageButton.textContent =
                    page;


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
            | NEXT
            |--------------------------------------------------------------------------
            */

            const next =
                document.createElement('button');


            next.type = 'button';

            next.className =
                'pagination-btn';


            next.innerHTML =
                '&#10095;';


            next.disabled =
                currentPage === totalPages;


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

                const searchTerm =
                    this.value
                        .toLowerCase()
                        .trim();


                filteredRows =
                    rows.filter(
                        function (row)
                        {

                            return row.textContent
                                .toLowerCase()
                                .includes(searchTerm);

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