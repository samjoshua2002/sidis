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

// Password was set/reset by the admin: only Settings (and logout) until it is changed
if (!empty($_SESSION['faculty_must_change'])
    && !in_array(basename($_SERVER['PHP_SELF']), ['settings.php', 'logout.php'], true)) {
    header('Location: settings.php?do=password');
    exit;
}
