<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email.';
    } else {
        $token = generateResetToken($email);
        if ($token) {
            $resetLink = APP_URL . '/reset-password.php?token=' . $token;
            $body = "Click the link below to reset your password:<br><br><a href=\"$resetLink\">Reset Password</a><br><br>Link expires in 1 hour.";
            if (sendMail($email, 'Reset your password - ' . APP_NAME, $body)) {
                $message = 'If that email exists, we\'ve sent a reset link. Check your inbox.';
            } else {
                $message = 'Reset link: ' . $resetLink . ' (Email sending failed - copy this link)';
            }
        } else {
            $message = 'If that email exists, we\'ve sent a reset link. Check your inbox.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Forgot Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php else: ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
        <?php endif; ?>
        <a href="index.php" class="auth-link">Back to login</a>
    </div>
</body>
</html>
