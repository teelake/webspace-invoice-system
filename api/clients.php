<?php
require_once __DIR__ . '/init.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        if (!$client) jsonResponse(['error' => 'Not found'], 404);
        jsonResponse($client);
    }
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY name");
    jsonResponse($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($input['name'] ?? '');
    if (empty($name)) jsonResponse(['error' => 'Name required'], 400);
    $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $name,
        $input['email'] ?? '',
        $input['phone'] ?? '',
        $input['address'] ?? '',
        $input['notes'] ?? ''
    ]);
    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch(), 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = $input['id'] ?? $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    $name = trim($input['name'] ?? '');
    if (empty($name)) jsonResponse(['error' => 'Name required'], 400);
    $stmt = $pdo->prepare("UPDATE clients SET name=?, email=?, phone=?, address=?, notes=? WHERE id=?");
    $stmt->execute([$name, $input['email'] ?? '', $input['phone'] ?? '', $input['address'] ?? '', $input['notes'] ?? '', $id]);
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch());
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'foreign key') !== false) {
            jsonResponse(['error' => 'Cannot delete: client has invoices'], 400);
        }
        throw $e;
    }
}
