<?php
/**
 * dashboard.php
 * ------------------------------------------------------------
 * Main dashboard showing library statistics and recent activity.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';
require_once 'includes/auth_check.php';

$base = '';
$page_title = 'Dashboard';
$active = 'dashboard';

/* ---------- Statistics queries ---------- */
$totalBooks = $conn->query("SELECT COALESCE(SUM(total_copies),0) AS total FROM books")->fetch_assoc()['total'];
$availableBooks = $conn->query("SELECT COALESCE(SUM(available_copies),0) AS total FROM books")->fetch_assoc()['total'];
$issuedBooksCount = $conn->query("SELECT COUNT(*) AS total FROM issued_books WHERE status IN ('Issued','Overdue')")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM members WHERE status = 'Active'")->fetch_assoc()['total'];
$pendingMembers = $conn->query("SELECT COUNT(*) AS total FROM members WHERE status = 'Pending'")->fetch_assoc()['total'];
$pendingRequests = $conn->query("SELECT COUNT(*) AS total FROM book_requests WHERE status = 'Pending'")->fetch_assoc()['total'];

/* ---------- Recent issued books (last 5) ---------- */
$recentIssues = $conn->query("
    SELECT ib.issue_id, b.title, m.full_name, ib.issue_date, ib.due_date, ib.status
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    JOIN members m ON ib.member_id = m.member_id
    ORDER BY ib.issue_id DESC
    LIMIT 5
");

/* ---------- Overdue books ---------- */
$overdueBooks = $conn->query("
    SELECT ib.issue_id, b.title, m.full_name, ib.due_date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    JOIN members m ON ib.member_id = m.member_id
    WHERE ib.status = 'Overdue' OR (ib.status = 'Issued' AND ib.due_date < CURDATE())
    ORDER BY ib.due_date ASC
    LIMIT 5
");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="subtitle">Overview of your library at a glance</p>
    </div>
</div>

<?php if ($pendingMembers > 0): ?>
    <div class="alert alert-warning">
        🕒 <strong><?php echo (int)$pendingMembers; ?></strong> student registration(s) awaiting approval.
        <a href="members/pending_members.php" style="text-decoration:underline;">Review now &rarr;</a>
    </div>
<?php endif; ?>
<?php if ($pendingRequests > 0): ?>
    <div class="alert alert-warning">
        📝 <strong><?php echo (int)$pendingRequests; ?></strong> book borrow request(s) awaiting review.
        <a href="requests/manage_requests.php" style="text-decoration:underline;">Review now &rarr;</a>
    </div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">📚</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo (int)$totalBooks; ?></div>
            <div class="stat-label">Total Books</div>
        </div>
    </div>
    <div class="stat-card available">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo (int)$availableBooks; ?></div>
            <div class="stat-label">Available Books</div>
        </div>
    </div>
    <div class="stat-card issued">
        <div class="stat-icon">📤</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo (int)$issuedBooksCount; ?></div>
            <div class="stat-label">Issued Books</div>
        </div>
    </div>
    <div class="stat-card members">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo (int)$totalMembers; ?></div>
            <div class="stat-label">Total Members</div>
        </div>
    </div>
</div>

<div class="dash-grid">
    <!-- Recent Issues -->
    <div class="card">
        <h2>Recent Book Issues</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Book</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if ($recentIssues->num_rows === 0): ?>
                        <tr><td colspan="5" class="text-center">No issue records yet.</td></tr>
                    <?php else: while ($row = $recentIssues->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <?php
                                    $badgeClass = $row['status'] === 'Returned' ? 'badge-success' : ($row['status'] === 'Overdue' ? 'badge-danger' : 'badge-warning');
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overdue Alerts -->
    <div class="card">
        <h3>⚠️ Overdue Books</h3>
        <?php if ($overdueBooks->num_rows === 0): ?>
            <p class="empty-state">No overdue books. 🎉</p>
        <?php else: while ($row = $overdueBooks->fetch_assoc()): ?>
            <div class="alert alert-danger" style="margin-bottom:10px;">
                <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                <?php echo htmlspecialchars($row['full_name']); ?> &mdash; due <?php echo htmlspecialchars($row['due_date']); ?>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
