<?php
/**
 * student_logout.php
 * ------------------------------------------------------------
 * Destroys the student's session variables and redirects to
 * the student login page.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';

unset($_SESSION['student_id'], $_SESSION['student_name'], $_SESSION['student_code']);
session_destroy();

header('Location: student_login.php');
exit();
