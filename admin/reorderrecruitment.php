<?php
/*
|--------------------------------------------------------------------------
| Save drag & drop order (Careers > Faculty / Postdoc / Staff Recruitment)
|--------------------------------------------------------------------------
| POST (form-encoded, integers only):
|   page   = faculty | careers
|   type   = titles | headings | rows
|   ids[]  = row ids in the new order
|            titles   -> one row id from each title block    (any row of that title)
|            headings -> one row id from each heading group  (any row of that heading)
|            rows     -> every row id of one heading, in order
| Returns JSON {ok: true} or {ok: false, error: "..."}.
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/recruitment_helpers.php';

header('Content-Type: application/json; charset=utf-8');

function reorderFail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reorderFail('POST only.', 405);
}

$page = $_POST['page'] ?? '';
$type = $_POST['type'] ?? '';
$ids  = $_POST['ids'] ?? [];

if (!isset(RECRUITMENT_PAGES[$page])) {
    reorderFail('Invalid page.');
}
if (!in_array($type, ['titles', 'headings', 'rows'], true)) {
    reorderFail('Invalid type.');
}
if (!is_array($ids) || count($ids) === 0 || count($ids) > 1000) {
    reorderFail('No items.');
}

$clean = [];
foreach ($ids as $id) {
    if (!ctype_digit((string)$id)) {
        reorderFail('Invalid id.');
    }
    $clean[] = (int)$id;
}
if (count(array_unique($clean)) !== count($clean)) {
    reorderFail('Duplicate id.');
}

try {

    /* Load the referenced rows (must all belong to this page) */
    $placeholders = implode(',', array_fill(0, count($clean), '?'));
    $stmt = $pdo->prepare("SELECT id, title, heading FROM recruitment WHERE page = ? AND id IN ($placeholders)");
    $stmt->execute(array_merge([$page], $clean));

    $rows = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rows[(int)$r['id']] = $r;
    }
    if (count($rows) !== count($clean)) {
        reorderFail('Some items no longer exist. Please reload the page.', 409);
    }

    $pdo->beginTransaction();

    if ($type === 'titles') {

        $upd = $pdo->prepare("UPDATE recruitment SET title_order = ? WHERE page = ? AND title = ?");
        $seen = [];
        foreach ($clean as $i => $id) {
            $title = $rows[$id]['title'];
            if (isset($seen[$title])) {
                throw new RuntimeException('Same title sent twice.');
            }
            $seen[$title] = true;
            $upd->execute([$i + 1, $page, $title]);
        }

    } elseif ($type === 'headings') {

        $title = $rows[$clean[0]]['title'];
        $upd = $pdo->prepare("UPDATE recruitment SET heading_order = ? WHERE page = ? AND title = ? AND heading = ?");
        $seen = [];
        foreach ($clean as $i => $id) {
            if ($rows[$id]['title'] !== $title) {
                throw new RuntimeException('Headings must belong to one title.');
            }
            $heading = $rows[$id]['heading'];
            if (isset($seen[$heading])) {
                throw new RuntimeException('Same heading sent twice.');
            }
            $seen[$heading] = true;
            $upd->execute([$i + 1, $page, $title, $heading]);
        }

    } else { // rows

        $title   = $rows[$clean[0]]['title'];
        $heading = $rows[$clean[0]]['heading'];
        $upd = $pdo->prepare("UPDATE recruitment SET display_order = ? WHERE id = ? AND page = ?");
        foreach ($clean as $i => $id) {
            if ($rows[$id]['title'] !== $title || $rows[$id]['heading'] !== $heading) {
                throw new RuntimeException('Rows must belong to one heading.');
            }
            $upd->execute([$i + 1, $id, $page]);
        }

    }

    $pdo->commit();
    echo json_encode(['ok' => true]);

} catch (RuntimeException $e) {

    if ($pdo->inTransaction()) $pdo->rollBack();
    reorderFail($e->getMessage());

} catch (PDOException $e) {

    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Recruitment reorder error: ' . $e->getMessage());
    reorderFail('Unable to save the order. Please try again.', 500);

}
