<?php
if (!empty($_GET['new'])) {
    header('Location: invoice-edit.php');
    exit;
}
$currentPage = 'invoices';
$pageTitle = 'Invoices';
$pageSubtitle = 'Manage and track all your invoices';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="section-header">
    <h2>All Invoices</h2>
    <a href="invoice-edit.php" class="btn btn-primary">+ New Invoice</a>
</div>
<div class="content-card">
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Client</th>
                <th>Status</th>
                <th>Total</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="invoicesTable">
            <tr><td colspan="6" class="text-muted">Loading...</td></tr>
        </tbody>
    </table>
</div>
</div>

<script>
const base = '<?= APP_URL ?>/api';
async function loadInvoices() {
    const list = await fetch(base + '/invoices.php').then(r => r.json());
    const tbody = document.getElementById('invoicesTable');
    tbody.innerHTML = (list || []).length ? list.map(inv => `
        <tr>
            <td>${inv.invoice_number}</td>
            <td>${inv.client_company_name ? inv.client_company_name + ' (' + (inv.client_name || '') + ')' : (inv.client_name || '-')}</td>
            <td><span class="badge badge-${inv.status}">${inv.status}</span></td>
            <td>${typeof formatMoney === 'function' ? formatMoney(inv.total) : 'NGN ' + Number(inv.total).toLocaleString()}</td>
            <td>${inv.due_date}</td>
            <td>
                <a href="invoice-view.php?id=${inv.id}" class="btn btn-sm btn-secondary">View</a>
                <a href="invoice-edit.php?id=${inv.id}" class="btn btn-sm">Edit</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon">📄</div><div class="empty-state-title">No invoices yet</div><div class="empty-state-text">Create your first invoice to get started</div><a href="invoice-edit.php" class="btn btn-primary" style="margin-top:1rem">+ New Invoice</a></div></td></tr>';
}
document.addEventListener('DOMContentLoaded', loadInvoices);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
