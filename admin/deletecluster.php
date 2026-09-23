<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{

    header('Location: clusters.php');

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

    header('Location: clusters.php');

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| CHECK CLUSTER EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id
    FROM clusters
    WHERE id = ?
    LIMIT 1
");


$stmt->execute([$id]);


$cluster =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$cluster)
{

    header('Location: clusters.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE CLUSTER
|--------------------------------------------------------------------------
*/

try
{

    $stmt = $pdo->prepare("
        DELETE FROM clusters
        WHERE id = ?
    ");


    $stmt->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header(
        'Location: clusters.php?success=deleted'
    );

    exit;

}
catch (PDOException $e)
{

    header(
        'Location: clusters.php?success=delete_error'
    );

    exit;

}