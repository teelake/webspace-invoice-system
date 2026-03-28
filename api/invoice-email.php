<?php
require_once __DIR__ . '/init-tenant.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$invoiceId = (int)($input['invoice_id'] ?? 0);
$to = trim($input['to'] ?? '');
$subject = $input['subject'] ?? 'Your Invoice';
$body = $input['body'] ?? 'Please find your invoice attached.';

if (!$invoiceId || !$to) {
    jsonResponse(['error' => 'Invoice ID and recipient email required'], 400);
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT i.*, c.name as client_name, c.email as client_email
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    WHERE i.id = ?
");
$stmt->execute([$invoiceId]);
$inv = $stmt->fetch();
if (!$inv) jsonResponse(['error' => 'Invoice not found'], 404);

$invoiceUrl = APP_URL . '/invoice-view?id=' . $invoiceId . '&print=1';
$htmlBody = nl2br(htmlspecialchars($body)) . '<br><br><a href="' . $invoiceUrl . '">View Invoice</a>';

if (sendMail($to, $subject, $htmlBody)) {
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['error' => 'Email could not be sent. Check server mail config.'], 500);
}
