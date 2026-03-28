<?php
/**
 * PDF export - uses browser print to PDF when no PDF library available
 * For production, consider adding TCPDF or Dompdf
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireTenantUser();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . APP_URL . '/invoices');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT i.*, c.name as client_name, c.company_name as client_company_name, c.email as client_email, c.phone as client_phone, c.address as client_address,
           pt.name as payment_terms_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    LEFT JOIN payment_terms pt ON i.payment_terms_id = pt.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if (!$inv) {
    header('Location: ' . APP_URL . '/invoices');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order, id");
$stmt->execute([$id]);
$inv['items'] = $stmt->fetchAll();
$stmt = $pdo->prepare("SELECT * FROM invoice_payments WHERE invoice_id = ? ORDER BY payment_date");
$stmt->execute([$id]);
$inv['payments'] = $stmt->fetchAll();

$company = getCompanySettings();
$stmt = $pdo->prepare("SELECT config FROM invoice_templates WHERE id = ?");
$stmt->execute([$inv['template_id'] ?? 1]);
$template = $stmt->fetch();
$tpl = $template ? json_decode($template['config'] ?? '{}', true) : [];

$taxRate = (float)($company['tax_rate'] ?? 7.5);
$subtotal = array_sum(array_column($inv['items'], 'amount'));
$tax = $subtotal * ($taxRate / 100);
$total = $subtotal + $tax;
$paid = array_sum(array_column($inv['payments'], 'amount'));
$balance = $total - $paid;
$accent = $tpl['accentColor'] ?? '#2563eb';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($inv['invoice_number']) ?></title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; padding: 1.5rem 2rem 2rem 1rem; color: #1e293b; }
        .invoice { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .header h1 { color: <?= $accent ?>; margin: 0; font-size: 1.75rem; }
        table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; }
        .totals { max-width: 280px; margin-left: auto; }
        .totals div { display: flex; justify-content: space-between; padding: 0.35rem 0; }
        .grand { font-weight: 700; font-size: 1.1rem; border-top: 2px solid <?= $accent ?>; padding-top: 0.5rem; margin-top: 0.5rem; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-unpaid { background: #dbeafe; color: #2563eb; }
        .badge-paid { background: #d1fae5; color: #059669; }
        .badge-overdue { background: #fee2e2; color: #dc2626; }
        .badge-cancelled { background: #f1f5f9; color: #64748b; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
<div class="invoice">
    <div class="header">
        <div>
            <?php if (!empty($company['logo_url'])): ?><img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="Logo" style="height:64px; width:auto; display:block; margin:0 0 0.35rem 0;"><?php else: ?><h2 style="margin:0; color:<?= $accent ?>"><?= htmlspecialchars($company['company_name'] ?? 'Company') ?></h2><?php endif; ?>
            <?php if (!empty($company['address'])): ?><p style="margin:0.15rem 0 0 0; color:#64748b;"><?= nl2br(htmlspecialchars($company['address'])) ?></p><?php endif; ?>
            <?php if (!empty($company['phone'])): ?><p style="margin:0; color:#64748b;"><?= htmlspecialchars($company['phone']) ?></p><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <h1>INVOICE</h1>
            <p style="font-weight:600;">#<?= htmlspecialchars($inv['invoice_number']) ?></p>
            <?php
            $statusLabel = $inv['status'];
            $statusClass = $inv['status'];
            if ($inv['status'] === 'unpaid' && $inv['due_date'] < date('Y-m-d')) {
                $statusLabel = 'Overdue';
                $statusClass = 'overdue';
            } else {
                $statusLabel = ucfirst($inv['status']);
            }
            ?>
            <p style="margin:0; color:#64748b; font-size:0.9rem;">Status: <span class="badge badge-<?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></p>
            <p style="margin:0.25rem 0 0 0; color:#64748b;"><?= $inv['issue_date'] ?> | Due: <?= $inv['due_date'] ?></p>
        </div>
    </div>
    <div style="margin-bottom:2rem;">
        <p style="color:#64748b; font-size:0.85rem; margin-bottom:0.25rem;">BILL TO</p>
        <?php if (!empty($inv['client_company_name'])): ?><p style="margin:0; font-weight:600;"><?= htmlspecialchars($inv['client_company_name']) ?></p><?php endif; ?>
        <p style="margin:0; font-weight:<?= !empty($inv['client_company_name']) ? '500' : '600' ?>;"><?= htmlspecialchars($inv['client_name'] ?? '-') ?></p>
        <?php if (!empty($inv['client_address'])): ?><p style="margin:0.25rem 0;"><?= nl2br(htmlspecialchars($inv['client_address'])) ?></p><?php endif; ?>
        <?php if (!empty($inv['client_email'])): ?><p style="margin:0;"><?= htmlspecialchars($inv['client_email']) ?></p><?php endif; ?>
    </div>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inv['items'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['description']) ?></td>
                <td class="text-right"><?= number_format($item['quantity'], 2) ?></td>
                <td class="text-right">NGN <?= number_format($item['unit_price'], 2) ?></td>
                <td class="text-right">NGN <?= number_format($item['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="totals">
        <div><span>Subtotal</span><span>NGN <?= number_format($subtotal, 2) ?></span></div>
        <div><span>Tax (<?= $taxRate ?>%)</span><span>NGN <?= number_format($tax, 2) ?></span></div>
        <?php if ($inv['payment_type'] === 'installment' && $paid > 0): ?>
        <div><span>Total</span><span>NGN <?= number_format($total, 2) ?></span></div>
        <div><span>Paid</span><span>NGN <?= number_format($paid, 2) ?></span></div>
        <div class="grand"><span>Balance Due</span><span>NGN <?= number_format($balance, 2) ?></span></div>
        <?php else: ?>
        <div class="grand"><span>Total</span><span>NGN <?= number_format($total, 2) ?></span></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($company['bank_name']) || !empty($company['bank_account_number'])): ?>
    <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
        <p style="font-size:0.8rem; color:#64748b; margin:0 0 0.5rem 0; text-transform:uppercase; letter-spacing:0.05em;">Payment Details</p>
        <?php if (!empty($company['bank_name'])): ?><p style="margin:0;"><strong>Bank:</strong> <?= htmlspecialchars($company['bank_name']) ?></p><?php endif; ?>
        <?php if (!empty($company['bank_account_name'])): ?><p style="margin:0.25rem 0;"><strong>Account Name:</strong> <?= htmlspecialchars($company['bank_account_name']) ?></p><?php endif; ?>
        <?php if (!empty($company['bank_account_number'])): ?><p style="margin:0;"><strong>Account Number:</strong> <?= htmlspecialchars($company['bank_account_number']) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($inv['notes'])): ?><p style="margin-top:1.5rem; color:#64748b;"><?= nl2br(htmlspecialchars($inv['notes'])) ?></p><?php endif; ?>
</div>
<script>
// Auto-trigger print dialog for PDF save
if (window.location.search.includes('print=1')) {
    window.onload = () => window.print();
}
</script>
</body>
</html>
