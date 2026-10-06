<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: important_dates.php');
    exit;
}


$id = $_POST['id'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: important_dates.php');
    exit;
}

$id = (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK ITEM EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM important_dates
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

if (!$stmt->fetch()) {
    header('Location: important_dates.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

try {

    $delStmt = $pdo->prepare("
        DELETE FROM important_dates
        WHERE id = ?
    ");

    $delStmt->execute([$id]);

    header('Location: important_dates.php?success=deleted');
    exit;

} catch (PDOException $e) {

    header('Location: important_dates.php?success=delete_error');
    exit;

}
