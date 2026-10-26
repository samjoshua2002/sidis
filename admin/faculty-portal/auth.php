<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!ob_get_level()) {
    ob_start();
}

if (!isset($_SESSION['faculty_user_id']) || !isset($_SESSION['faculty_id'])) {
    header('Location: ../login.php');
    exit;
}
