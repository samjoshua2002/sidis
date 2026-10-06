<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| FETCH IMPORTANT DATES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        schedule_name,
        description,
        date_schedule
    FROM important_dates
    ORDER BY id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$important_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| GET DISTINCT SCHEDULES FOR FILTER
|--------------------------------------------------------------------------
*/

$schedules = [];
foreach ($important_dates as $row) {
    $sch = trim($row['schedule_name'] ?? '');
    if ($sch !== '' && !in_array($sch, $schedules, true)) {
        $schedules[] = $sch;
    }
}

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

            <h2>Important Dates</h2>

            <p>
                Manage admission schedules, important dates and milestones.
            </p>

        </div>


        <div>

            <a
                href="add_important_date.php"
                class="btn-add"
            >
                + Add Important Date
            </a>

        </div>

    </div>


    <!-- SUCCESS / ERROR MESSAGES -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Important date added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Important date updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Important date deleted successfully.
            </div>

        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">
                Unable to delete the important date. Please try again.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- IMPORTANT DATES CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Important Dates</h3>

                <p>
                    View, filter, edit or delete admission schedule records.
                </p>

            </div>

        </div>


        <?php if (!empty($important_dates)): ?>


            <!-- TOOLBAR: SEARCH & FILTER -->

            <div class="table-toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;">

                <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">

                    <!-- SEARCH -->
                    <div class="table-search" style="position: relative;">

                        <input
                            type="text"
                            id="datesSearch"
                            class="table-search-input"
                            placeholder="Search description or date..."
                            autocomplete="off"
                        >

                        <span class="table-search-icon">
                            &#128269;
                        </span>

                    </div>

                    <!-- SCHEDULE FILTER -->
                    <div>
                        <select id="scheduleFilter" class="form-control" style="height: 38px; min-width: 220px; font-size: 13px;">
                            <option value="">All Admission Schedules</option>
                            <?php foreach ($schedules as $sch): ?>
                                <option value="<?= htmlspecialchars($sch, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($sch, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div
                    id="datesSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="datesTable"
                >

                    <thead>

                        <tr>

                            <th style="min-width: 180px;">Admission Schedule</th>

                            <th>Description</th>

                            <th>Date / Schedule</th>

                            <th style="width: 140px; text-align: center;">Actions</th>

                        </tr>

                    </thead>


                    <tbody id="datesTableBody">


                        <?php foreach ($important_dates as $item): ?>


                            <tr class="date-row" data-schedule="<?= htmlspecialchars($item['schedule_name'], ENT_QUOTES, 'UTF-8') ?>">


                                <!-- ADMISSION SCHEDULE -->

                                <td>

                                    <strong style="color: #184C74;">

                                        <?= htmlspecialchars(
                                            $item['schedule_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- DESCRIPTION -->

                                <td>

                                    <?= htmlspecialchars(
                                        $item['description'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- DATE / SCHEDULE -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $item['date_schedule'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions" style="justify-content: center;">


                                        <a
                                            href="edit_important_date.php?id=<?= (int)$item['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$item['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $item['description'],
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
                id="datesNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No important dates found matching your search.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="datesPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No important dates found.
                </p>


                <a
                    href="add_important_date.php"
                    class="btn-add"
                >
                    + Add Important Date
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
            Delete Important Date?
        </h3>


        <p>

            Are you sure you want to delete:
            <br>
            <strong id="deleteItemDescription"></strong>?

        </p>


        <p class="delete-warning">

            This action cannot be undone.

        </p>


        <form
            method="POST"
            action="delete_important_date.php"
        >


            <input
                type="hidden"
                name="id"
                id="deleteItemId"
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

function openDeleteModal(id, desc)
{

    document.getElementById(
        'deleteItemId'
    ).value = id;


    document.getElementById(
        'deleteItemDescription'
    ).textContent = desc;


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
| SEARCH + FILTER + PAGINATION
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function ()
    {


        const rows = Array.from(
            document.querySelectorAll(
                '#datesTableBody .date-row'
            )
        );


        const searchInput =
            document.getElementById(
                'datesSearch'
            );

        const scheduleFilter =
            document.getElementById(
                'scheduleFilter'
            );


        const pagination =
            document.getElementById(
                'datesPagination'
            );


        const noResults =
            document.getElementById(
                'datesNoResults'
            );


        const searchCount =
            document.getElementById(
                'datesSearchCount'
            );


        const rowsPerPage = 10;


        let currentPage = 1;


        let filteredRows = [...rows];


        /*
        |--------------------------------------------------------------------------
        | FILTER ROWS
        |--------------------------------------------------------------------------
        */

        function applyFilters()
        {

            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const selectedSchedule = scheduleFilter ? scheduleFilter.value.trim() : '';

            filteredRows = rows.filter(function (row) {

                const rowSchedule = (row.getAttribute('data-schedule') || '').trim();
                const rowText = row.innerText.toLowerCase();

                const matchesQuery = query === '' || rowText.includes(query);
                const matchesSchedule = selectedSchedule === '' || rowSchedule === selectedSchedule;

                return matchesQuery && matchesSchedule;

            });

            currentPage = 1;
            displayTable();

        }


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

                if (searchCount) searchCount.textContent = '';

                if (noResults) noResults.style.display = 'block';

            }
            else
            {

                if (searchCount) {
                    searchCount.textContent =
                        filteredRows.length +
                        (
                            filteredRows.length === 1
                                ? ' record'
                                : ' records'
                        );
                }

                if (noResults) noResults.style.display = 'none';

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

            if (!pagination) return;

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


            /* PREVIOUS BUTTON */
            const previous =
                document.createElement(
                    'button'
                );

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


            /* PAGE NUMBERS */
            for (let i = 1; i <= totalPages; i++)
            {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
                btn.textContent = i;

                btn.addEventListener(
                    'click',
                    function ()
                    {
                        currentPage = i;
                        displayTable();
                    }
                );

                pagination.appendChild(btn);
            }


            /* NEXT BUTTON */
            const next =
                document.createElement(
                    'button'
                );

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
        | EVENT LISTENERS
        |--------------------------------------------------------------------------
        */

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (scheduleFilter) {
            scheduleFilter.addEventListener('change', applyFilters);
        }


        displayTable();

    }
);

</script>


<?php include 'footer.php'; ?>
