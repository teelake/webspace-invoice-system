<?php
require_once __DIR__ . '/init-tenant.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $invoiceId = (int)($input['invoice_id'] ?? 0);
    $amount = (float)($input['amount'] ?? 0);
    if (!$invoiceId || $amount <= 0) jsonResponse(['error' => 'Invalid invoice or amount'], 400);

    $stmt = $pdo->prepare("INSERT INTO invoice_payments (invoice_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $invoiceId,
        $amount,
        $input['payment_date'] ?? date('Y-m-d'),
        $input['notes'] ?? ''
    ]);
    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM invoice_payments WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse($stmt->fetch(), 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    $pdo->prepare("DELETE FROM invoice_payments WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}
