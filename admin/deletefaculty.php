<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{

    header('Location: faculty.php');

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

    header('Location: faculty.php');

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| IMAGE DIRECTORY
|--------------------------------------------------------------------------
*/

$imageDirectory =
    __DIR__ . '/../images/faculty/';


/*
|--------------------------------------------------------------------------
| FETCH FACULTY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        image
    FROM faculty
    WHERE id = ?
    LIMIT 1
");


$stmt->execute([$id]);


$member =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$member)
{

    header('Location: faculty.php');

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
        DELETE FROM faculty
        WHERE id = ?
    ");


    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | DELETE IMAGE
    |--------------------------------------------------------------------------
    */

    if (
        !empty($member['image']) &&
        file_exists(
            $imageDirectory .
            $member['image']
        )
    )
    {

        unlink(
            $imageDirectory .
            $member['image']
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    header(
        'Location: faculty.php?success=deleted'
    );

    exit;

}
catch (PDOException $e)
{

    header(
        'Location: faculty.php?success=delete_error'
    );

    exit;

}