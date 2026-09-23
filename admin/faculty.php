<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH FACULTY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        image,
        department,
        designation,
        email,
        personal_page,
        cluster_id,
        status,
        top_order
    FROM faculty
    ORDER BY top_order DESC, name ASC, id ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute();


$faculty =
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

            <h2>Faculty</h2>

            <p>
                Manage faculty members displayed on the website.
            </p>

        </div>


        <div>

            <a
                href="addfaculty.php"
                class="btn-add"
            >
                + Add Faculty
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>


        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Faculty member added successfully.
            </div>


        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Faculty member updated successfully.
            </div>


        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Faculty member deleted successfully.
            </div>


        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">
                Unable to delete the faculty member. Please try again.
            </div>

        <?php endif; ?>


    <?php endif; ?>


    <!-- FACULTY CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Faculty Members</h3>

                <p>
                    View, edit or delete faculty members.
                </p>

            </div>

        </div>


        <?php if (!empty($faculty)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="facultySearch"
                        class="table-search-input"
                        placeholder="Search faculty..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>


                <div
                    id="facultySearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="facultyTable"
                >

                    <thead>

                        <tr>

                            <th>Image</th>

                            <th>Name</th>

                            <th>Department</th>

                            <th>Designation</th>

                            <th>Type</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="facultyTableBody">


                        <?php foreach ($faculty as $member): ?>


                            <tr class="faculty-row">


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($member['image'])): ?>

                                        <img
                                            src="../images/faculty/<?= htmlspecialchars(
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


                                <!-- DEPARTMENT -->

                                <td>

                                    <?= htmlspecialchars(
                                        $member['department'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- DESIGNATION -->

                                <td>

                                    <?= htmlspecialchars(
                                        $member['designation'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <?php if ((int)$member['top_order'] === 1): ?>

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
                                            Top Faculty
                                        </span>

                                    <?php else: ?>

                                        <span
                                            style="
                                                display:inline-block;
                                                padding:5px 10px;
                                                border-radius:4px;
                                                background:#e2e3e5;
                                                color:#383d41;
                                                font-size:13px;
                                            "
                                        >
                                            Cluster Faculty
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">


                                        <a
                                            href="editfaculty.php?id=<?= (int)$member['id'] ?>"
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
                id="facultyNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No faculty members found.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="facultyPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No faculty members found.
                </p>


                <a
                    href="addfaculty.php"
                    class="btn-add"
                >
                    + Add Faculty
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
            Delete Faculty Member?
        </h3>


        <p>
            Are you sure you want to delete this faculty member?
        </p>


        <p>
            <strong id="deleteFacultyName"></strong>
        </p>


        <p class="delete-warning">
            This action cannot be undone.
        </p>


        <form
            method="POST"
            action="deletefaculty.php"
        >

            <input
                type="hidden"
                name="id"
                id="deleteFacultyId"
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
        'deleteFacultyId'
    ).value = id;


    document.getElementById(
        'deleteFacultyName'
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
                '#facultyTableBody .faculty-row'
            )
        );


        const searchInput =
            document.getElementById(
                'facultySearch'
            );


        const pagination =
            document.getElementById(
                'facultyPagination'
            );


        const noResults =
            document.getElementById(
                'facultyNoResults'
            );


        const searchCount =
            document.getElementById(
                'facultySearchCount'
            );


        /*
        |--------------------------------------------------------------------------
        | 10 ROWS PER PAGE
        |--------------------------------------------------------------------------
        */

        const rowsPerPage = 10;


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
                            ? ' faculty member'
                            : ' faculty members'
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
            | PREVIOUS BUTTON
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


            /*
            |--------------------------------------------------------------------------
            | PAGE NUMBERS
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | 1 2 3 4 5 6 7 8 9 10 ... 30
            |
            | Middle:
            |
            | 1 ... 13 14 15 16 17 ... 30
            |
            | End:
            |
            | 1 ... 21 22 23 24 25 26 27 28 29 30
            |
            |--------------------------------------------------------------------------
            */

            const pages =
                getPaginationPages(
                    totalPages,
                    currentPage
                );


            pages.forEach(
                function(page)
                {

                    /*
                    |--------------------------------------------------------------------------
                    | ELLIPSIS
                    |--------------------------------------------------------------------------
                    */

                    if (page === '...')
                    {

                        const ellipsis =
                            document.createElement(
                                'span'
                            );


                        ellipsis.className =
                            'pagination-btn';


                        ellipsis.textContent =
                            '...';


                        ellipsis.style.cursor =
                            'default';


                        ellipsis.style.pointerEvents =
                            'none';


                        pagination.appendChild(
                            ellipsis
                        );


                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PAGE BUTTON
                    |--------------------------------------------------------------------------
                    */

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
            );


            /*
            |--------------------------------------------------------------------------
            | NEXT BUTTON
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


        /*
        |--------------------------------------------------------------------------
        | CREATE COMPACT PAGE RANGE
        |--------------------------------------------------------------------------
        */

        function getPaginationPages(
            totalPages,
            currentPage
        )
        {

            const pages = [];


            /*
            |--------------------------------------------------------------------------
            | 10 OR FEWER PAGES
            |--------------------------------------------------------------------------
            */

            if (totalPages <= 10)
            {

                for (
                    let page = 1;
                    page <= totalPages;
                    page++
                )
                {

                    pages.push(page);

                }


                return pages;

            }


            /*
            |--------------------------------------------------------------------------
            | FIRST PAGE
            |--------------------------------------------------------------------------
            */

            pages.push(1);


            /*
            |--------------------------------------------------------------------------
            | CURRENT PAGE IS AT THE BEGINNING
            |--------------------------------------------------------------------------
            |
            | 1 2 3 4 5 6 7 8 9 10 ... 30
            |
            |--------------------------------------------------------------------------
            */

            if (currentPage <= 6)
            {

                for (
                    let page = 2;
                    page <= 10;
                    page++
                )
                {

                    pages.push(page);

                }


                pages.push('...');

                pages.push(totalPages);


                return pages;

            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT PAGE IS AT THE END
            |--------------------------------------------------------------------------
            |
            | 1 ... 21 22 23 24 25 26 27 28 29 30
            |
            |--------------------------------------------------------------------------
            */

            if (
                currentPage >=
                totalPages - 5
            )
            {

                pages.push('...');


                for (
                    let page =
                        totalPages - 8;
                    page <= totalPages;
                    page++
                )
                {

                    pages.push(page);

                }


                return pages;

            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT PAGE IS IN THE MIDDLE
            |--------------------------------------------------------------------------
            |
            | 1 ... 13 14 15 16 17 ... 30
            |
            |--------------------------------------------------------------------------
            */

            pages.push('...');


            pages.push(
                currentPage - 2
            );


            pages.push(
                currentPage - 1
            );


            pages.push(
                currentPage
            );


            pages.push(
                currentPage + 1
            );


            pages.push(
                currentPage + 2
            );


            pages.push('...');


            pages.push(totalPages);


            return pages;

        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | INITIAL LOAD
        |--------------------------------------------------------------------------
        */

        displayTable();

    }
);

</script>


<?php include 'footer.php'; ?>