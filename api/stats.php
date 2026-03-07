<?php
require_once __DIR__ . '/init.php';
$pdo = getDB();
$company = getCompanySettings();
$taxRate = (float)($company['tax_rate'] ?? 0);

$stats = [];

// Total invoices
$stmt = $pdo->query("SELECT COUNT(*) as c FROM invoices");
$stats['total_invoices'] = (int)$stmt->fetch()['c'];

// Overdue (unpaid invoices past due date)
$stmt = $pdo->query("SELECT COUNT(*) as c FROM invoices WHERE status = 'unpaid' AND due_date < CURDATE()");
$stats['overdue'] = (int)$stmt->fetch()['c'];

// Draft
$stmt = $pdo->query("SELECT COUNT(*) as c FROM invoices WHERE status = 'draft'");
$stats['draft'] = (int)$stmt->fetch()['c'];

// Total revenue (paid invoices)
$stmt = $pdo->query("
    SELECT COALESCE(SUM(p.amount), 0) as total
    FROM invoice_payments p
");
$stats['total_paid'] = (float)$stmt->fetch()['total'];

// Outstanding (invoices with balance)
$stmt = $pdo->query("
    SELECT i.id,
           (SELECT COALESCE(SUM(amount), 0) FROM invoice_items WHERE invoice_id = i.id) as subtotal,
           (SELECT COALESCE(SUM(amount), 0) FROM invoice_payments WHERE invoice_id = i.id) as paid
    FROM invoices i
    WHERE i.status IN ('unpaid', 'draft') AND i.status != 'cancelled'
");
$outstanding = 0;
while ($row = $stmt->fetch()) {
    $subtotal = (float)$row['subtotal'];
    $tax = $subtotal * ($taxRate / 100);
    $total = $subtotal + $tax;
    $balance = $total - (float)$row['paid'];
    if ($balance > 0) $outstanding += $balance;
}
$stats['outstanding'] = $outstanding;

// Recent invoices
$stmt = $pdo->query("
    SELECT i.id, i.invoice_number, i.status, i.due_date, c.name as client_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    ORDER BY i.created_at DESC
    LIMIT 5
");
$stats['recent'] = $stmt->fetchAll();

jsonResponse($stats);
