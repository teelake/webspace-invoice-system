<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function login($email, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, password, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

function generateResetToken($email) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) return null;
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires]);
    return $token;
}

function validateResetToken($token) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function resetPassword($token, $newPassword) {
    $data = validateResetToken($token);
    if (!$data) return false;
    
    $pdo = getDB();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hash, $data['email']]);
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
