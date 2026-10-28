<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: counter.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: counter.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM `counter` WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: counter.php?success=deleted');
    exit;
} catch (PDOException $e) {
    header('Location: counter.php?error=delete_error');
    exit;
}
