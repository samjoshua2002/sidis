<?php
require_once __DIR__ . '/config.php';
$hash = password_hash('password123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password = '$hash' WHERE role = 'faculty'");
$pdo->exec("INSERT INTO users (username, password, role, faculty_id, status, created_at, updated_at) 
            VALUES ('ravindra', '$hash', 'faculty', 153, 1, NOW(), NOW()) 
            ON DUPLICATE KEY UPDATE password = '$hash', role = 'faculty', faculty_id = 153, status = 1");
echo "Faculty passwords updated to password123\n";
