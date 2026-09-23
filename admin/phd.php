<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH PH.D. SCHOLARS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        batch,
        roll_number,
        student_name,
        mail_id,
        mentor_1,
        mentor_2
    FROM phd_scholars
    ORDER BY id ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute();


$scholars =
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

            <h2>Ph.D. Scholars</h2>

            <p>
                Manage Ph.D. scholar details and batch information.
            </p>

        </div>


        <div>

            <a
                href="addphd.php"
                class="btn-add"
            >
                + Add Ph.D. Scholar
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">

                Ph.D. scholar added successfully.

            </div>


        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">

                Ph.D. scholar updated successfully.

            </div>


        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">

                Ph.D. scholar deleted successfully.

            </div>


        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">

                Unable to delete the Ph.D. scholar. Please try again.

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- PH.D. CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Ph.D. Scholars</h3>

                <p>
                    View, edit or delete Ph.D. scholar records.
                </p>

            </div>

        </div>


        <?php if (!empty($scholars)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="phdSearch"
                        class="table-search-input"
                        placeholder="Search Ph.D. scholars..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>


                <div
                    id="phdSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="phdTable"
                >

                    <thead>

                        <tr>

                            <th>Batch</th>

                            <th>Roll Number</th>

                            <th>Student Name</th>

                            <th>Mail ID</th>

                            <th>Mentor 1</th>

                            <th>Mentor 2</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="phdTableBody">


                        <?php foreach ($scholars as $scholar): ?>


                            <tr class="phd-row">


                                <!-- BATCH -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $scholar['batch'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- ROLL NUMBER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $scholar['roll_number'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- STUDENT NAME -->

                                <td>

                                    <?= htmlspecialchars(
                                        $scholar['student_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MAIL ID -->

                                <td>

                                    <?= htmlspecialchars(
                                        $scholar['mail_id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MENTOR 1 -->

                                <td>

                                    <?= htmlspecialchars(
                                        $scholar['mentor_1'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MENTOR 2 -->

                                <td>

                                    <?= htmlspecialchars(
                                        $scholar['mentor_2'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">


                                        <a
                                            href="editphd.php?id=<?= (int)$scholar['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$scholar['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $scholar['student_name'],
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
                id="phdNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No Ph.D. scholars found.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="phdPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No Ph.D. scholars found.
                </p>


                <a
                    href="addphd.php"
                    class="btn-add"
                >
                    + Add Ph.D. Scholar
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
            Delete Ph.D. Scholar?
        </h3>


        <p>

            Are you sure you want to delete
            <strong id="deleteStudentName"></strong>?

        </p>


        <p class="delete-warning">

            This action cannot be undone.

        </p>


        <form
            method="POST"
            action="deletephd.php"
        >


            <input
                type="hidden"
                name="id"
                id="deleteStudentId"
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
        'deleteStudentId'
    ).value = id;


    document.getElementById(
        'deleteStudentName'
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
| CLOSE MODAL WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/

document.getElementById(
    'deleteModal'
).addEventListener(
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
                '#phdTableBody .phd-row'
            )
        );


        const searchInput =
            document.getElementById(
                'phdSearch'
            );


        const pagination =
            document.getElementById(
                'phdPagination'
            );


        const noResults =
            document.getElementById(
                'phdNoResults'
            );


        const searchCount =
            document.getElementById(
                'phdSearchCount'
            );


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
                (currentPage - 1) *
                rowsPerPage;


            const end =
                start +
                rowsPerPage;


            const currentRows =
                filteredRows.slice(
                    start,
                    end
                );


            currentRows.forEach(
                function (row)
                {

                    row.style.display =
                        'table-row';

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

                noResults.style.display =
                    'block';

            }
            else
            {

                searchCount.textContent =
                    filteredRows.length +
                    (
                        filteredRows.length === 1
                            ? ' scholar'
                            : ' scholars'
                    );


                noResults.style.display =
                    'none';

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
                    filteredRows.length /
                    rowsPerPage
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
                document.createElement(
                    'button'
                );


            previous.type =
                'button';


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


            pagination.appendChild(
                previous
            );


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
                    function ()
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


            /*
            |--------------------------------------------------------------------------
            | NEXT
            |--------------------------------------------------------------------------
            */

            const next =
                document.createElement(
                    'button'
                );


            next.type =
                'button';


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


            pagination.appendChild(
                next
            );

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
                                .includes(
                                    searchTerm
                                );

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