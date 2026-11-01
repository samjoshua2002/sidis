<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/recruitment_helpers.php';


/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: faculty_recruitment.php');
    exit;
}

$id = $_POST['id'] ?? '';

if ($id === '' || !ctype_digit((string)$id)) {
    header('Location: faculty_recruitment.php');
    exit;
}

$id = (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK ENTRY EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("SELECT page FROM recruitment WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$page = $stmt->fetchColumn();

if ($page === false) {
    header('Location: faculty_recruitment.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("DELETE FROM recruitment WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ' . recruitmentListUrl($page, 'success=deleted'));
    exit;

} catch (PDOException $e) {

    header('Location: ' . recruitmentListUrl($page, 'success=delete_error'));
    exit;

}
