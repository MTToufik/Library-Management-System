<?php
/**
 * members/view_members.php
 * ------------------------------------------------------------
 * Lists all members with search and links to edit/delete.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'View Members';
$active = 'view_members';

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM members";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " WHERE full_name LIKE ? OR email LIKE ? OR member_code LIKE ? OR phone LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}
$sql .= " ORDER BY member_id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$members = $stmt->get_result();

$pendingCount = $conn->query("SELECT COUNT(*) AS cnt FROM members WHERE status = 'Pending'")->fetch_assoc()['cnt'];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Members</h1>
        <p class="subtitle">Manage library member records</p>
    </div>
    <a href="add_member.php" class="btn btn-primary">➕ Add New Member</a>
</div>

<?php if ($flashSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div><?php endif; ?>
<?php if ($pendingCount > 0): ?>
    <div class="alert alert-warning">
        🕒 You have <strong><?php echo (int)$pendingCount; ?></strong> student registration(s) awaiting approval.
        <a href="pending_members.php" style="text-decoration:underline;">Review now &rarr;</a>
    </div>
<?php endif; ?>

<form method="GET" action="view_members.php" class="search-bar">
    <input type="text" name="q" placeholder="Search by name, email, code or phone..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-secondary">🔍 Search</button>
    <?php if ($search): ?><a href="view_members.php" class="btn btn-outline">Clear</a><?php endif; ?>
</form>

<div class="table-wrapper">
    <table id="dataTable">
        <thead>
            <tr>
                <th>Code</th><th>Full Name</th><th>Email</th><th>Phone</th>
                <th>Joined</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($members->num_rows === 0): ?>
                <tr><td colspan="7" class="text-center">No members found.</td></tr>
            <?php else: while ($m = $members->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['member_code']); ?></td>
                    <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($m['phone']); ?></td>
                    <td><?php echo htmlspecialchars($m['membership_date']); ?></td>
                    <td>
                        <?php
                            $statusBadge = [
                                'Active' => 'badge-success',
                                'Inactive' => 'badge-muted',
                                'Pending' => 'badge-warning',
                                'Rejected' => 'badge-danger',
                            ][$m['status']] ?? 'badge-muted';
                        ?>
                        <span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($m['status']); ?></span>
                    </td>
                    <td>
                        <div class="action-icons">
                            <a href="edit_member.php?id=<?php echo $m['member_id']; ?>" class="edit" title="Edit">✏️</a>
                            <a href="delete_member.php?id=<?php echo $m['member_id']; ?>"
                               class="delete confirm-delete" data-name="<?php echo htmlspecialchars($m['full_name']); ?>" title="Delete">🗑️</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
