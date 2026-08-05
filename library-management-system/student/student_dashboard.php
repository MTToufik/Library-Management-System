<?php
/**
 * student/student_dashboard.php
 * ------------------------------------------------------------
 * Landing page after student login: personal borrowing stats,
 * currently borrowed books, and recent request status.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/student_auth_check.php';

$base = '../';
$page_title = 'My Dashboard';
$active = 'student_dashboard';
$studentId = $_SESSION['student_id'];

/* ---------- Personal stats ---------- */
$currentlyBorrowed = $conn->prepare("SELECT COUNT(*) AS cnt FROM issued_books WHERE member_id = ? AND status IN ('Issued','Overdue')");
$currentlyBorrowed->bind_param('i', $studentId);
$currentlyBorrowed->execute();
$borrowedCount = $currentlyBorrowed->get_result()->fetch_assoc()['cnt'];
$currentlyBorrowed->close();

$overdueCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM issued_books WHERE member_id = ? AND status IN ('Issued','Overdue') AND due_date < CURDATE()");
$overdueCheck->bind_param('i', $studentId);
$overdueCheck->execute();
$overdueCount = $overdueCheck->get_result()->fetch_assoc()['cnt'];
$overdueCheck->close();

$pendingReqCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM book_requests WHERE member_id = ? AND status = 'Pending'");
$pendingReqCheck->bind_param('i', $studentId);
$pendingReqCheck->execute();
$pendingReqCount = $pendingReqCheck->get_result()->fetch_assoc()['cnt'];
$pendingReqCheck->close();

$finesCheck = $conn->prepare("SELECT COALESCE(SUM(fine_amount),0) AS total FROM issued_books WHERE member_id = ? AND status = 'Returned'");
$finesCheck->bind_param('i', $studentId);
$finesCheck->execute();
$totalFines = $finesCheck->get_result()->fetch_assoc()['total'];
$finesCheck->close();

/* ---------- Currently borrowed books ---------- */
$myBooks = $conn->prepare("
    SELECT b.title, b.author, ib.issue_date, ib.due_date, ib.status
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    WHERE ib.member_id = ? AND ib.status IN ('Issued','Overdue')
    ORDER BY ib.due_date ASC
");
$myBooks->bind_param('i', $studentId);
$myBooks->execute();
$myBooksResult = $myBooks->get_result();

include '../includes/header.php';
include '../includes/student_sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?> 👋</h1>
        <p class="subtitle">Member Code: <?php echo htmlspecialchars($_SESSION['student_code']); ?></p>
    </div>
    <a href="browse_books.php" class="btn btn-primary">📖 Browse Books</a>
</div>

<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">📚</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$borrowedCount; ?></div><div class="stat-label">Books Borrowed</div></div>
    </div>
    <div class="stat-card issued">
        <div class="stat-icon">⚠️</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$overdueCount; ?></div><div class="stat-label">Overdue Books</div></div>
    </div>
    <div class="stat-card members">
        <div class="stat-icon">📝</div>
        <div class="stat-info"><div class="stat-value"><?php echo (int)$pendingReqCount; ?></div><div class="stat-label">Pending Requests</div></div>
    </div>
    <div class="stat-card available">
        <div class="stat-icon">💰</div>
        <div class="stat-info"><div class="stat-value">৳<?php echo number_format($totalFines, 2); ?></div><div class="stat-label">Fines Paid (Total)</div></div>
    </div>
</div>

<div class="card">
    <h2>My Currently Borrowed Books</h2>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Title</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($myBooksResult->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center">You have no books currently borrowed. <a href="browse_books.php">Browse the catalog &rarr;</a></td></tr>
                <?php else: while ($row = $myBooksResult->fetch_assoc()):
                    $isOverdue = $row['due_date'] < date('Y-m-d');
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><span class="badge <?php echo $isOverdue ? 'badge-danger' : 'badge-warning'; ?>"><?php echo $isOverdue ? 'Overdue' : 'Issued'; ?></span></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
