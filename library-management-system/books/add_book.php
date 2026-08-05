<?php
/**
 * books/add_book.php
 * ------------------------------------------------------------
 * Form + handler for adding a new book to the catalog.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Add Book';
$active = 'add_book';

$errors = [];
$formData = ['title' => '', 'author' => '', 'isbn' => '', 'category' => '', 'publisher' => '', 'publish_year' => '', 'total_copies' => '', 'shelf_location' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['author'] = trim($_POST['author'] ?? '');
    $formData['isbn'] = trim($_POST['isbn'] ?? '');
    $formData['category'] = trim($_POST['category'] ?? '');
    $formData['publisher'] = trim($_POST['publisher'] ?? '');
    $formData['publish_year'] = trim($_POST['publish_year'] ?? '');
    $formData['total_copies'] = trim($_POST['total_copies'] ?? '');
    $formData['shelf_location'] = trim($_POST['shelf_location'] ?? '');

    // ---------- Server-side validation ----------
    if ($formData['title'] === '') $errors[] = 'Book title is required.';
    if ($formData['author'] === '') $errors[] = 'Author is required.';
    if ($formData['isbn'] === '') $errors[] = 'ISBN is required.';
    if ($formData['category'] === '') $errors[] = 'Category is required.';
    if ($formData['total_copies'] === '' || (int)$formData['total_copies'] < 1) $errors[] = 'Total copies must be at least 1.';

    // Check duplicate ISBN
    if (empty($errors)) {
        $check = $conn->prepare('SELECT book_id FROM books WHERE isbn = ?');
        $check->bind_param('s', $formData['isbn']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'A book with this ISBN already exists.';
        }
        $check->close();
    }

    if (empty($errors)) {
        $totalCopies = (int)$formData['total_copies'];
        $publishYear = $formData['publish_year'] !== '' ? (int)$formData['publish_year'] : null;

        $stmt = $conn->prepare('INSERT INTO books (title, author, isbn, category, publisher, publish_year, total_copies, available_copies, shelf_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssiiis',
            $formData['title'], $formData['author'], $formData['isbn'], $formData['category'],
            $formData['publisher'], $publishYear, $totalCopies, $totalCopies, $formData['shelf_location']
        );

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Book "' . $formData['title'] . '" was added successfully.';
            header('Location: view_books.php');
            exit();
        } else {
            $errors[] = 'Failed to add book. Please try again.';
        }
        $stmt->close();
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Add New Book</h1>
        <p class="subtitle">Add a new title to the library catalog</p>
    </div>
    <a href="view_books.php" class="btn btn-secondary">&larr; Back to Books</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="add_book.php" onsubmit="return validateBookForm();" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Book Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($formData['title']); ?>" placeholder="e.g. The Great Gatsby">
                <span class="error-message" id="title_error"></span>
            </div>
            <div class="form-group">
                <label for="author">Author <span class="required">*</span></label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($formData['author']); ?>" placeholder="e.g. F. Scott Fitzgerald">
                <span class="error-message" id="author_error"></span>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN <span class="required">*</span></label>
                <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($formData['isbn']); ?>" placeholder="e.g. 9780743273565">
                <span class="error-message" id="isbn_error"></span>
            </div>
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($formData['category']); ?>" placeholder="e.g. Fiction, Science, Technology">
                <span class="error-message" id="category_error"></span>
            </div>
            <div class="form-group">
                <label for="publisher">Publisher</label>
                <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($formData['publisher']); ?>" placeholder="e.g. Penguin Books">
            </div>
            <div class="form-group">
                <label for="publish_year">Publish Year</label>
                <input type="number" id="publish_year" name="publish_year" value="<?php echo htmlspecialchars($formData['publish_year']); ?>" placeholder="e.g. 2020" min="1000" max="2100">
            </div>
            <div class="form-group">
                <label for="total_copies">Total Copies <span class="required">*</span></label>
                <input type="number" id="total_copies" name="total_copies" value="<?php echo htmlspecialchars($formData['total_copies']); ?>" min="1" placeholder="e.g. 5">
                <span class="error-message" id="total_copies_error"></span>
            </div>
            <div class="form-group">
                <label for="shelf_location">Shelf Location</label>
                <input type="text" id="shelf_location" name="shelf_location" value="<?php echo htmlspecialchars($formData['shelf_location']); ?>" placeholder="e.g. A1-01">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Book</button>
            <a href="view_books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
