<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{

    header('Location: guidelines.php');

    exit;

}


$id =
    $_POST['id'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

if (
    $id === '' ||
    !ctype_digit((string)$id)
)
{

    header('Location: guidelines.php');

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK GUIDELINE EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM guidelines
    WHERE id = ?
    LIMIT 1
");


$stmt->execute([$id]);


if (!$stmt->fetch())
{

    header('Location: guidelines.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

try
{

    $stmt = $pdo->prepare("
        DELETE FROM guidelines
        WHERE id = ?
    ");


    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: guidelines.php?success=deleted'
    );

    exit;

}
catch (PDOException $e)
{

    /*
    |--------------------------------------------------------------------------
    | DELETE FAILED
    |--------------------------------------------------------------------------
    */

    header(
        'Location: guidelines.php?success=delete_error'
    );

    exit;

}