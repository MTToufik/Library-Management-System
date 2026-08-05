<?php
/**
 * register.php
 * ------------------------------------------------------------
 * Student self-registration. New accounts are created with
 * status = 'Pending' and must be approved by a librarian/admin
 * (see members/pending_members.php) before the student can log in.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';

if (isset($_SESSION['student_id'])) {
    header('Location: student/student_dashboard.php');
    exit();
}

$errors = [];
$success = '';
$formData = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['address'] = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($formData['full_name'] === '') $errors[] = 'Full name is required.';
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($formData['phone'] === '') $errors[] = 'Phone number is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    // Check duplicate email across members
    if (empty($errors)) {
        $check = $conn->prepare('SELECT member_id FROM members WHERE email = ?');
        $check->bind_param('s', $formData['email']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
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
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO members (member_code, full_name, email, password, phone, address, membership_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, "Pending")');
        $stmt->bind_param('sssssss',
            $memberCode, $formData['full_name'], $formData['email'], $hashedPassword,
            $formData['phone'], $formData['address'], $membershipDate
        );

        if ($stmt->execute()) {
            $success = 'Registration submitted! Your account (' . $memberCode . ') is pending approval from a librarian. You will be able to log in once approved.';
            $formData = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => ''];
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card" style="max-width: 480px;">
            <div class="brand">📚 LMS</div>
            <p class="auth-sub">Create your student account</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p class="text-center mt-1"><a href="student_login.php" class="btn btn-primary btn-block">Go to Student Login</a></p>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" id="registerForm" onsubmit="return validateRegisterForm();" novalidate>
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" placeholder="e.g. John Carter" value="<?php echo htmlspecialchars($formData['full_name']); ?>">
                        <span class="error-message" id="full_name_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="e.g. john@example.com" value="<?php echo htmlspecialchars($formData['email']); ?>">
                        <span class="error-message" id="email_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone <span class="required">*</span></label>
                        <input type="text" id="phone" name="phone" placeholder="e.g. 01711000000" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                        <span class="error-message" id="phone_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="Street, City" value="<?php echo htmlspecialchars($formData['address']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="At least 6 characters">
                        <span class="error-message" id="password_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password">
                        <span class="error-message" id="confirm_password_error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>

                <p class="text-center mt-2">Already have an account? <a href="student_login.php">Login here</a></p>
            <?php endif; ?>

            <p class="text-center mt-1"><a href="index.php">&larr; Back to Home</a></p>
        </div>
    </div>

    <script src="assets/js/validation.js"></script>
</body>
</html>
