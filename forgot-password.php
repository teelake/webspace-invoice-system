<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirectAfterLogin();
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email.';
    } else {
        try {
            $token = generateResetToken($email);
            if ($token) {
                $resetLink = APP_URL . '/reset-password?token=' . $token;
                $body = "Click the link below to reset your password:<br><br><a href=\"$resetLink\">Reset Password</a><br><br>Link expires in 1 hour.";
                if (sendMail($email, 'Reset your password - ' . APP_NAME, $body)) {
                    $message = 'If that email exists, we\'ve sent a reset link. Check your inbox.';
                } else {
                    $message = 'Reset link: ' . $resetLink . ' (Email sending failed - copy this link)';
                }
            } else {
                $message = 'If that email exists, we\'ve sent a reset link. Check your inbox.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please ensure the database is set up correctly.';
        } catch (Exception $e) {
            $error = 'Something went wrong. Please try again later.';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
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
        <a href="<?= APP_URL ?>/" class="auth-link">Back to login</a>
    </div>
</body>
</html>
