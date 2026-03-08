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
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="statTotalInvoices">-</span>
            <span class="stat-label">Total Invoices</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="statOverdue">-</span>
            <span class="stat-label">Overdue</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="statOutstanding">-</span>
            <span class="stat-label">Outstanding</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="statPaid">-</span>
            <span class="stat-label">Total Collected</span>
        </div>
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
    const [stats, invoicesRes] = await Promise.all([
        fetch(base + '/stats.php').then(r => r.json()),
        fetch(base + '/invoices.php?limit=5&page=1').then(r => r.json())
    ]);
    document.getElementById('statTotalInvoices').textContent = stats.total_invoices;
    document.getElementById('statOverdue').textContent = stats.overdue;
    document.getElementById('statOutstanding').textContent = formatMoney(stats.outstanding);
    document.getElementById('statPaid').textContent = formatMoney(stats.total_paid);
    const tbody = document.getElementById('recentInvoices');
    const invoices = invoicesRes?.items ?? invoicesRes ?? [];
    const recent = Array.isArray(invoices) ? invoices.slice(0, 5) : [];
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
