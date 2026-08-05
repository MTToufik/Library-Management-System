<?php
/**
 * student/browse_books.php
 * ------------------------------------------------------------
 * Lets a student search the catalog by title/author and submit
 * a borrow request for any book with available copies.
 * A student cannot request a book they already have out, or
 * request the same book twice while a request is pending.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/student_auth_check.php';

$base = '../';
$page_title = 'Browse Books';
$active = 'browse_books';
$studentId = $_SESSION['student_id'];

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/* ---------- Handle borrow request submission ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)($_POST['book_id'] ?? 0);

    if ($bookId <= 0) {
        $_SESSION['flash_error'] = 'Invalid book selected.';
    } else {
        // Block if student already has this book issued or a pending request for it
        $dupCheck = $conn->prepare("
            SELECT 1 FROM issued_books WHERE book_id = ? AND member_id = ? AND status IN ('Issued','Overdue')
            UNION
            SELECT 1 FROM book_requests WHERE book_id = ? AND member_id = ? AND status = 'Pending'
        ");
        $dupCheck->bind_param('iiii', $bookId, $studentId, $bookId, $studentId);
        $dupCheck->execute();
        $hasDup = $dupCheck->get_result()->num_rows > 0;
        $dupCheck->close();

        if ($hasDup) {
            $_SESSION['flash_error'] = 'You already have this book issued or a pending request for it.';
        } else {
            $availCheck = $conn->prepare('SELECT available_copies FROM books WHERE book_id = ?');
            $availCheck->bind_param('i', $bookId);
            $availCheck->execute();
            $availRow = $availCheck->get_result()->fetch_assoc();
            $availCheck->close();

            if (!$availRow || $availRow['available_copies'] < 1) {
                $_SESSION['flash_error'] = 'Sorry, no copies of this book are currently available.';
            } else {
                $today = date('Y-m-d');
                $insert = $conn->prepare('INSERT INTO book_requests (book_id, member_id, request_date, status) VALUES (?, ?, ?, "Pending")');
                $insert->bind_param('iis', $bookId, $studentId, $today);
                if ($insert->execute()) {
                    $_SESSION['flash_success'] = 'Borrow request submitted! A librarian will review it shortly.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to submit request. Please try again.';
                }
                $insert->close();
            }
        }
    }
    header('Location: browse_books.php' . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : ''));
    exit();
}

/* ---------- Search ---------- */
$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM books";
$params = [];
$types = '';
if ($search !== '') {
    $sql .= " WHERE title LIKE ? OR author LIKE ? OR category LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
    $types = 'sss';
}
$sql .= " ORDER BY title ASC";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$books = $stmt->get_result();

include '../includes/header.php';
include '../includes/student_sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Browse Books</h1>
        <p class="subtitle">Search the catalog and request to borrow a book</p>
    </div>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div><?php endif; ?>

<form method="GET" action="browse_books.php" class="search-bar">
    <input type="text" name="q" placeholder="Search by title, author or category..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-secondary">🔍 Search</button>
    <?php if ($search): ?><a href="browse_books.php" class="btn btn-outline">Clear</a><?php endif; ?>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr><th>Title</th><th>Author</th><th>Category</th><th>Available</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if ($books->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center">No books found.</td></tr>
            <?php else: while ($book = $books->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                    <td>
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="badge badge-success"><?php echo (int)$book['available_copies']; ?> available</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Out of stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="browse_books.php<?php echo $search ? '?q=' . urlencode($search) : ''; ?>">
                            <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                            <button type="submit" class="btn btn-primary btn-sm" <?php echo $book['available_copies'] < 1 ? 'disabled' : ''; ?>>
                                Request to Borrow
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
