<?php
/**
 * books/delete_book.php
 * ------------------------------------------------------------
 * Deletes a book record. Prevents deletion if the book currently
 * has copies issued out to members.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$bookId = (int)($_GET['id'] ?? 0);

if ($bookId <= 0) {
    $_SESSION['flash_error'] = 'Invalid book selected.';
    header('Location: view_books.php');
    exit();
}

// Check if this book has any currently issued (not yet returned) copies
$check = $conn->prepare("SELECT COUNT(*) AS cnt FROM issued_books WHERE book_id = ? AND status IN ('Issued','Overdue')");
$check->bind_param('i', $bookId);
$check->execute();
$cnt = $check->get_result()->fetch_assoc()['cnt'];
$check->close();

if ($cnt > 0) {
    $_SESSION['flash_error'] = 'Cannot delete this book — it currently has copies issued to members.';
    header('Location: view_books.php');
    exit();
}

$stmt = $conn->prepare('DELETE FROM books WHERE book_id = ?');
$stmt->bind_param('i', $bookId);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Book deleted successfully.';
} else {
    $_SESSION['flash_error'] = 'Failed to delete book.';
}
$stmt->close();

header('Location: view_books.php');
exit();
