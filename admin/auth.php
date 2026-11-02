<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Password must be changed first: only Settings (and logout) until it is changed
if (!empty($_SESSION['admin_must_change'])
    && !in_array(basename($_SERVER['PHP_SELF']), ['settings.php', 'logout.php'], true)) {
    header('Location: settings.php?do=password');
    exit;
}