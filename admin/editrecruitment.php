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


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: faculty_recruitment.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| FETCH ENTRY
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT id, page, title, title_order, heading, heading_order, label, row_type, content, link_url, link_text, display_order, status
        FROM recruitment
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);

    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        header('Location: faculty_recruitment.php');
        exit;
    }

} catch (PDOException $e) {
    header('Location: faculty_recruitment.php');
    exit;
}

$form = [
    'page'          => $entry['page'],
    'title'         => $entry['title'],
    'new_title'     => '',
    'heading'       => $entry['heading'],
    'new_heading'   => '',
    'label'         => $entry['label'],
    'row_type'      => $entry['row_type'],
    'content'       => (string)$entry['content'],
    'link_url'      => $entry['link_url'],
    'link_text'     => $entry['link_text'],
    'status'        => (int)$entry['status'],
];


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST['page'] = $entry['page']; // page stays the same; it is fixed by the menu

    try {
        [$data, $error] = recruitmentReadPost($pdo, $id);
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
                UPDATE recruitment
                SET
                    page = ?,
                    title = ?,
                    title_order = ?,
                    heading = ?,
                    heading_order = ?,
                    label = ?,
                    row_type = ?,
                    content = ?,
                    link_url = ?,
                    link_text = ?,
                    display_order = ?,
                    status = ?
                WHERE id = ?
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
                $id,
            ]);

            recruitmentSyncOrders($pdo, $data);

            $pdo->commit();

            header('Location: ' . recruitmentListUrl($entry['page'], 'success=updated'));
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('DB Error: ' . $e->getMessage());
            $error = 'Unable to update the entry. Please try again.';

        }
    }
}

$structure   = recruitmentStructure($pdo, $entry['page']);
$formAction  = 'editrecruitment.php?id=' . $id;
$navActivePage = RECRUITMENT_LIST_FILES[$entry['page']] ?? 'faculty_recruitment.php';
$submitLabel = 'Update Entry';


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
            <h1>Edit <?= htmlspecialchars(RECRUITMENT_PAGES[$entry['page']] ?? '', ENT_QUOTES, 'UTF-8') ?> Entry</h1>
            <p>Update the row details below.</p>
        </div>

        <a href="<?= htmlspecialchars(recruitmentListUrl($entry['page']), ENT_QUOTES, 'UTF-8') ?>" class="btn-add">
            <i class="fas fa-arrow-left"></i>
            Back to <?= htmlspecialchars(RECRUITMENT_PAGES[$entry['page']] ?? 'List', ENT_QUOTES, 'UTF-8') ?>
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
                <h3>Edit Entry</h3>
                <p>Move the row to another title/heading, change its type, or edit its content.</p>
            </div>
        </div>

        <div class="card-body">
            <?php include __DIR__ . '/recruitment_form.php'; ?>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
