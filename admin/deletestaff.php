<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{

    header('Location: staff.php');

    exit;

}


$id =
    $_POST['id'] ?? '';


if (
    $id === '' ||
    !ctype_digit((string)$id)
)
{

    header('Location: staff.php');

    exit;

}


$id = (int)$id;


$imageDirectory =
    __DIR__ . '/../images/staff/';


$stmt = $pdo->prepare("
    SELECT
        id,
        image
    FROM staff
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);


$member =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$member)
{

    header('Location: staff.php');

    exit;

}


try
{

    $stmt = $pdo->prepare("
        DELETE FROM staff
        WHERE id = ?
    ");

    $stmt->execute([$id]);


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


    header(
        'Location: staff.php?success=deleted'
    );

    exit;

}
catch (PDOException $e)
{

    header(
        'Location: staff.php?success=delete_error'
    );

    exit;

}