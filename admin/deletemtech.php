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

    header('Location: mtech.php');

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

    header('Location: mtech.php');

    exit;

}


$id =
    (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK STUDENT EXISTS
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT id
        FROM mtech_students
        WHERE id = ?
        LIMIT 1
    ");


$stmt->execute([
    $id
]);


if (!$stmt->fetch())
{

    header('Location: mtech.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

try
{

    $stmt =
        $pdo->prepare("
            DELETE FROM mtech_students
            WHERE id = ?
        ");


    $stmt->execute([
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: mtech.php?success=deleted'
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
        'Location: mtech.php?success=delete_error'
    );

    exit;

}