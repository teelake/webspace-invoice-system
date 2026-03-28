<?php
require_once __DIR__ . '/../config/app.php';

$dbConfig = __DIR__ . '/../config/database.php';
if (!file_exists($dbConfig)) {
    header('Location: ' . APP_URL . '/setup');
    exit;
}
require_once $dbConfig;

if (!function_exists('getDB')) {
    function getDB() {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return $pdo;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, name, is_system_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['is_system_admin'] = (int)($u['is_system_admin'] ?? 0);
    }
    return $u;
}

/** Platform operator — not tied to a tenant; no invoice/client UI. */
function isSystemAdmin() {
    if (!isLoggedIn()) return false;
    if (array_key_exists('is_system_admin', $_SESSION)) {
        return (int)$_SESSION['is_system_admin'] === 1;
    }
    $u = getCurrentUser();
    return $u && (int)($u['is_system_admin'] ?? 0) === 1;
}

/** Redirect if the user is a platform operator (tenant pages only). */
function requireTenantUser() {
    requireLogin();
    if (isSystemAdmin()) {
        header('Location: ' . APP_URL . '/platform-dashboard');
        exit;
    }
}

function redirectAfterLogin() {
    $admin = array_key_exists('is_system_admin', $_SESSION)
        ? ((int)$_SESSION['is_system_admin'] === 1)
        : isSystemAdmin();
    $path = $admin ? '/platform-dashboard' : '/dashboard';
    header('Location: ' . APP_URL . $path);
    exit;
}

function login($email, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, password, name, is_system_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_system_admin'] = (int)($user['is_system_admin'] ?? 0);
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . APP_URL . '/');
    exit;
}

function generateResetToken($email) {
    $pdo = getDB();
    // Ensure password_reset_tokens table exists (for fresh installs or older DBs)
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
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
