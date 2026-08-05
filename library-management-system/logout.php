<?php
/**
 * logout.php
 * ------------------------------------------------------------
 * Destroys the current session and redirects to the login page.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

header('Location: login.php');
exit();
