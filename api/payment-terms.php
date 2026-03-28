<?php
require_once __DIR__ . '/init-tenant.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM payment_terms ORDER BY is_default DESC, days");
    jsonResponse($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($input['name'] ?? '');
    if (empty($name)) jsonResponse(['error' => 'Name required'], 400);
    $stmt = $pdo->prepare("INSERT INTO payment_terms (name, days, description, is_default) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $name,
        (int)($input['days'] ?? 0),
        $input['description'] ?? '',
        !empty($input['is_default']) ? 1 : 0
    ]);
    if (!empty($input['is_default'])) {
        $pdo->prepare("UPDATE payment_terms SET is_default = 0 WHERE id != ?")->execute([$pdo->lastInsertId()]);
    }
    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM payment_terms WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch(), 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = $input['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    $stmt = $pdo->prepare("UPDATE payment_terms SET name=?, days=?, description=?, is_default=? WHERE id=?");
    $stmt->execute([
        $input['name'] ?? '',
        (int)($input['days'] ?? 0),
        $input['description'] ?? '',
        !empty($input['is_default']) ? 1 : 0,
        $id
    ]);
    if (!empty($input['is_default'])) {
        $pdo->prepare("UPDATE payment_terms SET is_default = 0 WHERE id != ?")->execute([$id]);
    }
    $stmt = $pdo->prepare("SELECT * FROM payment_terms WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch());
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    $pdo->prepare("DELETE FROM payment_terms WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}
