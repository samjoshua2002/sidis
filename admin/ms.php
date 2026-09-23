<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH M.S. STUDENTS
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
    FROM ms
    ORDER BY id ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute();


$students =
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

            <h2>M.S. Students</h2>

            <p>
                Manage M.S. student details and batch information.
            </p>

        </div>


        <div>

            <a
                href="addms.php"
                class="btn-add"
            >
                + Add M.S. Student
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">

                M.S. student added successfully.

            </div>


        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">

                M.S. student updated successfully.

            </div>


        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">

                M.S. student deleted successfully.

            </div>


        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">

                Unable to delete the M.S. student. Please try again.

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- M.S. CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>M.S. Students</h3>

                <p>
                    View, edit or delete M.S. student records.
                </p>

            </div>

        </div>


        <?php if (!empty($students)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="msSearch"
                        class="table-search-input"
                        placeholder="Search M.S. students..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>


                <div
                    id="msSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="msTable"
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


                    <tbody id="msTableBody">


                        <?php foreach ($students as $student): ?>


                            <tr class="ms-row">


                                <!-- BATCH -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $student['batch'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- ROLL NUMBER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['roll_number'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- STUDENT NAME -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['student_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MAIL ID -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['mail_id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MENTOR 1 -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['mentor_1'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- MENTOR 2 -->

                                <td>

                                    <?= htmlspecialchars(
                                        $student['mentor_2'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">


                                        <a
                                            href="editms.php?id=<?= (int)$student['id'] ?>"
                                            class="btn-edit"
                                        >
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

            <div
                id="msNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No M.S. students found.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="msPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No M.S. students found.
                </p>


                <a
                    href="addms.php"
                    class="btn-add"
                >
                    + Add M.S. Student
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
            Delete M.S. Student?
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
            action="deletems.php"
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
                '#msTableBody .ms-row'
            )
        );


        const searchInput =
            document.getElementById(
                'msSearch'
            );


        const pagination =
            document.getElementById(
                'msPagination'
            );


        const noResults =
            document.getElementById(
                'msNoResults'
            );


        const searchCount =
            document.getElementById(
                'msSearchCount'
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
                            ? ' student'
                            : ' students'
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