<?php
/**
 * auth_check.php
 * ------------------------------------------------------------
 * Include this file at the TOP of every protected page.
 * Redirects to login.php if the admin/librarian is not logged in.
 * ------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    $inSubfolder = strpos($_SERVER['PHP_SELF'], '/books/') !== false
        || strpos($_SERVER['PHP_SELF'], '/members/') !== false
        || strpos($_SERVER['PHP_SELF'], '/requests/') !== false;
    header('Location: ' . ($inSubfolder ? '../login.php' : 'login.php'));
    exit();
}
?>
