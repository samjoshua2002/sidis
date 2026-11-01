<?php
/*
|--------------------------------------------------------------------------
| BOOTSTRAP – process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/recruitment_helpers.php';

$error = '';

/* Page comes from the menu: Join Us > Faculty Recruitment / Careers */
$lockedPage = $_GET['page'] ?? 'faculty';
if (!isset(RECRUITMENT_PAGES[$lockedPage])) {
    $lockedPage = 'faculty';
}

$form = [
    'page'          => $lockedPage,
    'title'         => '',
    'new_title'     => '',
    'heading'       => '',
    'new_heading'   => '',
    'label'         => '',
    'row_type'      => 'text',
    'content'       => '',
    'link_url'      => '',
    'link_text'     => '',
    'status'        => 1,
];


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST['page'] = $lockedPage; // never trust a posted page

    try {
        [$data, $error] = recruitmentReadPost($pdo);
    } catch (PDOException $e) {
        error_log('DB Error: ' . $e->getMessage());
        $data  = $form;
        $error = 'Unable to read the form. Please try again.';
    }

    $form = array_merge($form, array_intersect_key($data, $form));

    if ($error === '') {

        try {

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO recruitment
                    (page, title, title_order, heading, heading_order, label, row_type, content, link_url, link_text, display_order, status)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['page'],
                $data['final_title'],
                $data['title_order'],
                $data['final_heading'],
                $data['heading_order'],
                $data['label'],
                $data['row_type'],
                $data['content'],
                $data['link_url'],
                $data['link_text'],
                $data['display_order'],
                $data['status'],
            ]);

            recruitmentSyncOrders($pdo, $data);

            $pdo->commit();

            header('Location: ' . recruitmentListUrl($lockedPage, 'success=added'));
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('DB Error: ' . $e->getMessage());
            $error = 'Unable to save the entry. Please try again.';

        }
    }
}

$structure   = recruitmentStructure($pdo, $lockedPage);
$formAction  = 'addrecruitment.php?page=' . urlencode($lockedPage);
$navActivePage = RECRUITMENT_LIST_FILES[$lockedPage];
$submitLabel = 'Save Entry';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h1>Add <?= htmlspecialchars(RECRUITMENT_PAGES[$lockedPage], ENT_QUOTES, 'UTF-8') ?> Entry</h1>
            <p>Add a row under a heading on the <?= htmlspecialchars(RECRUITMENT_PAGES[$lockedPage], ENT_QUOTES, 'UTF-8') ?> page.</p>
        </div>

        <a href="<?= htmlspecialchars(recruitmentListUrl($lockedPage), ENT_QUOTES, 'UTF-8') ?>" class="btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to <?= htmlspecialchars(RECRUITMENT_PAGES[$lockedPage], ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>New Entry</h3>
                <p>Choose a title, then a table heading, then whether the row is Text or a Button.</p>
            </div>
        </div>

        <div class="card-body">
            <?php include __DIR__ . '/recruitment_form.php'; ?>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
