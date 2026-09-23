<?php

require_once '../config.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: news.php');
    exit;

}


$id = $_POST['id'] ?? '';


if (
    empty($id) ||
    !ctype_digit((string)$id)
) {

    header('Location: news.php');
    exit;

}


$id = (int)$id;


/*
|--------------------------------------------------------------------------
| GET NEWS RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        image,
        link
    FROM news_events
    WHERE id = ?
");

$stmt->execute([$id]);

$news = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$news) {

    header('Location: news.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| DELETE IMAGE
|--------------------------------------------------------------------------
*/

if (!empty($news['image'])) {

    $imagePath =
        '../images/' .
        $news['image'];


    if (file_exists($imagePath)) {

        unlink($imagePath);

    }

}


/*
|--------------------------------------------------------------------------
| DELETE PDF IF IT IS AN ATTACHMENT
|--------------------------------------------------------------------------
*/

if (
    !empty($news['link']) &&
    preg_match('/\.pdf$/i', $news['link'])
) {

    $attachmentPath =
        '../images/attachments/' .
        $news['link'];


    if (file_exists($attachmentPath)) {

        unlink($attachmentPath);

    }

}


/*
|--------------------------------------------------------------------------
| DELETE DATABASE RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM news_events
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

header('Location: news.php?success=deleted');
exit;