<?php
/**
 * student/profile.php
 * ------------------------------------------------------------
 * Lets a student update their contact details and change their
 * password (current password required to confirm the change).
 * ------------------------------------------------------------
 */
require_once '../config/config.php';
require_once '../includes/student_auth_check.php';

$base = '../';
$page_title = 'My Profile';
$active = 'profile';
$studentId = $_SESSION['student_id'];

$errors = [];
$success = '';

// Fetch current profile
$stmt = $conn->prepare('SELECT * FROM members WHERE member_id = ?');
$stmt->bind_param('i', $studentId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    // ---------- Update profile details ----------
    if ($formType === 'profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($fullName === '') $errors[] = 'Full name is required.';
        if ($phone === '') $errors[] = 'Phone number is required.';

        if (empty($errors)) {
            $update = $conn->prepare('UPDATE members SET full_name = ?, phone = ?, address = ? WHERE member_id = ?');
            $update->bind_param('sssi', $fullName, $phone, $address, $studentId);
            if ($update->execute()) {
                $_SESSION['student_name'] = $fullName;
                $success = 'Profile updated successfully.';
                $student['full_name'] = $fullName;
                $student['phone'] = $phone;
                $student['address'] = $address;
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
            $update->close();
        }
    }

    // ---------- Change password ----------
    if ($formType === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';

        if (!password_verify($currentPassword, $student['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare('UPDATE members SET password = ? WHERE member_id = ?');
            $update->bind_param('si', $newHash, $studentId);
            if ($update->execute()) {
                $success = 'Password changed successfully.';
            } else {
                $errors[] = 'Failed to change password. Please try again.';
            }
            $update->close();
        }
    }
}

include '../includes/header.php';
include '../includes/student_sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p class="subtitle">Member Code: <?php echo htmlspecialchars($student['member_code']); ?></p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<div class="dash-grid">
    <div class="card">
        <h2>Profile Details</h2>
        <form method="POST" action="profile.php">
            <input type="hidden" name="form_type" value="profile">
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>">
            </div>
            <div class="form-group">
                <label>Email (cannot be changed)</label>
                <input type="email" value="<?php echo htmlspecialchars($student['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="phone">Phone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <div class="card">
        <h3>Change Password</h3>
        <form method="POST" action="profile.php">
            <input type="hidden" name="form_type" value="password">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
                <label for="confirm_new_password">Confirm New Password</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password">
            </div>
            <button type="submit" class="btn btn-secondary">Update Password</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
