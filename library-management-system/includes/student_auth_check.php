<?php
/**
 * student_auth_check.php
 * ------------------------------------------------------------
 * Include this file at the TOP of every student-portal page.
 * Redirects to student_login.php if the student is not logged in.
 * ------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/student/') ? '../student_login.php' : 'student_login.php'));
    exit();
}
?>
