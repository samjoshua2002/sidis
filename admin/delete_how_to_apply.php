<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: how_to_apply.php');
    exit;
}

$id = $_POST['id'] ?? '';

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: how_to_apply.php');
    exit;
}

$id = (int)$id;

/*
|--------------------------------------------------------------------------
| CHECK ITEM EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("SELECT * FROM how_to_apply WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: how_to_apply.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

try {

    $delStmt = $pdo->prepare("DELETE FROM how_to_apply WHERE id = ?");
    $delStmt->execute([$id]);

    header('Location: how_to_apply.php?success=deleted');
    exit;

} catch (PDOException $e) {

    header('Location: how_to_apply.php?success=delete_error');
    exit;

}
