<?php
/**
 * books/view_books.php
 * ------------------------------------------------------------
 * Lists all books with a simple client + server search filter,
 * and links to edit/delete each record.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'View Books';
$active = 'view_books';

/* ---------- Handle flash messages from redirects ---------- */
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/* ---------- Server-side search (by title/author/category) ---------- */
$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM books";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}
$sql .= " ORDER BY book_id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Books</h1>
        <p class="subtitle">Manage your entire book catalog</p>
    </div>
    <a href="add_book.php" class="btn btn-primary">➕ Add New Book</a>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div><?php endif; ?>

<form method="GET" action="view_books.php" class="search-bar">
    <input type="text" name="q" placeholder="Search by title, author, category or ISBN..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-secondary">🔍 Search</button>
    <?php if ($search): ?><a href="view_books.php" class="btn btn-outline">Clear</a><?php endif; ?>
</form>

<div class="table-wrapper">
    <table id="dataTable">
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>ISBN</th><th>Category</th>
                <th>Total</th><th>Available</th><th>Shelf</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($books->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center">No books found.</td></tr>
            <?php else: while ($book = $books->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                    <td><?php echo (int)$book['total_copies']; ?></td>
                    <td>
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="badge badge-success"><?php echo (int)$book['available_copies']; ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">0</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($book['shelf_location'] ?: '-'); ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="edit_book.php?id=<?php echo $book['book_id']; ?>" class="edit" title="Edit">✏️</a>
                            <a href="delete_book.php?id=<?php echo $book['book_id']; ?>"
                               class="delete confirm-delete" data-name="<?php echo htmlspecialchars($book['title']); ?>" title="Delete">🗑️</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
