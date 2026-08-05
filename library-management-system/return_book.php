<?php
/**
 * return_book.php
 * ------------------------------------------------------------
 * Processes the return of an issued book. Calculates a late fine
 * (৳5/day) if returned after the due date, increments the book's
 * available_copies, and marks the issue record as "Returned".
 * ------------------------------------------------------------
 */
require_once 'config/config.php';
require_once 'includes/auth_check.php';

$base = '';
$page_title = 'Return Book';
$active = 'return_book';

const FINE_PER_DAY = 5.00;

$errors = [];
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// ---------- Handle the return submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issueId = (int)($_POST['issue_id'] ?? 0);
    $returnDate = trim($_POST['return_date'] ?? date('Y-m-d'));

    if ($issueId <= 0) {
        $errors[] = 'Invalid issue record selected.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM issued_books WHERE issue_id = ? AND status IN ("Issued","Overdue")');
        $stmt->bind_param('i', $issueId);
        $stmt->execute();
        $issue = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$issue) {
            $errors[] = 'This book has already been returned or the record does not exist.';
        } else {
            // Calculate fine if overdue
            $fine = 0.00;
            $dueDate = new DateTime($issue['due_date']);
            $actualReturn = new DateTime($returnDate);
            if ($actualReturn > $dueDate) {
                $daysLate = $dueDate->diff($actualReturn)->days;
                $fine = $daysLate * FINE_PER_DAY;
            }

            $conn->begin_transaction();
            try {
                $update = $conn->prepare('UPDATE issued_books SET return_date = ?, status = "Returned", fine_amount = ? WHERE issue_id = ?');
                $update->bind_param('sdi', $returnDate, $fine, $issueId);
                $update->execute();
                $update->close();

                $incrementBook = $conn->prepare('UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?');
                $incrementBook->bind_param('i', $issue['book_id']);
                $incrementBook->execute();
                $incrementBook->close();

                $conn->commit();
                $msg = 'Book returned successfully.';
                if ($fine > 0) {
                    $msg .= ' Late fine applied: ৳' . number_format($fine, 2) . '.';
                }
                $_SESSION['flash_success'] = $msg;
                header('Location: return_book.php');
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = 'Failed to process return. Please try again.';
            }
        }
    }
}

// ---------- List all books currently issued (awaiting return) ----------
$pendingReturns = $conn->query("
    SELECT ib.issue_id, ib.book_id, b.title, b.author, m.full_name, m.member_code,
           ib.issue_date, ib.due_date,
           DATEDIFF(CURDATE(), ib.due_date) AS days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.book_id
    JOIN members m ON ib.member_id = m.member_id
    WHERE ib.status IN ('Issued','Overdue')
    ORDER BY ib.due_date ASC
");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Return Book</h1>
        <p class="subtitle">Process a book return and calculate late fines (৳<?php echo number_format(FINE_PER_DAY, 2); ?>/day)</p>
    </div>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Book</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th><th>Return</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pendingReturns->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center">No books pending return. 🎉</td></tr>
            <?php else: while ($row = $pendingReturns->fetch_assoc()):
                $overdue = $row['days_overdue'] > 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?> <br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['author']); ?></small></td>
                    <td><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['member_code'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                    <td>
                        <?php if ($overdue): ?>
                            <span class="badge badge-danger"><?php echo (int)$row['days_overdue']; ?> day(s) overdue</span>
                        <?php else: ?>
                            <span class="badge badge-warning">On time</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="return_book.php" style="display:flex; gap:6px; align-items:center;">
                            <input type="hidden" name="issue_id" value="<?php echo $row['issue_id']; ?>">
                            <input type="date" name="return_date" value="<?php echo date('Y-m-d'); ?>" style="padding:6px 8px; border:1px solid var(--border-color); border-radius:6px; font-size:13px;">
                            <button type="submit" class="btn btn-success btn-sm">Return</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
