<?php
/**
 * student_login.php
 * ------------------------------------------------------------
 * Login for students (members table). Blocks login for accounts
 * that are still Pending approval, Rejected, or Inactive.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';

if (isset($_SESSION['student_id'])) {
    header('Location: student/student_dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare('SELECT member_id, member_code, full_name, password, status FROM members WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();

            if (!password_verify($password, $student['password'])) {
                $error = 'Invalid email or password.';
            } elseif ($student['status'] === 'Pending') {
                $error = 'Your account is still pending approval from a librarian. Please check back later.';
            } elseif ($student['status'] === 'Rejected') {
                $error = 'Your registration was not approved. Please contact the library for assistance.';
            } elseif ($student['status'] === 'Inactive') {
                $error = 'Your account is inactive. Please contact the library for assistance.';
            } else {
                // Active - log the student in
                $_SESSION['student_id'] = $student['member_id'];
                $_SESSION['student_name'] = $student['full_name'];
                $_SESSION['student_code'] = $student['member_code'];

                header('Location: student/student_dashboard.php');
                exit();
            }
        } else {
            $error = 'Invalid email or password.';
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
    <title>Student Login | Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="brand">📚 LMS</div>
            <p class="auth-sub">Student Login</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="student_login.php" id="studentLoginForm" onsubmit="return validateStudentLoginForm();" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <span class="error-message" id="email_error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <span class="error-message" id="password_error"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-demo-box">
                <strong>Demo student account:</strong><br>
                Email: <code>john.carter@example.com</code> &nbsp; Password: <code>student123</code>
            </div>

            <p class="text-center mt-2">New here? <a href="register.php">Create a student account</a></p>
            <p class="text-center mt-1">
                <a href="login.php">Librarian Login</a> &nbsp;|&nbsp; <a href="index.php">&larr; Back to Home</a>
            </p>
        </div>
    </div>

    <script src="assets/js/validation.js"></script>
</body>
</html>
