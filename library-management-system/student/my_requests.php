<?php
/**
 * student/my_requests.php
 * ------------------------------------------------------------
 * Shows the status of every borrow request the student has
 * submitted: Pending, Approved, or Rejected.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/student_auth_check.php';

$base = '../';
$page_title = 'My Requests';
$active = 'my_requests';
$studentId = $_SESSION['student_id'];

$requests = $conn->prepare("
    SELECT br.request_date, br.status, br.processed_date, b.title, b.author
    FROM book_requests br
    JOIN books b ON br.book_id = b.book_id
    WHERE br.member_id = ?
    ORDER BY br.request_date DESC
");
$requests->bind_param('i', $studentId);
$requests->execute();
$requestsResult = $requests->get_result();

include '../includes/header.php';
include '../includes/student_sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>My Requests</h1>
        <p class="subtitle">Track the status of your book borrow requests</p>
    </div>
    <a href="browse_books.php" class="btn btn-primary">📖 Browse Books</a>
</div>

<div class="table-wrapper">
    <table>
        <thead><tr><th>Book</th><th>Author</th><th>Requested On</th><th>Status</th><th>Processed On</th></tr></thead>
        <tbody>
            <?php if ($requestsResult->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center">You haven't made any borrow requests yet.</td></tr>
            <?php else: while ($row = $requestsResult->fetch_assoc()):
                $badgeClass = $row['status'] === 'Approved' ? 'badge-success' : ($row['status'] === 'Rejected' ? 'badge-danger' : 'badge-warning');
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['processed_date'] ?: '-'); ?></td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
