<?php
/**
 * issue_book.php
 * ------------------------------------------------------------
 * Issues an available book copy to an active member.
 * Decrements the book's available_copies and creates a record
 * in issued_books with a due date 14 days from issue date.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';
require_once 'includes/auth_check.php';

$base = '';
$page_title = 'Issue Book';
$active = 'issue_book';

$errors = [];
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $memberId = (int)($_POST['member_id'] ?? 0);
    $issueDate = trim($_POST['issue_date'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($bookId <= 0) $errors[] = 'Please select a book.';
    if ($memberId <= 0) $errors[] = 'Please select a member.';
    if ($issueDate === '') $errors[] = 'Issue date is required.';
    if ($dueDate === '') $errors[] = 'Due date is required.';
    if ($issueDate !== '' && $dueDate !== '' && $dueDate < $issueDate) $errors[] = 'Due date cannot be before the issue date.';

    // Verify book has available copies
    if (empty($errors)) {
        $bookCheck = $conn->prepare('SELECT available_copies, title FROM books WHERE book_id = ?');
        $bookCheck->bind_param('i', $bookId);
        $bookCheck->execute();
        $bookRow = $bookCheck->get_result()->fetch_assoc();
        $bookCheck->close();

        if (!$bookRow) {
            $errors[] = 'Selected book does not exist.';
        } elseif ($bookRow['available_copies'] < 1) {
            $errors[] = 'No available copies of "' . $bookRow['title'] . '" to issue.';
        }
    }

    // Verify member is active
    if (empty($errors)) {
        $memberCheck = $conn->prepare("SELECT status FROM members WHERE member_id = ?");
        $memberCheck->bind_param('i', $memberId);
        $memberCheck->execute();
        $memberRow = $memberCheck->get_result()->fetch_assoc();
        $memberCheck->close();

        if (!$memberRow) {
            $errors[] = 'Selected member does not exist.';
        } elseif ($memberRow['status'] !== 'Active') {
            $errors[] = 'This member is inactive and cannot borrow books.';
        }
    }

    if (empty($errors)) {
        // Use a transaction so the insert + copy decrement stay in sync
        $conn->begin_transaction();
        try {
            $insert = $conn->prepare('INSERT INTO issued_books (book_id, member_id, issue_date, due_date, status, issued_by) VALUES (?, ?, ?, ?, "Issued", ?)');
            $adminId = $_SESSION['admin_id'];
            $insert->bind_param('iissi', $bookId, $memberId, $issueDate, $dueDate, $adminId);
            $insert->execute();
            $insert->close();

            $decrement = $conn->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ? AND available_copies > 0');
            $decrement->bind_param('i', $bookId);
            $decrement->execute();
            $decrement->close();

            $conn->commit();
            $_SESSION['flash_success'] = 'Book issued successfully.';
            header('Location: issue_book.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Failed to issue book. Please try again.';
        }
    }
}

// Books with at least 1 available copy
$availableBooks = $conn->query("SELECT book_id, title, author, available_copies FROM books WHERE available_copies > 0 ORDER BY title ASC");

// Active members only
$activeMembers = $conn->query("SELECT member_id, member_code, full_name FROM members WHERE status = 'Active' ORDER BY full_name ASC");

// Currently issued books (for reference table below the form)
$currentIssues = $conn->query("
    SELECT ib.issue_id, b.title, m.full_name, m.member_code, ib.issue_date, ib.due_date, ib.status
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
        <h1>Issue Book</h1>
        <p class="subtitle">Lend a book to a library member</p>
    </div>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="card mb-1">
    <form method="POST" action="issue_book.php" onsubmit="return validateIssueForm();" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="book_id">Select Book <span class="required">*</span></label>
                <select id="book_id" name="book_id">
                    <option value="">-- Choose a book --</option>
                    <?php while ($b = $availableBooks->fetch_assoc()): ?>
                        <option value="<?php echo $b['book_id']; ?>" <?php echo (($_POST['book_id'] ?? '') == $b['book_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['title'] . ' — ' . $b['author'] . ' (' . $b['available_copies'] . ' available)'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span class="error-message" id="book_id_error"></span>
            </div>
            <div class="form-group">
                <label for="member_id">Select Member <span class="required">*</span></label>
                <select id="member_id" name="member_id">
                    <option value="">-- Choose a member --</option>
                    <?php while ($m = $activeMembers->fetch_assoc()): ?>
                        <option value="<?php echo $m['member_id']; ?>" <?php echo (($_POST['member_id'] ?? '') == $m['member_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['full_name'] . ' (' . $m['member_code'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span class="error-message" id="member_id_error"></span>
            </div>
            <div class="form-group">
                <label for="issue_date">Issue Date <span class="required">*</span></label>
                <input type="date" id="issue_date" name="issue_date" value="<?php echo htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')); ?>">
                <span class="error-message" id="issue_date_error"></span>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date <span class="required">*</span></label>
                <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'))); ?>">
                <span class="error-message" id="due_date_error"></span>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">📤 Issue Book</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Currently Issued Books</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Book</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if ($currentIssues->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center">No books currently issued.</td></tr>
                <?php else: while ($row = $currentIssues->fetch_assoc()):
                    $isOverdue = $row['status'] === 'Overdue' || $row['due_date'] < date('Y-m-d');
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name'] . ' (' . $row['member_code'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><span class="badge <?php echo $isOverdue ? 'badge-danger' : 'badge-warning'; ?>"><?php echo $isOverdue ? 'Overdue' : 'Issued'; ?></span></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
