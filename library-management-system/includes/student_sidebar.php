<?php
/**
 * student_sidebar.php
 * ------------------------------------------------------------
 * Left sidebar navigation for logged-in students.
 * Expects $base (relative path prefix) and $active (current page key).
 * ------------------------------------------------------------
 */
if (!isset($base)) { $base = ''; }
if (!isset($active)) { $active = ''; }

if (!function_exists('nav_active')) {
    function nav_active($key, $active) {
        return $key === $active ? 'active' : '';
    }
}
?>
    <div class="app-body">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <a href="<?php echo $base; ?>student/student_dashboard.php" class="<?php echo nav_active('student_dashboard', $active); ?>">
                    <span class="nav-icon">🏠</span> Dashboard
                </a>
                <a href="<?php echo $base; ?>student/browse_books.php" class="<?php echo nav_active('browse_books', $active); ?>">
                    <span class="nav-icon">📖</span> Browse Books
                </a>
                <a href="<?php echo $base; ?>student/my_books.php" class="<?php echo nav_active('my_books', $active); ?>">
                    <span class="nav-icon">📚</span> My Borrowed Books
                </a>
                <a href="<?php echo $base; ?>student/my_requests.php" class="<?php echo nav_active('my_requests', $active); ?>">
                    <span class="nav-icon">📝</span> My Requests
                </a>
                <a href="<?php echo $base; ?>student/profile.php" class="<?php echo nav_active('profile', $active); ?>">
                    <span class="nav-icon">👤</span> My Profile
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
