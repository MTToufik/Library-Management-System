<?php
/**
 * index.php - Public Landing Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <nav class="landing-nav">
        <div class="brand">📚 LMS</div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="student_login.php">Student Login</a>
            <a href="login.php" class="btn btn-primary">Staff Login</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Smart Library Management System for Modern Libraries</h1>
        <p>Manage books, members, issues, and returns from one simple, secure dashboard — built for librarians and students alike.</p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="register.php" class="btn btn-secondary">Student Sign Up &rarr;</a>
            <a href="login.php" class="btn" style="background:rgba(255,255,255,0.15); color:#fff; border:1.5px solid rgba(255,255,255,0.5);">Librarian Login</a>
        </div>
    </section>

    <section class="features-section" id="features">
        <h2>Everything Your Library Needs</h2>
        <p class="section-sub">A complete toolkit to manage day-to-day library operations efficiently.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">📖</div>
                <h3>Book Management</h3>
                <p>Add, edit, delete and browse your entire book catalog with shelf locations and copy tracking.</p>
            </div>
            <div class="feature-card">
                <div class="icon">👥</div>
                <h3>Member Management</h3>
                <p>Keep track of member details, membership status, and borrowing history in one place.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔄</div>
                <h3>Issue &amp; Return</h3>
                <p>Issue books to members and process returns with automatic due-date and fine calculation.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔍</div>
                <h3>Powerful Search</h3>
                <p>Instantly find any book by title or author across your entire collection.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Reports &amp; Analytics</h3>
                <p>Live dashboard statistics on total books, available copies, issued books, and members.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔒</div>
                <h3>Secure Access</h3>
                <p>Session-based authentication keeps your library data safe from unauthorized access.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🎓</div>
                <h3>Student Self-Service</h3>
                <p>Students register, browse the catalog, and request books online — librarians simply approve and issue.</p>
            </div>
        </div>
    </section>

    <footer class="landing-footer" id="about">
        <p>&copy; <?php echo date('Y'); ?> Library Management System &mdash; Built with PHP &amp; MySQL.</p>
    </footer>

</body>
</html>
