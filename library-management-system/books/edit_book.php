<?php
/**
 * books/edit_book.php
 * ------------------------------------------------------------
 * Form + handler for editing an existing book's details.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Edit Book';
$active = 'view_books';

$bookId = (int)($_GET['id'] ?? 0);
if ($bookId <= 0) {
    header('Location: view_books.php');
    exit();
}

// Fetch existing book
$stmt = $conn->prepare('SELECT * FROM books WHERE book_id = ?');
$stmt->bind_param('i', $bookId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_error'] = 'Book not found.';
    header('Location: view_books.php');
    exit();
}
$book = $result->fetch_assoc();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publishYear = trim($_POST['publish_year'] ?? '');
    $totalCopies = trim($_POST['total_copies'] ?? '');
    $shelfLocation = trim($_POST['shelf_location'] ?? '');

    if ($title === '') $errors[] = 'Book title is required.';
    if ($author === '') $errors[] = 'Author is required.';
    if ($isbn === '') $errors[] = 'ISBN is required.';
    if ($category === '') $errors[] = 'Category is required.';
    if ($totalCopies === '' || (int)$totalCopies < 1) $errors[] = 'Total copies must be at least 1.';

    // Ensure total copies is not less than currently issued copies
    $issuedCount = (int)$book['total_copies'] - (int)$book['available_copies'];
    if ((int)$totalCopies < $issuedCount) {
        $errors[] = "Total copies cannot be less than currently issued copies ($issuedCount).";
    }

    // Check duplicate ISBN (excluding current book)
    if (empty($errors)) {
        $check = $conn->prepare('SELECT book_id FROM books WHERE isbn = ? AND book_id != ?');
        $check->bind_param('si', $isbn, $bookId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Another book with this ISBN already exists.';
        }
        $check->close();
    }

    if (empty($errors)) {
        $newTotal = (int)$totalCopies;
        $newAvailable = $newTotal - $issuedCount; // recalculate available copies
        $publishYearVal = $publishYear !== '' ? (int)$publishYear : null;

        $update = $conn->prepare('UPDATE books SET title=?, author=?, isbn=?, category=?, publisher=?, publish_year=?, total_copies=?, available_copies=?, shelf_location=? WHERE book_id=?');
        $update->bind_param('sssssiiisi',
            $title, $author, $isbn, $category, $publisher, $publishYearVal,
            $newTotal, $newAvailable, $shelfLocation, $bookId
        );

        if ($update->execute()) {
            $_SESSION['flash_success'] = 'Book "' . $title . '" was updated successfully.';
            header('Location: view_books.php');
            exit();
        } else {
            $errors[] = 'Failed to update book. Please try again.';
        }
        $update->close();
    }

    // Keep posted values in the form on error
    $book = array_merge($book, [
        'title' => $title, 'author' => $author, 'isbn' => $isbn, 'category' => $category,
        'publisher' => $publisher, 'publish_year' => $publishYear, 'total_copies' => $totalCopies,
        'shelf_location' => $shelfLocation
    ]);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Edit Book</h1>
        <p class="subtitle">Update details for "<?php echo htmlspecialchars($book['title']); ?>"</p>
    </div>
    <a href="view_books.php" class="btn btn-secondary">&larr; Back to Books</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="edit_book.php?id=<?php echo $bookId; ?>" onsubmit="return validateBookForm();" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Book Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>">
                <span class="error-message" id="title_error"></span>
            </div>
            <div class="form-group">
                <label for="author">Author <span class="required">*</span></label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>">
                <span class="error-message" id="author_error"></span>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN <span class="required">*</span></label>
                <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                <span class="error-message" id="isbn_error"></span>
            </div>
            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($book['category']); ?>">
                <span class="error-message" id="category_error"></span>
            </div>
            <div class="form-group">
                <label for="publisher">Publisher</label>
                <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>">
            </div>
            <div class="form-group">
                <label for="publish_year">Publish Year</label>
                <input type="number" id="publish_year" name="publish_year" value="<?php echo htmlspecialchars($book['publish_year']); ?>" min="1000" max="2100">
            </div>
            <div class="form-group">
                <label for="total_copies">Total Copies <span class="required">*</span></label>
                <input type="number" id="total_copies" name="total_copies" value="<?php echo htmlspecialchars($book['total_copies']); ?>" min="1">
                <span class="error-message" id="total_copies_error"></span>
            </div>
            <div class="form-group">
                <label for="shelf_location">Shelf Location</label>
                <input type="text" id="shelf_location" name="shelf_location" value="<?php echo htmlspecialchars($book['shelf_location']); ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="view_books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
