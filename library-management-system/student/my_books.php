<?php
/**
 * student/my_books.php
 * ------------------------------------------------------------
 * Shows the student's full borrowing history: currently issued
 * books (with due dates) and previously returned books (with
 * any fines that were applied).
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/student_auth_check.php';

$base = '../';
$page_title = 'My Borrowed Books';
$active = 'my_books';
$studentId = $_SESSION['student_id'];

$current = $conn->prepare("
    SELECT b.title, b.author, ib.issue_date, ib.due_date
    FROM issued_books ib JOIN books b ON ib.book_id = b.book_id
    WHERE ib.member_id = ? AND ib.status IN ('Issued','Overdue')
    ORDER BY ib.due_date ASC
");
$current->bind_param('i', $studentId);
$current->execute();
$currentResult = $current->get_result();

$history = $conn->prepare("
    SELECT b.title, b.author, ib.issue_date, ib.due_date, ib.return_date, ib.fine_amount
    FROM issued_books ib JOIN books b ON ib.book_id = b.book_id
    WHERE ib.member_id = ? AND ib.status = 'Returned'
    ORDER BY ib.return_date DESC
");
$history->bind_param('i', $studentId);
$history->execute();
$historyResult = $history->get_result();

include '../includes/header.php';
include '../includes/student_sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>My Borrowed Books</h1>
        <p class="subtitle">Currently borrowed and past borrowing history</p>
    </div>
</div>

<div class="card mb-1">
    <h2>Currently Borrowed</h2>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Title</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php if ($currentResult->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center">No books currently borrowed.</td></tr>
                <?php else: while ($row = $currentResult->fetch_assoc()):
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

<div class="card">
    <h2>Borrowing History</h2>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Title</th><th>Author</th><th>Issue Date</th><th>Return Date</th><th>Fine</th></tr></thead>
            <tbody>
                <?php if ($historyResult->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center">No return history yet.</td></tr>
                <?php else: while ($row = $historyResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                        <td>
                            <?php if ($row['fine_amount'] > 0): ?>
                                <span class="badge badge-danger">৳<?php echo number_format($row['fine_amount'], 2); ?></span>
                            <?php else: ?>
                                <span class="badge badge-success">None</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
