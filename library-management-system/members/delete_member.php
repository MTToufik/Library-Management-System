<?php
/**
 * members/delete_member.php
 * ------------------------------------------------------------
 * Deletes a member record. Prevents deletion if the member
 * currently has any books issued (not yet returned).
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$memberId = (int)($_GET['id'] ?? 0);

if ($memberId <= 0) {
    $_SESSION['flash_error'] = 'Invalid member selected.';
    header('Location: view_members.php');
    exit();
}

// Check if this member currently has any issued (not returned) books
$check = $conn->prepare("SELECT COUNT(*) AS cnt FROM issued_books WHERE member_id = ? AND status IN ('Issued','Overdue')");
$check->bind_param('i', $memberId);
$check->execute();
$cnt = $check->get_result()->fetch_assoc()['cnt'];
$check->close();

if ($cnt > 0) {
    $_SESSION['flash_error'] = 'Cannot delete this member — they currently have book(s) issued.';
    header('Location: view_members.php');
    exit();
}

$stmt = $conn->prepare('DELETE FROM members WHERE member_id = ?');
$stmt->bind_param('i', $memberId);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Member deleted successfully.';
} else {
    $_SESSION['flash_error'] = 'Failed to delete member.';
}
$stmt->close();

header('Location: view_members.php');
exit();
