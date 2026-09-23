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

    header('Location: committee.php');

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

    header('Location: committee.php');

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
    FROM advisory_committee
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);


if (!$stmt->fetch())
{

    header('Location: committee.php');

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
        DELETE FROM advisory_committee
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: committee.php?success=deleted'
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
        'Location: committee.php?success=delete_error'
    );

    exit;

}