<?php
/**
 * search_books.php
 * ------------------------------------------------------------
 * Dedicated search page allowing lookup of books by title
 * and/or author, with availability status shown.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';
require_once 'includes/auth_check.php';

$base = '';
$page_title = 'Search Books';
$active = 'search_books';

$title = trim($_GET['title'] ?? '');
$author = trim($_GET['author'] ?? '');
$hasSearched = isset($_GET['title']) || isset($_GET['author']);

$results = null;
if ($hasSearched) {
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];
    $types = '';

    if ($title !== '') {
        $sql .= " AND title LIKE ?";
        $params[] = '%' . $title . '%';
        $types .= 's';
    }
    if ($author !== '') {
        $sql .= " AND author LIKE ?";
        $params[] = '%' . $author . '%';
        $types .= 's';
    }
    $sql .= " ORDER BY title ASC";

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Search Books</h1>
        <p class="subtitle">Find books by title and/or author</p>
    </div>
</div>

<div class="card mb-1">
    <form method="GET" action="search_books.php" class="form-grid">
        <div class="form-group">
            <label for="title">Book Title</label>
            <input type="text" id="title" name="title" placeholder="e.g. Gatsby" value="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="form-group">
            <label for="author">Author</label>
            <input type="text" id="author" name="author" placeholder="e.g. Fitzgerald" value="<?php echo htmlspecialchars($author); ?>">
        </div>
        <div class="form-actions" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <a href="search_books.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<?php if ($hasSearched): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Available</th><th>Shelf</th></tr>
            </thead>
            <tbody>
                <?php if ($results->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center">No books matched your search.</td></tr>
                <?php else: while ($book = $results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td>
                            <?php if ($book['available_copies'] > 0): ?>
                                <span class="badge badge-success"><?php echo (int)$book['available_copies']; ?> available</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($book['shelf_location'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="icon">🔍</div>
        <p>Enter a title or author above to search the catalog.</p>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
