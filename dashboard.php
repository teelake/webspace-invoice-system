<?php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of your invoices and revenue';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="welcome-block">
    <h2>Welcome back, <?= htmlspecialchars($user['name'] ?? '') ?>!</h2>
    <p>Here's what's happening with your invoices today.</p>
</div>
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
            <td><span class="badge badge-${inv.status === 'unpaid' && inv.due_date && inv.due_date < new Date().toISOString().slice(0,10) ? 'overdue' : inv.status}">${inv.status === 'unpaid' && inv.due_date && inv.due_date < new Date().toISOString().slice(0,10) ? 'Overdue' : (inv.status ? inv.status.charAt(0).toUpperCase() + inv.status.slice(1) : 'Draft')}</span></td>
            <td>${inv.due_date}</td>
            <td>
                <a href="invoice-view.php?id=${inv.id}" class="btn btn-sm btn-secondary">View</a>
                <a href="invoice-edit.php?id=${inv.id}" class="btn btn-sm">Edit</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5"><div class="empty-state"><div class="empty-state-icon">📄</div><div class="empty-state-title">No invoices yet</div><div class="empty-state-text">Create your first invoice to get started</div><a href="invoice-edit.php" class="btn btn-primary" style="margin-top:1rem">+ New Invoice</a></div></td></tr>';
});
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
