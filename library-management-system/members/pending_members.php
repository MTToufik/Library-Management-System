<?php
/**
 * members/pending_members.php
 * ------------------------------------------------------------
 * Lists student accounts awaiting approval (status = 'Pending')
 * and lets a librarian/admin approve or reject each one.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Pending Registrations';
$active = 'pending_members';

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ---------- Handle approve/reject actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int)($_POST['member_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $adminId = $_SESSION['admin_id'];

    if ($memberId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = $action === 'approve' ? 'Active' : 'Rejected';
        $stmt = $conn->prepare('UPDATE members SET status = ?, approved_by = ? WHERE member_id = ? AND status = "Pending"');
        $stmt->bind_param('sii', $newStatus, $adminId, $memberId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['flash_success'] = $action === 'approve'
                ? 'Student account approved. They can now log in.'
                : 'Student registration rejected.';
        } else {
            $_SESSION['flash_error'] = 'Could not update this registration (it may already be processed).';
        }
        $stmt->close();
    }
    header('Location: pending_members.php');
    exit();
}

$pending = $conn->query("SELECT * FROM members WHERE status = 'Pending' ORDER BY member_id ASC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Pending Registrations</h1>
        <p class="subtitle">Review and approve new student sign-ups</p>
    </div>
    <a href="view_members.php" class="btn btn-secondary">&larr; All Members</a>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div><?php endif; ?>

<div class="table-wrapper">
    <table>
        <thead>
            <tr><th>Code</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Requested On</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if ($pending->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center">No pending registrations. 🎉</td></tr>
            <?php else: while ($m = $pending->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['member_code']); ?></td>
                    <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($m['phone']); ?></td>
                    <td><?php echo htmlspecialchars($m['membership_date']); ?></td>
                    <td>
                        <div class="action-icons">
                            <form method="POST" action="pending_members.php" style="display:inline;">
                                <input type="hidden" name="member_id" value="<?php echo $m['member_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-sm">✅ Approve</button>
                            </form>
                            <form method="POST" action="pending_members.php" style="display:inline;" onsubmit="return confirm('Reject this registration?');">
                                <input type="hidden" name="member_id" value="<?php echo $m['member_id']; ?>">
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

<?php include '../includes/footer.php'; ?>
