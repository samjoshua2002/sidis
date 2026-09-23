<?php

$env = parse_ini_file(__DIR__ . '/.env');

$dbHost = $env['DB_HOST'];
$dbName = $env['DB_NAME'];
$dbUser = $env['DB_USER'];
$dbPass = $env['DB_PASS'];

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}