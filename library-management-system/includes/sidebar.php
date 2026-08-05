<?php
/**
 * sidebar.php
 * ------------------------------------------------------------
 * Reusable left sidebar navigation.
 * Expects $base (relative path prefix) and $active (current page key)
 * to be set by the including page for highlighting the active link.
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
                <a href="<?php echo $base; ?>dashboard.php" class="<?php echo nav_active('dashboard', $active); ?>">
                    <span class="nav-icon">🏠</span> Dashboard
                </a>

                <p class="nav-section">Books</p>
                <a href="<?php echo $base; ?>books/view_books.php" class="<?php echo nav_active('view_books', $active); ?>">
                    <span class="nav-icon">📖</span> View Books
                </a>
                <a href="<?php echo $base; ?>books/add_book.php" class="<?php echo nav_active('add_book', $active); ?>">
                    <span class="nav-icon">➕</span> Add Book
                </a>

                <p class="nav-section">Members</p>
                <a href="<?php echo $base; ?>members/view_members.php" class="<?php echo nav_active('view_members', $active); ?>">
                    <span class="nav-icon">👥</span> View Members
                </a>
                <a href="<?php echo $base; ?>members/add_member.php" class="<?php echo nav_active('add_member', $active); ?>">
                    <span class="nav-icon">➕</span> Add Member
                </a>
                <a href="<?php echo $base; ?>members/pending_members.php" class="<?php echo nav_active('pending_members', $active); ?>">
                    <span class="nav-icon">🕒</span> Pending Registrations
                </a>

                <p class="nav-section">Circulation</p>
                <a href="<?php echo $base; ?>requests/manage_requests.php" class="<?php echo nav_active('manage_requests', $active); ?>">
                    <span class="nav-icon">📝</span> Book Requests
                </a>
                <a href="<?php echo $base; ?>issue_book.php" class="<?php echo nav_active('issue_book', $active); ?>">
                    <span class="nav-icon">📤</span> Issue Book
                </a>
                <a href="<?php echo $base; ?>return_book.php" class="<?php echo nav_active('return_book', $active); ?>">
                    <span class="nav-icon">📥</span> Return Book
                </a>
                <a href="<?php echo $base; ?>search_books.php" class="<?php echo nav_active('search_books', $active); ?>">
                    <span class="nav-icon">🔍</span> Search Books
                </a>

                <p class="nav-section">Analytics</p>
                <a href="<?php echo $base; ?>reports.php" class="<?php echo nav_active('reports', $active); ?>">
                    <span class="nav-icon">📊</span> Reports
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
