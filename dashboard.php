<?php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-value" id="statTotalInvoices">-</span>
        <span class="stat-label">Total Invoices</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" id="statOverdue">-</span>
        <span class="stat-label">Overdue</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" id="statOutstanding">-</span>
        <span class="stat-label">Outstanding (NGN)</span>
    </div>
    <div class="stat-card">
        <span class="stat-value" id="statPaid">-</span>
        <span class="stat-label">Total Collected (NGN)</span>
    </div>
</div>
<div class="dashboard-section">
    <div class="section-header">
        <h2>Recent Invoices</h2>
        <a href="invoice-edit.php" class="btn btn-primary">New Invoice</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="recentInvoices">
                <tr><td colspan="5" class="text-muted">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const base = '<?= APP_URL ?>/api';
    const [stats, invoices] = await Promise.all([
        fetch(base + '/stats.php').then(r => r.json()),
        fetch(base + '/invoices.php').then(r => r.json())
    ]);
    document.getElementById('statTotalInvoices').textContent = stats.total_invoices;
    document.getElementById('statOverdue').textContent = stats.overdue;
    document.getElementById('statOutstanding').textContent = formatMoney(stats.outstanding);
    document.getElementById('statPaid').textContent = formatMoney(stats.total_paid);
    const tbody = document.getElementById('recentInvoices');
    const recent = (invoices || []).slice(0, 5);
    tbody.innerHTML = recent.length ? recent.map(inv => `
        <tr>
            <td>${inv.invoice_number}</td>
            <td>${inv.client_company_name ? inv.client_company_name + ' (' + (inv.client_name || '') + ')' : (inv.client_name || '-')}</td>
            <td><span class="badge badge-${inv.status}">${inv.status}</span></td>
            <td>${inv.due_date}</td>
            <td>
                <a href="invoice-view.php?id=${inv.id}" class="btn btn-sm btn-secondary">View</a>
                <a href="invoice-edit.php?id=${inv.id}" class="btn btn-sm">Edit</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5" class="text-muted">No invoices yet</td></tr>';
});
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
