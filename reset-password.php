<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (empty($token)) {
    $error = 'Invalid or expired reset link.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (resetPassword($token, $password)) {
        $success = true;
    } else {
        $error = 'Invalid or expired reset link.';
    }
} elseif (!validateResetToken($token)) {
    $error = 'Invalid or expired reset link.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Reset Password</h1>
            <p>Enter your new password</p>
        </div>
        <?php if ($success): ?>
        <div class="alert alert-success">Password reset successfully. <a href="index.php">Login now</a></div>
        <?php elseif ($error && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <a href="forgot-password.php" class="auth-link">Request new link</a>
        <?php else: ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
