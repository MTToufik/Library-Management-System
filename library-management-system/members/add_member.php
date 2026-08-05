<?php
/**
 * members/add_member.php
 * ------------------------------------------------------------
 * Form + handler for registering a new library member.
 * Auto-generates a unique member_code (e.g. MEM005).
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/auth_check.php';

$base = '../';
$page_title = 'Add Member';
$active = 'add_member';

$errors = [];
$formData = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => '', 'status' => 'Active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['address'] = trim($_POST['address'] ?? '');
    $formData['status'] = $_POST['status'] ?? 'Active';
    $initialPassword = $_POST['initial_password'] ?? '';

    if ($formData['full_name'] === '') $errors[] = 'Full name is required.';
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($formData['phone'] === '') $errors[] = 'Phone number is required.';
    if (strlen($initialPassword) < 6) $errors[] = 'Initial password must be at least 6 characters (this lets the student log in).';

    // Check duplicate email
    if (empty($errors)) {
        $check = $conn->prepare('SELECT member_id FROM members WHERE email = ?');
        $check->bind_param('s', $formData['email']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'A member with this email already exists.';
        }
        $check->close();
    }

    if (empty($errors)) {
        // Generate next member code (MEM001, MEM002, ...)
        $lastCode = $conn->query("SELECT member_code FROM members ORDER BY member_id DESC LIMIT 1")->fetch_assoc();
        $nextNumber = 1;
        if ($lastCode && preg_match('/(\d+)$/', $lastCode['member_code'], $m)) {
            $nextNumber = (int)$m[1] + 1;
        }
        $memberCode = 'MEM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $membershipDate = date('Y-m-d');
        $hashedPassword = password_hash($initialPassword, PASSWORD_DEFAULT);
        $adminId = $_SESSION['admin_id'];

        $stmt = $conn->prepare('INSERT INTO members (member_code, full_name, email, password, phone, address, membership_date, status, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssssi',
            $memberCode, $formData['full_name'], $formData['email'], $hashedPassword, $formData['phone'],
            $formData['address'], $membershipDate, $formData['status'], $adminId
        );

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Member "' . $formData['full_name'] . '" (' . $memberCode . ') was added successfully. They can log in with the password you set.';
            header('Location: view_members.php');
            exit();
        } else {
            $errors[] = 'Failed to add member. Please try again.';
        }
        $stmt->close();
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Add New Member</h1>
        <p class="subtitle">Register a new library member</p>
    </div>
    <a href="view_members.php" class="btn btn-secondary">&larr; Back to Members</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="add_member.php" onsubmit="return validateMemberForm();" novalidate>
        <div class="form-grid">
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($formData['full_name']); ?>" placeholder="e.g. John Carter">
                <span class="error-message" id="full_name_error"></span>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" placeholder="e.g. john@example.com">
                <span class="error-message" id="email_error"></span>
            </div>
            <div class="form-group">
                <label for="phone">Phone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" placeholder="e.g. 01711000000">
                <span class="error-message" id="phone_error"></span>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Active" <?php echo $formData['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $formData['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="initial_password">Initial Password <span class="required">*</span></label>
                <input type="text" id="initial_password" name="initial_password" placeholder="At least 6 characters — share this with the student">
                <span class="error-message" id="initial_password_error"></span>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address">Address</label>
                <textarea id="address" name="address" placeholder="Street, City"><?php echo htmlspecialchars($formData['address']); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Member</button>
            <a href="view_members.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
