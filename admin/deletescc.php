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

    header('Location: scc.php');

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

    header('Location: scc.php');

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK MEMBER EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM school_consultative_committee
    WHERE id = ?
    LIMIT 1
");


$stmt->execute([$id]);


if (!$stmt->fetch())
{

    header('Location: scc.php');

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
        DELETE FROM school_consultative_committee
        WHERE id = ?
    ");


    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: scc.php?success=deleted'
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
        'Location: scc.php?success=delete_error'
    );

    exit;

}