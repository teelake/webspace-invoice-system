<?php
require_once __DIR__ . '/init.php';
$pdo = getDB();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) jsonResponse(['error' => 'Not found'], 404);
    jsonResponse($user);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    // Update profile (name, email)
    if (isset($input['name']) || isset($input['email'])) {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($name)) {
            jsonResponse(['error' => 'Name is required'], 400);
        }
        if (empty($email)) {
            jsonResponse(['error' => 'Email is required'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Invalid email format'], 400);
        }

        // Check email uniqueness (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Email is already in use'], 400);
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $userId]);
        $_SESSION['user_name'] = $name;

        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        jsonResponse($stmt->fetch());
    }

    // Change password
    if (isset($input['current_password']) && isset($input['new_password'])) {
        $currentPassword = $input['current_password'];
        $newPassword = $input['new_password'];

        if (strlen($newPassword) < 6) {
            jsonResponse(['error' => 'New password must be at least 6 characters'], 400);
        }

        $passCol = usersPasswordColumnSql($pdo);
        if ($passCol === '') {
            jsonResponse(['error' => 'Password storage is not configured'], 500);
        }
        $stmt = $pdo->prepare("SELECT {$passCol} AS password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user || empty($user['password']) || !password_verify($currentPassword, $user['password'])) {
            jsonResponse(['error' => 'Current password is incorrect'], 400);
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET {$passCol} = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        jsonResponse(['success' => true, 'message' => 'Password updated']);
    }

    jsonResponse(['error' => 'No valid update data provided'], 400);
}

jsonResponse(['error' => 'Method not allowed'], 405);
