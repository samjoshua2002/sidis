<?php
/*
|--------------------------------------------------------------------------
| Rename a Title or a Table Heading (all rows under it, order unchanged)
|--------------------------------------------------------------------------
| POST:
|   type = title | heading
|   id   = any row id inside that title / heading
|   new_name  -> chunked Base64 (new_name_chunk_count, new_name_chunk_0..N)
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/recruitment_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: faculty_recruitment.php');
    exit;
}

$type = $_POST['type'] ?? '';
$id   = $_POST['id'] ?? '';

if (!in_array($type, ['title', 'heading'], true) || !ctype_digit((string)$id)) {
    header('Location: faculty_recruitment.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Find the title / heading through the reference row
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("SELECT page, title, heading FROM recruitment WHERE id = ? LIMIT 1");
$stmt->execute([(int)$id]);
$ref = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ref || !isset(RECRUITMENT_PAGES[$ref['page']])) {
    header('Location: faculty_recruitment.php');
    exit;
}

$back = function ($query) use ($ref) {
    header('Location: ' . recruitmentListUrl($ref['page'], $query));
    exit;
};


/*
|--------------------------------------------------------------------------
| Validate new name
|--------------------------------------------------------------------------
*/

$newName = reconstructChunks('new_name');
$oldName = $type === 'title' ? $ref['title'] : $ref['heading'];

if ($newName === '' || mb_strlen($newName) > 255) {
    $back('success=rename_invalid');
}

if ($newName === $oldName) {
    $back('success=renamed');
}


/*
|--------------------------------------------------------------------------
| Rename (refuse if another title / heading already has that name)
|--------------------------------------------------------------------------
*/

try {

    if ($type === 'title') {

        $check = $pdo->prepare("SELECT COUNT(*) FROM recruitment WHERE page = ? AND title = ? AND title <> ?");
        $check->execute([$ref['page'], $newName, $oldName]);
        if ((int)$check->fetchColumn() > 0) {
            $back('success=rename_exists');
        }

        $upd = $pdo->prepare("UPDATE recruitment SET title = ? WHERE page = ? AND title = ?");
        $upd->execute([$newName, $ref['page'], $oldName]);

    } else {

        $check = $pdo->prepare("SELECT COUNT(*) FROM recruitment WHERE page = ? AND title = ? AND heading = ? AND heading <> ?");
        $check->execute([$ref['page'], $ref['title'], $newName, $oldName]);
        if ((int)$check->fetchColumn() > 0) {
            $back('success=rename_exists');
        }

        $upd = $pdo->prepare("UPDATE recruitment SET heading = ? WHERE page = ? AND title = ? AND heading = ?");
        $upd->execute([$newName, $ref['page'], $ref['title'], $oldName]);

    }

    $back('success=renamed');

} catch (PDOException $e) {

    error_log('Recruitment rename error: ' . $e->getMessage());
    $back('success=rename_error');

}
