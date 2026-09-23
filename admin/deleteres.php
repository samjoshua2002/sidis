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
        'Location: research.php'
    );

    exit;

}


$id =
    $_POST['id'] ?? '';


if (
    empty($id) ||
    !ctype_digit((string)$id)
) {

    header(
        'Location: research.php'
    );

    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| GET RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        image,
        link
    FROM featured_research
    WHERE id = ?
");

$stmt->execute([$id]);

$research =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$research) {

    header(
        'Location: research.php'
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE IMAGE
|--------------------------------------------------------------------------
*/

if (!empty($research['image'])) {

    $imagePath =
        '../images/' .
        $research['image'];


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
    !empty($research['link']) &&
    preg_match(
        '/\.pdf$/i',
        $research['link']
    )
) {

    $attachmentPath =
        '../images/attachments/' .
        $research['link'];


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
    DELETE FROM featured_research
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

header(
    'Location: research.php?success=deleted'
);

exit;