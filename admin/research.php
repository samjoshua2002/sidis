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
    FROM featured_research
    ORDER BY id DESC
");

$stmt->execute();

$featuredResearch = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="dashboard-content">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Featured Research</h2>

            <p>
                Manage featured research displayed on the website.
            </p>

        </div>

        <div>

            <a href="addres.php" class="btn-add">
                + Add Featured Research
            </a>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Featured research added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Featured research updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Featured research deleted successfully.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- MAIN CARD -->

    <div class="dashboard-card">

        <div class="card-header">

            <div>

                <h3>Featured Research</h3>

                <p>
                    Add, edit or delete featured research.
                </p>

            </div>

        </div>


        <?php if (!empty($featuredResearch)): ?>


            <!-- SEARCH -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="tableSearch"
                        class="table-search-input"
                        placeholder="Search featured research..."
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


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="researchTable"
                >

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


                    <tbody id="researchTableBody">

                        <?php foreach ($featuredResearch as $research): ?>

                            <?php

                            $content =
                                $research['link'] ?? '';

                            $isAttachment = false;

                            if (
                                !empty($content) &&
                                preg_match(
                                    '/\.pdf$/i',
                                    $content
                                )
                            ) {

                                $isAttachment = true;

                            }

                            ?>


                            <tr class="table-data-row">


                                <!-- ID -->

                                <!-- <td>

                                    <span class="table-id">

                                        <?= (int)$research['id'] ?>

                                    </span>

                                </td> -->


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($research['image'])): ?>

                                        <img
                                            src="../images/<?= htmlspecialchars(
                                                $research['image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $research['title'],
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


                                <!-- TITLE -->

                                <td>

                                    <strong class="table-title">

                                        <?= htmlspecialchars(
                                            $research['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- CONTENT -->

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


                                <!-- STATUS -->

                                <td>

                                    <?php if ($research['status'] == 1): ?>

                                        <span class="status-badge status-active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge status-inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="editres.php?id=<?= (int)$research['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$research['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $research['title']
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
                id="noSearchResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No featured research matches your search.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="tablePagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No featured research found.
                </p>

                <a
                    href="addres.php"
                    class="btn-add"
                >
                    + Add Featured Research
                </a>

            </div>


        <?php endif; ?>

    </div>

</div>


<!-- DELETE MODAL -->

<div
    id="deleteModal"
    class="delete-modal"
>

    <div class="delete-modal-content">

        <div class="delete-modal-icon">
            !
        </div>

        <h3>
            Delete Featured Research?
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
            action="deleteres.php"
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

    document.getElementById(
        'deleteItemId'
    ).value = id;

    document.getElementById(
        'deleteItemTitle'
    ).textContent = title;

    document.getElementById(
        'deleteModal'
    ).classList.add('show');

}


function closeDeleteModal() {

    document.getElementById(
        'deleteModal'
    ).classList.remove('show');

}


document
    .getElementById('deleteModal')
    .addEventListener(
        'click',
        function (event) {

            if (event.target === this) {

                closeDeleteModal();

            }

        }
    );


document.addEventListener(
    'keydown',
    function (event) {

        if (event.key === 'Escape') {

            closeDeleteModal();

        }

    }
);


/* =========================================================
   SEARCH + PAGINATION
   ========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const rows = Array.from(
            document.querySelectorAll(
                '#researchTableBody .table-data-row'
            )
        );

        const searchInput =
            document.getElementById(
                'tableSearch'
            );

        const pagination =
            document.getElementById(
                'tablePagination'
            );

        const noSearchResults =
            document.getElementById(
                'noSearchResults'
            );

        const resultCount =
            document.getElementById(
                'searchResultCount'
            );


        const rowsPerPage = 5;

        let currentPage = 1;

        let filteredRows = [...rows];


        function displayTable() {

            rows.forEach(function (row) {

                row.style.display = 'none';

            });


            const start =
                (currentPage - 1) *
                rowsPerPage;

            const end =
                start + rowsPerPage;


            filteredRows
                .slice(start, end)
                .forEach(function (row) {

                    row.style.display =
                        'table-row';

                });


            if (
                searchInput.value.trim() !== ''
            ) {

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


            if (filteredRows.length === 0) {

                noSearchResults.style.display =
                    'block';

            } else {

                noSearchResults.style.display =
                    'none';

            }


            createPagination();

        }


        function createPagination() {

            pagination.innerHTML = '';


            const totalPages =
                Math.ceil(
                    filteredRows.length /
                    rowsPerPage
                );


            if (totalPages <= 1) {

                return;

            }


            const previousButton =
                document.createElement('button');

            previousButton.type = 'button';

            previousButton.className =
                'pagination-btn pagination-prev';

            previousButton.innerHTML =
                '&#10094;';

            previousButton.disabled =
                currentPage === 1;


            previousButton.addEventListener(
                'click',
                function () {

                    if (currentPage > 1) {

                        currentPage--;

                        displayTable();

                    }

                }
            );


            pagination.appendChild(
                previousButton
            );


            for (
                let page = 1;
                page <= totalPages;
                page++
            ) {

                const pageButton =
                    document.createElement(
                        'button'
                    );

                pageButton.type = 'button';

                pageButton.className =
                    'pagination-btn';


                if (page === currentPage) {

                    pageButton.classList.add(
                        'active'
                    );

                }


                pageButton.textContent =
                    page;


                pageButton.addEventListener(
                    'click',
                    function () {

                        currentPage = page;

                        displayTable();

                    }
                );


                pagination.appendChild(
                    pageButton
                );

            }


            const nextButton =
                document.createElement('button');

            nextButton.type = 'button';

            nextButton.className =
                'pagination-btn pagination-next';

            nextButton.innerHTML =
                '&#10095;';

            nextButton.disabled =
                currentPage === totalPages;


            nextButton.addEventListener(
                'click',
                function () {

                    if (
                        currentPage <
                        totalPages
                    ) {

                        currentPage++;

                        displayTable();

                    }

                }
            );


            pagination.appendChild(
                nextButton
            );

        }


        searchInput.addEventListener(
            'input',
            function () {

                const searchTerm =
                    this.value
                        .toLowerCase()
                        .trim();


                filteredRows =
                    rows.filter(
                        function (row) {

                            return row
                                .textContent
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