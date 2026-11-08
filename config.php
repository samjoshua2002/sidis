<?php

$env = file_exists(__DIR__ . '/.env') ? parse_ini_file(__DIR__ . '/.env') : [];

$dbHost = $env['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$dbName = $env['DB_NAME'] ?? getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'sidis');
$dbUser = $env['DB_USER'] ?? getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'root');
$dbPass = $env['DB_PASS'] ?? getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: '');

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