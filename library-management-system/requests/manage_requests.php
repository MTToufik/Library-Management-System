<?php
/**
 * requests/manage_requests.php
 * ------------------------------------------------------------
 * Lists pending book borrow requests submitted by students.
 * Approving a request creates an issued_books record (14-day
 * due date) and decrements the book's available copies.
 * Rejecting just marks the request as Rejected.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Book Requests';
$active = 'manage_requests';

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ---------- Handle approve/reject actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $adminId = $_SESSION['admin_id'];
    $today = date('Y-m-d');

    if ($requestId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $reqStmt = $conn->prepare('SELECT * FROM book_requests WHERE request_id = ? AND status = "Pending"');
        $reqStmt->bind_param('i', $requestId);
        $reqStmt->execute();
        $reqRow = $reqStmt->get_result()->fetch_assoc();
        $reqStmt->close();

        if (!$reqRow) {
            $_SESSION['flash_error'] = 'This request has already been processed.';
        } elseif ($action === 'reject') {
            $update = $conn->prepare('UPDATE book_requests SET status = "Rejected", processed_by = ?, processed_date = ? WHERE request_id = ?');
            $update->bind_param('isi', $adminId, $today, $requestId);
            $update->execute();
            $update->close();
            $_SESSION['flash_success'] = 'Request rejected.';
        } else {
            // Approve: verify a copy is still available, then issue the book
            $bookCheck = $conn->prepare('SELECT available_copies, title FROM books WHERE book_id = ?');
            $bookCheck->bind_param('i', $reqRow['book_id']);
            $bookCheck->execute();
            $bookRow = $bookCheck->get_result()->fetch_assoc();
            $bookCheck->close();

            if (!$bookRow || $bookRow['available_copies'] < 1) {
                $_SESSION['flash_error'] = 'Cannot approve — no available copies of "' . ($bookRow['title'] ?? 'this book') . '" left.';
            } else {
                $conn->begin_transaction();
                try {
                    $dueDate = date('Y-m-d', strtotime('+14 days'));

                    $insert = $conn->prepare('INSERT INTO issued_books (book_id, member_id, issue_date, due_date, status, issued_by) VALUES (?, ?, ?, ?, "Issued", ?)');
                    $insert->bind_param('iissi', $reqRow['book_id'], $reqRow['member_id'], $today, $dueDate, $adminId);
                    $insert->execute();
                    $insert->close();

                    $decrement = $conn->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ? AND available_copies > 0');
                    $decrement->bind_param('i', $reqRow['book_id']);
                    $decrement->execute();
                    $decrement->close();

                    $update = $conn->prepare('UPDATE book_requests SET status = "Approved", processed_by = ?, processed_date = ? WHERE request_id = ?');
                    $update->bind_param('isi', $adminId, $today, $requestId);
                    $update->execute();
                    $update->close();

                    $conn->commit();
                    $_SESSION['flash_success'] = 'Request approved and book issued to the student.';
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['flash_error'] = 'Failed to process the request. Please try again.';
                }
            }
        }
    }
    header('Location: manage_requests.php');
    exit();
}

$pendingRequests = $conn->query("
    SELECT br.request_id, br.request_date, b.title, b.author, b.available_copies, m.full_name, m.member_code
    FROM book_requests br
    JOIN books b ON br.book_id = b.book_id
    JOIN members m ON br.member_id = m.member_id
    WHERE br.status = 'Pending'
    ORDER BY br.request_date ASC
");

$recentProcessed = $conn->query("
    SELECT br.request_id, br.status, br.processed_date, b.title, m.full_name
    FROM book_requests br
    JOIN books b ON br.book_id = b.book_id
    JOIN members m ON br.member_id = m.member_id
    WHERE br.status IN ('Approved','Rejected')
    ORDER BY br.processed_date DESC
    LIMIT 8
");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Book Requests</h1>
        <p class="subtitle">Review borrow requests submitted by students</p>
    </div>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div><?php endif; ?>

<div class="table-wrapper mb-1">
    <table>
        <thead>
            <tr><th>Book</th><th>Requested By</th><th>Request Date</th><th>Available Copies</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if ($pendingRequests->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center">No pending requests. 🎉</td></tr>
            <?php else: while ($r = $pendingRequests->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['title']); ?><br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($r['author']); ?></small></td>
                    <td><?php echo htmlspecialchars($r['full_name'] . ' (' . $r['member_code'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($r['request_date']); ?></td>
                    <td>
                        <?php if ($r['available_copies'] > 0): ?>
                            <span class="badge badge-success"><?php echo (int)$r['available_copies']; ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">0</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-icons">
                            <form method="POST" action="manage_requests.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $r['request_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-sm" <?php echo $r['available_copies'] < 1 ? 'disabled' : ''; ?>>✅ Approve</button>
                            </form>
                            <form method="POST" action="manage_requests.php" style="display:inline;" onsubmit="return confirm('Reject this request?');">
                                <input type="hidden" name="request_id" value="<?php echo $r['request_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-sm">❌ Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Recently Processed</h2>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Book</th><th>Student</th><th>Status</th><th>Processed Date</th></tr></thead>
            <tbody>
                <?php if ($recentProcessed->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center">No processed requests yet.</td></tr>
                <?php else: while ($r = $recentProcessed->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td><span class="badge <?php echo $r['status'] === 'Approved' ? 'badge-success' : 'badge-danger'; ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['processed_date']); ?></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
