<?php
/**
 * header.php
 * ------------------------------------------------------------
 * Reusable top header/navbar for all logged-in pages.
 * Expects (optional) variables set by the including page:
 *   $base   -> relative path prefix to project root ('' or '../')
 *   $page_title -> string shown in <title> tag
 * ------------------------------------------------------------
 */
if (!isset($base)) { $base = ''; }
if (!isset($page_title)) { $page_title = 'Library Management System'; }

// Determine whether the current session belongs to a student or an admin/librarian
$is_student_session = isset($_SESSION['student_id']);
$dashboard_link = $is_student_session ? 'student/student_dashboard.php' : 'dashboard.php';
$welcome_name = $is_student_session ? ($_SESSION['student_name'] ?? 'Student') : ($_SESSION['admin_name'] ?? 'Guest');
$welcome_role = $is_student_session ? 'Student' : ($_SESSION['admin_role'] ?? '');
$logout_link = $is_student_session ? 'student_logout.php' : 'logout.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | LMS</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">
    <!-- Top Navbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">&#9776;</button>
            <a href="<?php echo $base . $dashboard_link; ?>" class="brand">
                <span class="brand-icon">📚</span> LMS
            </a>
        </div>
        <div class="topbar-right">
            <span class="welcome-text">
                Welcome, <strong><?php echo htmlspecialchars($welcome_name); ?></strong>
                <span class="role-badge"><?php echo htmlspecialchars($welcome_role); ?></span>
            </span>
            <a href="<?php echo $base . $logout_link; ?>" class="btn-logout">Logout</a>
        </div>
    </header>
