<?php
/**
 * members/edit_member.php
 * ------------------------------------------------------------
 * Form + handler for editing an existing member's details.
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Edit Member';
$active = 'view_members';

$memberId = (int)($_GET['id'] ?? 0);
if ($memberId <= 0) {
    header('Location: view_members.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM members WHERE member_id = ?');
$stmt->bind_param('i', $memberId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_error'] = 'Member not found.';
    header('Location: view_members.php');
    exit();
}
$member = $result->fetch_assoc();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        $check = $conn->prepare('SELECT member_id FROM members WHERE email = ? AND member_id != ?');
        $check->bind_param('si', $email, $memberId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Another member with this email already exists.';
        }
        $check->close();
    }

    if (empty($errors)) {
        $update = $conn->prepare('UPDATE members SET full_name=?, email=?, phone=?, address=?, status=? WHERE member_id=?');
        $update->bind_param('sssssi', $fullName, $email, $phone, $address, $status, $memberId);

        if ($update->execute()) {
            $_SESSION['flash_success'] = 'Member "' . $fullName . '" was updated successfully.';
            header('Location: view_members.php');
            exit();
        } else {
            $errors[] = 'Failed to update member. Please try again.';
        }
        $update->close();
    }

    $member = array_merge($member, [
        'full_name' => $fullName, 'email' => $email, 'phone' => $phone,
        'address' => $address, 'status' => $status
    ]);
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Edit Member</h1>
        <p class="subtitle">Update details for "<?php echo htmlspecialchars($member['full_name']); ?>" (<?php echo htmlspecialchars($member['member_code']); ?>)</p>
    </div>
    <a href="view_members.php" class="btn btn-secondary">&larr; Back to Members</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="edit_member.php?id=<?php echo $memberId; ?>" onsubmit="return validateMemberForm();" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($member['full_name']); ?>">
                <span class="error-message" id="full_name_error"></span>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>">
                <span class="error-message" id="email_error"></span>
            </div>
            <div class="form-group">
                <label for="phone">Phone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
                <span class="error-message" id="phone_error"></span>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Active" <?php echo $member['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $member['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($member['address']); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Member</button>
            <a href="view_members.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
