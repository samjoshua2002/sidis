<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

/*
|--------------------------------------------------------------------------
| FETCH HOW TO APPLY CARDS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        title,
        image,
        link_type,
        link_url,
        attachment,
        display_order,
        status
    FROM how_to_apply
    ORDER BY display_order ASC, id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            <h2>How to Apply Cards</h2>

            <p>
                Manage the How to Apply cards, images, external links and downloadable attachments.
            </p>

        </div>


        <div>

            <a
                href="add_how_to_apply.php"
                class="btn-add"
            >
                + Add Card
            </a>

        </div>

    </div>


    <!-- SUCCESS / ERROR MESSAGES -->

    <?php if (isset($_GET['success'])): ?>

        <?php if ($_GET['success'] === 'added'): ?>

            <div class="alert alert-success">
                Card added successfully.
            </div>

        <?php elseif ($_GET['success'] === 'updated'): ?>

            <div class="alert alert-success">
                Card updated successfully.
            </div>

        <?php elseif ($_GET['success'] === 'deleted'): ?>

            <div class="alert alert-success">
                Card deleted successfully.
            </div>

        <?php elseif ($_GET['success'] === 'delete_error'): ?>

            <div class="alert alert-danger">
                Unable to delete the card. Please try again.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <!-- CARDS TABLE -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>How to Apply Items</h3>

                <p>
                    Cards appear 4 per row on desktop under the "How to apply" admissions page.
                </p>

            </div>

        </div>


        <?php if (!empty($cards)): ?>


            <!-- SEARCH TOOLBAR -->

            <div class="table-toolbar">

                <div class="table-search">

                    <input
                        type="text"
                        id="cardsSearch"
                        class="table-search-input"
                        placeholder="Search cards..."
                        autocomplete="off"
                    >

                    <span class="table-search-icon">
                        &#128269;
                    </span>

                </div>

                <div
                    id="cardsSearchCount"
                    class="search-result-count"
                ></div>

            </div>


            <!-- TABLE -->

            <div class="table-wrapper">

                <table
                    class="data-table"
                    id="cardsTable"
                >

                    <thead>

                        <tr>

                            <th style="width: 100px;">Image</th>

                            <th>Title</th>

                            <th style="width: 120px;">Type</th>

                            <th>Link / Attachment Destination</th>

                            <th style="width: 80px; text-align: center;">Order</th>

                            <th style="width: 100px; text-align: center;">Status</th>

                            <th style="width: 140px; text-align: center;">Actions</th>

                        </tr>

                    </thead>


                    <tbody id="cardsTableBody">


                        <?php foreach ($cards as $card): ?>


                            <tr class="card-row">


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($card['image'])): ?>

                                        <img
                                            src="../images/<?= htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            style="width: 75px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb;"
                                        >

                                    <?php else: ?>

                                        <span style="color: #9CA3AF; font-size: 12px;">
                                            No Image
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TITLE -->

                                <td>

                                    <strong style="color: #184C74; font-size: 15px;">

                                        <?= htmlspecialchars(
                                            $card['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <?php if ($card['link_type'] === 'attachment'): ?>

                                        <span style="display: inline-block; padding: 4px 8px; background-color: #EEF2FF; color: #4338CA; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <i class="fa-solid fa-paperclip"></i> Attachment
                                        </span>

                                    <?php else: ?>

                                        <span style="display: inline-block; padding: 4px 8px; background-color: #ECFDF5; color: #047857; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <i class="fa-solid fa-link"></i> Web Link
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- DESTINATION -->

                                <td>

                                    <?php if ($card['link_type'] === 'attachment' && !empty($card['attachment'])): ?>

                                        <a
                                            href="../images/attachments/<?= htmlspecialchars($card['attachment'], ENT_QUOTES, 'UTF-8') ?>"
                                            target="_blank"
                                            style="color: #184C74; text-decoration: underline; font-size: 13px; display: inline-flex; align-items: center; gap: 5px;"
                                        >
                                            <i class="fa-solid fa-file-pdf" style="color: #DC2626;"></i>
                                            <?= htmlspecialchars($card['attachment'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>

                                    <?php elseif (!empty($card['link_url'])): ?>

                                        <a
                                            href="<?= htmlspecialchars($card['link_url'], ENT_QUOTES, 'UTF-8') ?>"
                                            target="_blank"
                                            style="color: #184C74; text-decoration: underline; font-size: 13px;"
                                        >
                                            <?= htmlspecialchars($card['link_url'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>

                                    <?php else: ?>

                                        <span style="color: #9CA3AF; font-size: 13px;">-</span>

                                    <?php endif; ?>

                                </td>


                                <!-- ORDER -->

                                <td style="text-align: center; color: #6B7280; font-weight: 600;">

                                    <?= (int)$card['display_order'] ?>

                                </td>


                                <!-- STATUS -->

                                <td style="text-align: center;">

                                    <?php if (!empty($card['status'])): ?>

                                        <span style="display: inline-block; padding: 3px 9px; background-color: #DEF7EC; color: #03543F; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span style="display: inline-block; padding: 3px 9px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="table-actions" style="justify-content: center;">


                                        <a
                                            href="edit_how_to_apply.php?id=<?= (int)$card['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>


                                        <button
                                            type="button"
                                            class="btn-delete"
                                            onclick="openDeleteModal(
                                                <?= (int)$card['id'] ?>,
                                                <?= htmlspecialchars(
                                                    json_encode(
                                                        $card['title'],
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
                id="cardsNoResults"
                class="table-empty"
                style="display:none;"
            >

                <p>
                    No cards found matching your search.
                </p>

            </div>


            <!-- PAGINATION -->

            <div
                id="cardsPagination"
                class="table-pagination"
            ></div>


        <?php else: ?>


            <div class="table-empty">

                <p>
                    No How to Apply cards found.
                </p>


                <a
                    href="add_how_to_apply.php"
                    class="btn-add"
                >
                    + Add Card
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
            Delete How to Apply Card?
        </h3>


        <p>

            Are you sure you want to delete
            <br>
            <strong id="deleteCardTitle"></strong>?

        </p>


        <p class="delete-warning">

            This action cannot be undone.

        </p>


        <form
            method="POST"
            action="delete_how_to_apply.php"
        >


            <input
                type="hidden"
                name="id"
                id="deleteCardId"
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

function openDeleteModal(id, title)
{

    document.getElementById('deleteCardId').value = id;
    document.getElementById('deleteCardTitle').textContent = title;
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

document.getElementById('deleteModal').addEventListener('click', function (event) {

    if (event.target === this) {
        closeDeleteModal();
    }

});


/*
|--------------------------------------------------------------------------
| ESCAPE KEY
|--------------------------------------------------------------------------
*/

