<?php

require_once '../config.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    header(
        'Location: announcements.php'
    );

    exit;

}


$id = $_POST['id'] ?? '';


if (
    empty($id) ||
    !ctype_digit((string)$id)
) {

    header(
        'Location: announcements.php'
    );

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| GET ANNOUNCEMENT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        image,
        link
    FROM announcements
    WHERE id = ?
");

$stmt->execute([$id]);

$announcement =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$announcement) {

    header(
        'Location: announcements.php'
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE IMAGE
|--------------------------------------------------------------------------
*/

if (!empty($announcement['image'])) {

    $imagePath =
        '../images/' .
        $announcement['image'];


    if (
        file_exists($imagePath)
    ) {

        unlink($imagePath);

    }

}


/*
|--------------------------------------------------------------------------
| DELETE PDF
|--------------------------------------------------------------------------
*/

if (
    !empty($announcement['link']) &&
    preg_match(
        '/\.pdf$/i',
        $announcement['link']
    )
) {

    $attachmentPath =
        '../images/attachments/' .
        $announcement['link'];


    if (
        file_exists($attachmentPath)
    ) {

        unlink($attachmentPath);

    }

}


/*
|--------------------------------------------------------------------------
| DELETE DATABASE RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM announcements
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

header(
    'Location: announcements.php?success=deleted'
);

exit;