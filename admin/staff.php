<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH STAFF
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        designation,
        image,
        status,
        display_order
    FROM staff
    ORDER BY display_order ASC, id ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute();


$staff =
    $stmt->fetchAll(PDO::FETCH_ASSOC);


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

            <h2>Staff</h2>

            <p>
                Manage staff members displayed on the website.
            </p>

        </div>


        <div>

            <a
                href="addstaff.php"
                class="btn-add"
            >
                + Add Staff
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>


        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Staff member added successfully.
            </div>


        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Staff member updated successfully.
            </div>


        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Staff member deleted successfully.
            </div>


        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">
                Unable to delete the staff member. Please try again.
            </div>

        <?php endif; ?>


    <?php endif; ?>


    <!-- STAFF CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Staff Members</h3>

                <p>
                    View, edit or delete staff members.
                </p>

            </div>

        </div>


        <?php if (!empty($staff)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="staffSearch"
                        class="table-search-input"
                        placeholder="Search staff..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>


                <div
                    id="staffSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="staffTable"
                >

                    <thead>

                        <tr>

                            <th>Image</th>

                            <th>Name</th>

                            <th>Designation</th>

                            <!-- <th>Display Order</th>

                            <th>Status</th> -->

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="staffTableBody">


                        <?php foreach ($staff as $member): ?>


                            <tr class="staff-row">


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($member['image'])): ?>

                                        <img
                                            src="../images/staff/<?= htmlspecialchars(
                                                $member['image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $member['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            style="
                                                width:70px;
                                                height:70px;
                                                object-fit:cover;
                                                border-radius:6px;
                                            "
                                        >

                                    <?php else: ?>

                                        <span>
                                            No Image
                                        </span>

                                    <?php endif; ?>

                                </td>


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


                              

                                <!-- <td>

                                    <?= (int)$member['display_order'] ?>

                                </td>


                              

                                <td>

                                    <?php if ((int)$member['status'] === 1): ?>

                                        <span
                                            style="
                                                display:inline-block;
                                                padding:5px 10px;
                                                border-radius:4px;
                                                background:#d4edda;
                                                color:#155724;
                                                font-size:13px;
                                            "
                                        >
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span
                                            style="
                                                display:inline-block;
                                                padding:5px 10px;
                                                border-radius:4px;
                                                background:#f8d7da;
                                                color:#721c24;
                                                font-size:13px;
                                            "
                                        >
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td> -->


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">


                                        <a
                                            href="editstaff.php?id=<?= (int)$member['id'] ?>"
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
                id="staffNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No staff members found.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="staffPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No staff members found.
                </p>


                <a
                    href="addstaff.php"
                    class="btn-add"
                >
                    + Add Staff
                </a>

            </div>


        <?php endif; ?>


    </div>

</div>


<!-- =========================================================
     DELETE MODAL
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
            Delete Staff Member?
        </h3>


        <p>
            Are you sure you want to delete this staff member?
        </p>


        <p>
            <strong id="deleteStaffName"></strong>
        </p>


        <p class="delete-warning">
            This action cannot be undone.
        </p>


        <form
            method="POST"
            action="deletestaff.php"
        >

            <input
                type="hidden"
                name="id"
                id="deleteStaffId"
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

    document.getElementById(
        'deleteStaffId'
    ).value = id;


    document.getElementById(
        'deleteStaffName'
    ).textContent = name;


    document.getElementById(
        'deleteModal'
    ).classList.add('show');

}


function closeDeleteModal()
{

    document.getElementById(
        'deleteModal'
    ).classList.remove('show');

}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL OUTSIDE
|--------------------------------------------------------------------------
*/

document.getElementById(
    'deleteModal'
).addEventListener(
    'click',
    function(event)
    {

        if (event.target === this)
        {
            closeDeleteModal();
        }

    }
);


/*
|--------------------------------------------------------------------------
| ESCAPE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event)
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
    function()
    {

        const rows = Array.from(
            document.querySelectorAll(
                '#staffTableBody .staff-row'
            )
        );


        const searchInput =
            document.getElementById(
                'staffSearch'
            );


        const pagination =
            document.getElementById(
                'staffPagination'
            );


        const noResults =
            document.getElementById(
                'staffNoResults'
            );


        const searchCount =
            document.getElementById(
                'staffSearchCount'
            );


        const rowsPerPage = 5;


        let currentPage = 1;


        let filteredRows = [...rows];


        function displayTable()
        {

            rows.forEach(
                function(row)
                {
                    row.style.display = 'none';
                }
            );


            const start =
                (currentPage - 1) *
                rowsPerPage;


            const end =
                start +
                rowsPerPage;


            filteredRows
                .slice(start, end)
                .forEach(
                    function(row)
                    {
                        row.style.display =
                            'table-row';
                    }
                );


            if (filteredRows.length === 0)
            {

                searchCount.textContent = '';

                noResults.style.display =
                    'block';

            }
            else
            {

                searchCount.textContent =
                    filteredRows.length +
                    (
                        filteredRows.length === 1
                            ? ' staff member'
                            : ' staff members'
                    );


                noResults.style.display =
                    'none';

            }


            createPagination();

        }


        function createPagination()
        {

            pagination.innerHTML = '';


            const totalPages =
                Math.ceil(
                    filteredRows.length /
                    rowsPerPage
                );


            if (totalPages <= 1)
            {
                return;
            }


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
                function()
                {

                    if (currentPage > 1)
                    {

                        currentPage--;

                        displayTable();

                    }

                }
            );


            pagination.appendChild(previous);


            for (
                let page = 1;
                page <= totalPages;
                page++
            )
            {

                const pageButton =
                    document.createElement(
                        'button'
                    );


                pageButton.type =
                    'button';


                pageButton.className =
                    'pagination-btn';


                pageButton.textContent =
                    page;


                if (page === currentPage)
                {

                    pageButton.classList.add(
                        'active'
                    );

                }


                pageButton.addEventListener(
                    'click',
                    function()
                    {

                        currentPage =
                            page;

                        displayTable();

                    }
                );


                pagination.appendChild(
                    pageButton
                );

            }


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
                function()
                {

                    if (
                        currentPage <
                        totalPages
                    )
                    {

                        currentPage++;

                        displayTable();

                    }

                }
            );


            pagination.appendChild(next);

        }


        searchInput.addEventListener(
            'input',
            function()
            {

                const searchTerm =
                    this.value
                        .toLowerCase()
                        .trim();


                filteredRows =
                    rows.filter(
                        function(row)
                        {

                            return row.textContent
                                .toLowerCase()
                                .includes(
                                    searchTerm
                                );

                        }
                    );


                currentPage = 1;

                displayTable();

            }
        );


        displayTable();

    }
);

</script>


<?php include 'footer.php'; ?>