<?php
/**
 * login.php
 * ------------------------------------------------------------
 * Admin / Librarian login. Verifies credentials against the
 * `admins` table using password_verify() and starts a PHP session.
 * ------------------------------------------------------------
 */
require_once 'config/config.php';

// If already logged in, go straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare('SELECT admin_id, username, password, full_name, role FROM admins WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // Successful login - set up session
                $_SESSION['admin_id']   = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
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
    <title>Login | Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="brand">📚 LMS</div>
            <p class="auth-sub">Sign in to manage your library</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm" onsubmit="return validateLoginForm();" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <span class="error-message" id="username_error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <span class="error-message" id="password_error"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-demo-box">
                <strong>Demo credentials:</strong><br>
                Username: <code>admin</code> &nbsp; Password: <code>admin123</code>
            </div>

            <p class="text-center mt-2">Are you a student? <a href="student_login.php">Student Login</a></p>
            <p class="text-center mt-1"><a href="index.php">&larr; Back to Home</a></p>
        </div>
    </div>

    <script src="assets/js/validation.js"></script>
</body>
</html>
