<?php
/**
 * reports.php
 * ------------------------------------------------------------
 * Analytics/report page: category breakdown, most-borrowed
 * books, overdue list, and member activity summary.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';
require_once 'includes/auth_check.php';

$base = '';
$page_title = 'Reports';
$active = 'reports';

/* ---------- Summary stats (reuse dashboard-style queries) ---------- */
$totalBooks = $conn->query("SELECT COALESCE(SUM(total_copies),0) AS total FROM books")->fetch_assoc()['total'];
$availableBooks = $conn->query("SELECT COALESCE(SUM(available_copies),0) AS total FROM books")->fetch_assoc()['total'];
$issuedBooksCount = $conn->query("SELECT COUNT(*) AS total FROM issued_books WHERE status IN ('Issued','Overdue')")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc()['total'];

/* ---------- Books per category ---------- */
$categoryBreakdown = $conn->query("
    SELECT category, COUNT(*) AS book_count, SUM(total_copies) AS total_copies
    FROM books GROUP BY category ORDER BY book_count DESC
");

/* ---------- Most borrowed books (all-time) ---------- */
$mostBorrowed = $conn->query("
    SELECT b.title, b.author, COUNT(ib.issue_id) AS times_borrowed
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    GROUP BY ib.book_id
    ORDER BY times_borrowed DESC
    LIMIT 5
");

/* ---------- All overdue books ---------- */
$overdueList = $conn->query("
    SELECT b.title, m.full_name, m.member_code, ib.due_date,
           DATEDIFF(CURDATE(), ib.due_date) AS days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    JOIN members m ON ib.member_id = m.member_id
    WHERE ib.status IN ('Issued','Overdue') AND ib.due_date < CURDATE()
    ORDER BY days_overdue DESC
");

/* ---------- Total fines collected ---------- */
$totalFines = $conn->query("SELECT COALESCE(SUM(fine_amount),0) AS total FROM issued_books WHERE status = 'Returned'")->fetch_assoc()['total'];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Reports &amp; Analytics</h1>
        <p class="subtitle">Library performance overview</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">📚</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$totalBooks; ?></div><div class="stat-label">Total Books</div></div>
    </div>
    <div class="stat-card available">
        <div class="stat-icon">✅</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$availableBooks; ?></div><div class="stat-label">Available Books</div></div>
    </div>
    <div class="stat-card issued">
        <div class="stat-icon">📤</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$issuedBooksCount; ?></div><div class="stat-label">Issued Books</div></div>
    </div>
    <div class="stat-card members">
        <div class="stat-icon">👥</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$totalMembers; ?></div><div class="stat-label">Total Members</div></div>
    </div>
</div>

<div class="dash-grid mt-2">
    <!-- Category breakdown -->
    <div class="card">
        <h2>📂 Books by Category</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Category</th><th>Titles</th><th>Total Copies</th></tr></thead>
                <tbody>
                    <?php if ($categoryBreakdown->num_rows === 0): ?>
                        <tr><td colspan="3" class="text-center">No data available.</td></tr>
                    <?php else: while ($row = $categoryBreakdown->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo (int)$row['book_count']; ?></td>
                            <td><?php echo (int)$row['total_copies']; ?></td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Most borrowed -->
    <div class="card">
        <h3>🔥 Most Borrowed Books</h3>
        <?php if ($mostBorrowed->num_rows === 0): ?>
            <p class="empty-state">No borrowing history yet.</p>
        <?php else: while ($row = $mostBorrowed->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-color);">
                <div>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                    <small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['author']); ?></small>
                </div>
                <span class="badge badge-success"><?php echo (int)$row['times_borrowed']; ?>x</span>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<div class="card mt-2">
    <h2>⚠️ Overdue Books Report</h2>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Book</th><th>Member</th><th>Due Date</th><th>Days Overdue</th></tr></thead>
            <tbody>
                <?php if ($overdueList->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center">No overdue books. 🎉</td></tr>
                <?php else: while ($row = $overdueList->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['member_code'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><span class="badge badge-danger"><?php echo (int)$row['days_overdue']; ?> day(s)</span></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-2">
    <h3>💰 Total Fines Collected</h3>
    <p style="font-size:28px; font-weight:700; color: var(--success); margin-top:8px;">
        ৳<?php echo number_format($totalFines, 2); ?>
    </p>
    <p style="color:var(--text-muted); font-size:13px;">From all returned books with late fines applied.</p>
</div>

<?php include 'includes/footer.php'; ?>