document.addEventListener('keydown', function (event) {

    if (event.key === 'Escape') {
        closeDeleteModal();
    }

});


/*
|--------------------------------------------------------------------------
| SEARCH + PAGINATION
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {


    const rows = Array.from(
        document.querySelectorAll('#cardsTableBody .card-row')
    );

    const searchInput = document.getElementById('cardsSearch');
    const pagination = document.getElementById('cardsPagination');
    const noResults = document.getElementById('cardsNoResults');
    const searchCount = document.getElementById('cardsSearchCount');

    const rowsPerPage = 10;
    let currentPage = 1;
    let filteredRows = [...rows];


    function applySearch() {

        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

        filteredRows = rows.filter(function (row) {
            return query === '' || row.innerText.toLowerCase().includes(query);
        });

        currentPage = 1;
        displayTable();

    }


    function displayTable() {

        rows.forEach(function (row) {
            row.style.display = 'none';
        });

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const currentRows = filteredRows.slice(start, end);

        currentRows.forEach(function (row) {
            row.style.display = 'table-row';
        });

        if (filteredRows.length === 0) {
            if (searchCount) searchCount.textContent = '';
            if (noResults) noResults.style.display = 'block';
        } else {
            if (searchCount) {
                searchCount.textContent = filteredRows.length + (filteredRows.length === 1 ? ' card' : ' cards');
            }
            if (noResults) noResults.style.display = 'none';
        }

        createPagination();

    }


    function createPagination() {

        if (!pagination) return;
        pagination.innerHTML = '';

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (totalPages <= 1) return;

        /* PREV */
        const prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'pagination-btn';
        prev.innerHTML = '&#10094;';
        prev.disabled = currentPage === 1;
        prev.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                displayTable();
            }
        });
        pagination.appendChild(prev);

        /* PAGES */
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
            btn.textContent = i;
            btn.addEventListener('click', function () {
                currentPage = i;
                displayTable();
            });
            pagination.appendChild(btn);
        }

        /* NEXT */
        const next = document.createElement('button');
        next.type = 'button';
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


    if (searchInput) {
        searchInput.addEventListener('input', applySearch);
    }

    displayTable();

});

</script>


<?php include 'footer.php'; ?>
