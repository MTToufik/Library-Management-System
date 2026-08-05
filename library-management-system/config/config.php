<?php
/**
 * config.php
 * ------------------------------------------------------------
 * Central database connection file for the Library Management
 * System. Uses MySQLi (procedural-friendly, OOP style object).
 * Update the constants below to match your XAMPP / server setup.
 * ------------------------------------------------------------
 */

// ---- Database credentials (default XAMPP settings) ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');               // default XAMPP MySQL password is empty
define('DB_NAME', 'library_management');

// ---- Create connection ----
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ---- Check connection ----
if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

// Set charset to avoid encoding issues
$conn->set_charset('utf8mb4');

// ---- Base URL of the project (used for links / redirects) ----
// Change this if your project folder name is different.
define('BASE_URL', '/library-management-system/');

// ---- Start session (used across the whole app) ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
