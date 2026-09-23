<?php include 'header.php'; ?>

<?php

require_once '../config.php';

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        link,
        image,
        status
    FROM news_events
    ORDER BY id DESC
");

$stmt->execute();

$newsEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="dashboard-content">

    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <div class="page-header">

        <div>

            <h2>News & Events</h2>

            <p>
                Manage the latest news and events displayed on the website.
            </p>

        </div>

        <div>

            <a href="addnews.php" class="btn-add">
                + Add News / Event
            </a>

        </div>

    </div>


    <!-- =====================================================
         SUCCESS MESSAGE
         ===================================================== -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                News / Event added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                News / Event updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                News / Event deleted successfully.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- =====================================================
         NEWS CARD
         ===================================================== -->

    <div class="dashboard-card">

        <!-- CARD HEADER -->

        <div class="card-header">

            <div>

                <h3>News & Events</h3>

                <p>
                    Add, edit or delete news and event items.
                </p>

            </div>

        </div>


        <!-- =================================================
             SEARCH
             ================================================= -->

        <?php if (!empty($newsEvents)): ?>

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="tableSearch"
                        class="table-search-input"
                        placeholder="Search news & events..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>

                <div
                    id="searchResultCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- =================================================
                 TABLE
                 ================================================= -->

            <div class="table-wrapper">

                <table class="data-table" id="newsTable">

                    <thead>

                        <tr>

                            <!-- <th>ID</th> -->
                            <th>Image</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody id="newsTableBody">

                        <?php foreach ($newsEvents as $news): ?>

                            <?php

                            $content = $news['link'] ?? '';

                            /*
                            |--------------------------------------------------------------------------
                            | DETERMINE WHETHER CONTENT IS A PDF
                            |--------------------------------------------------------------------------
                            */

                            $isAttachment = false;

                            if (
                                !empty($content) &&
                                preg_match('/\.pdf$/i', $content)
                            ) {

                                $isAttachment = true;

                            }

                            ?>

                            <tr class="table-data-row">


                                <!-- =============================================
                                     ID
                                     ============================================= -->

                                <!-- <td>

                                    <span class="table-id">
                                        <?= (int)$news['id'] ?>
                                    </span>

                                </td> -->


                                <!-- =============================================
                                     IMAGE
                                     ============================================= -->

                                <td>

                                    <?php if (!empty($news['image'])): ?>

                                        <img
                                            src="../images/<?= htmlspecialchars(
                                                $news['image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $news['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            class="table-image"
                                        >

                                    <?php else: ?>

                                        <span class="table-muted">
                                            No Image
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =============================================
                                     TITLE
                                     ============================================= -->

                                <td>

                                    <strong class="table-title">

                                        <?= htmlspecialchars(
                                            $news['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- =============================================
                                     CONTENT
                                     ============================================= -->

                                <td>

                                    <?php if ($isAttachment): ?>

                                        <a
                                            href="../images/attachments/<?= htmlspecialchars(
                                                $content,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="table-link"
                                        >
                                            View PDF
                                        </a>

                                    <?php elseif (!empty($content)): ?>

                                        <a
                                            href="<?= htmlspecialchars(
                                                $content,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="table-link"
                                        >
                                            View Link
                                        </a>

                                    <?php else: ?>

                                        <span class="table-muted">
                                            No Content
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =============================================
                                     STATUS
                                     ============================================= -->

                                <td>

                                    <?php if ($news['status'] == 1): ?>

                                        <span class="status-badge status-active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge status-inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =============================================
                                     ACTIONS
                                     ============================================= -->

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editnews.php?id=<?= (int)$news['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$news['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode($news['title']),
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


            <!-- =================================================
                 NO SEARCH RESULTS
                 ================================================= -->

            <div
                id="noSearchResults"
                class="table-empty"
                style="display: none;"
            >

                <p>
                    No news or events match your search.
                </p>

            </div>


            <!-- =================================================
                 PAGINATION
                 ================================================= -->

            <div
                id="tablePagination"
                class="table-pagination"
            ></div>


        <?php else: ?>

            <!-- =================================================
                 EMPTY DATABASE
                 ================================================= -->

            <div class="table-empty">

                <p>
                    No news or events found.
                </p>

                <a href="addnews.php" class="btn-add">
                    + Add News / Event
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
            Delete News / Event?
        </h3>

        <p>
            Are you sure you want to delete
            <strong id="deleteItemTitle"></strong>?
        </p>

        <p class="delete-warning">
            This action cannot be undone.
        </p>


        <form
            method="POST"
            action="deletenews.php"
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

/* =========================================================
   DELETE MODAL
   ========================================================= */

function openDeleteModal(id, title) {

    document.getElementById('deleteItemId').value = id;

    document.getElementById('deleteItemTitle').textContent = title;

    document
        .getElementById('deleteModal')
        .classList.add('show');

}


function closeDeleteModal() {

    document
        .getElementById('deleteModal')
        .classList.remove('show');

}


/* Close when clicking outside */

document
    .getElementById('deleteModal')
    .addEventListener('click', function (event) {

        if (event.target === this) {

            closeDeleteModal();

        }

    });


/* Close with Escape */

document.addEventListener('keydown', function (event) {

    if (event.key === 'Escape') {

        closeDeleteModal();

    }

});


/* =========================================================
   TABLE SEARCH + PAGINATION
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    const rows = Array.from(
        document.querySelectorAll(
            '#newsTableBody .table-data-row'
        )
    );

    const searchInput =
        document.getElementById('tableSearch');

    const pagination =
        document.getElementById('tablePagination');

    const noSearchResults =
        document.getElementById('noSearchResults');

    const resultCount =
        document.getElementById('searchResultCount');


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    const rowsPerPage = 5;

    let currentPage = 1;

    let filteredRows = [...rows];


    /*
    |--------------------------------------------------------------------------
    | DISPLAY TABLE
    |--------------------------------------------------------------------------
    */

    function displayTable() {

        /*
        |--------------------------------------------------------------------------
        | HIDE ALL ROWS FIRST
        |--------------------------------------------------------------------------
        */

        rows.forEach(function (row) {

            row.style.display = 'none';

        });


        /*
        |--------------------------------------------------------------------------
        | CALCULATE START / END
        |--------------------------------------------------------------------------
        */

        const start =
            (currentPage - 1) * rowsPerPage;

        const end =
            start + rowsPerPage;


        /*
        |--------------------------------------------------------------------------
        | SHOW CURRENT PAGE
        |--------------------------------------------------------------------------
        */

        filteredRows
            .slice(start, end)
            .forEach(function (row) {

                row.style.display = 'table-row';

            });


        /*
        |--------------------------------------------------------------------------
        | SEARCH RESULT COUNT
        |--------------------------------------------------------------------------
        */

        if (searchInput.value.trim() !== '') {

            resultCount.textContent =
                filteredRows.length +
                (
                    filteredRows.length === 1
                        ? ' result'
                        : ' results'
                );

        } else {

            resultCount.textContent =
                rows.length +
                (
                    rows.length === 1
                        ? ' item'
                        : ' items'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY SEARCH RESULT
        |--------------------------------------------------------------------------
        */

        if (filteredRows.length === 0) {

            noSearchResults.style.display = 'block';

        } else {

            noSearchResults.style.display = 'none';

        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        createPagination();

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE PAGINATION
    |--------------------------------------------------------------------------
    */

    function createPagination() {

        pagination.innerHTML = '';


        const totalPages =
            Math.ceil(
                filteredRows.length / rowsPerPage
            );


        /*
        |--------------------------------------------------------------------------
        | NO PAGINATION NEEDED
        |--------------------------------------------------------------------------
        */

        if (totalPages <= 1) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS BUTTON
        |--------------------------------------------------------------------------
        */

        const previousButton =
            document.createElement('button');

        previousButton.type = 'button';

        previousButton.className =
            'pagination-btn pagination-prev';

        previousButton.innerHTML =
            '&#10094;';


        if (currentPage === 1) {

            previousButton.disabled = true;

        }


        previousButton.addEventListener(
            'click',
            function () {

                if (currentPage > 1) {

                    currentPage--;

                    displayTable();

                }

            }
        );


        pagination.appendChild(previousButton);


        /*
        |--------------------------------------------------------------------------
        | PAGE BUTTONS
        |--------------------------------------------------------------------------
        */

        for (
            let page = 1;
            page <= totalPages;
            page++
        ) {

            const pageButton =
                document.createElement('button');

            pageButton.type = 'button';

            pageButton.className =
                'pagination-btn';


            if (page === currentPage) {

                pageButton.classList.add('active');

            }


            pageButton.textContent = page;


            pageButton.addEventListener(
                'click',
                function () {

                    currentPage = page;

                    displayTable();

                }
            );


            pagination.appendChild(pageButton);

        }


        /*
        |--------------------------------------------------------------------------
        | NEXT BUTTON
        |--------------------------------------------------------------------------
        */

        const nextButton =
            document.createElement('button');

        nextButton.type = 'button';

        nextButton.className =
            'pagination-btn pagination-next';

        nextButton.innerHTML =
            '&#10095;';


        if (currentPage === totalPages) {

            nextButton.disabled = true;

        }


        nextButton.addEventListener(
            'click',
            function () {

                if (currentPage < totalPages) {

                    currentPage++;

                    displayTable();

                }

            }
        );


        pagination.appendChild(nextButton);

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener(
        'input',
        function () {

            const searchTerm =
                this.value
                    .toLowerCase()
                    .trim();


            filteredRows =
                rows.filter(function (row) {

                    return row.textContent
                        .toLowerCase()
                        .includes(searchTerm);

                });


            /*
            |--------------------------------------------------------------------------
            | ALWAYS RETURN TO PAGE 1 AFTER SEARCH
            |--------------------------------------------------------------------------
            */

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

});

</script>


<?php include 'footer.php'; ?>