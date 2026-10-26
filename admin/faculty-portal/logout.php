<?php
session_start();

unset($_SESSION['faculty_user_id']);
unset($_SESSION['faculty_username']);
unset($_SESSION['faculty_id']);

header('Location: ../login.php');
exit;
