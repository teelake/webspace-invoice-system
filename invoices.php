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
<div class="filters-bar">
    <input type="search" id="invoiceSearch" class="filter-input" placeholder="Search by invoice # or client..." value="">
    <select id="invoiceStatusFilter" class="filter-input" style="max-width:140px">
        <option value="">All statuses</option>
        <option value="draft">Draft</option>
        <option value="unpaid">Unpaid</option>
        <option value="paid">Paid</option>
        <option value="cancelled">Cancelled</option>
    </select>
    <button type="button" class="btn btn-secondary" onclick="applyInvoiceFilters()">Search</button>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Invoice #</th>
                <th>Client</th>
                <th>Status</th>
                <th>Total</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="invoicesTable">
            <tr><td colspan="7" class="text-muted">Loading...</td></tr>
        </tbody>
    </table>
</div>
<div class="pagination-bar" id="invoicePagination"></div>
</div>

<script>
const base = '<?= APP_URL ?>/api';
let invoicePage = 1;
let invoiceTotalPages = 1;

function buildInvoiceUrl() {
    const params = new URLSearchParams();
    params.set('page', invoicePage);
    params.set('limit', 20);
    const q = document.getElementById('invoiceSearch').value.trim();
    if (q) params.set('search', q);
    const status = document.getElementById('invoiceStatusFilter').value;
    if (status) params.set('status', status);
    return base + '/invoices.php?' + params.toString();
}

async function loadInvoices() {
    const tbody = document.getElementById('invoicesTable');
    tbody.innerHTML = '<tr><td colspan="7" class="text-muted">Loading...</td></tr>';
    const data = await fetch(buildInvoiceUrl()).then(r => r.json());
    const list = data.items || [];
    invoiceTotalPages = data.pages || 1;
    const total = data.total || 0;
    const from = total ? (invoicePage - 1) * (data.limit || 20) + 1 : 0;
    const to = Math.min(invoicePage * (data.limit || 20), total);
    tbody.innerHTML = list.length ? list.map(inv => `
        <tr>
            <td>${inv.id}</td>
            <td>${inv.invoice_number}</td>
            <td>${inv.client_company_name ? inv.client_company_name + ' (' + (inv.client_name || '') + ')' : (inv.client_name || '-')}</td>
            <td>
                <select class="status-select" data-id="${inv.id}" onchange="updateStatus(${inv.id}, this.value)">
                    <option value="draft" ${inv.status === 'draft' ? 'selected' : ''}>Draft</option>
                    <option value="unpaid" ${inv.status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
                    <option value="paid" ${inv.status === 'paid' ? 'selected' : ''}>Paid</option>
                    <option value="cancelled" ${inv.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
            <td>${typeof formatMoney === 'function' ? formatMoney(inv.total) : 'NGN ' + Number(inv.total).toLocaleString()}</td>
            <td>${inv.due_date}</td>
            <td>
                <a href="invoice-view.php?id=${inv.id}" class="btn btn-sm btn-secondary">View</a>
                <a href="invoice-edit.php?id=${inv.id}" class="btn btn-sm">Edit</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">📄</div><div class="empty-state-title">No invoices yet</div><div class="empty-state-text">Create your first invoice to get started</div><a href="invoice-edit.php" class="btn btn-primary" style="margin-top:1rem">+ New Invoice</a></div></td></tr>';
    const pagEl = document.getElementById('invoicePagination');
    if (total > 0) {
        pagEl.innerHTML = `
            <span class="pagination-info">Showing ${from}–${to} of ${total}</span>
            <div class="pagination-btns">
                <button type="button" class="btn btn-secondary btn-page" onclick="goInvoicePage(1)" ${invoicePage <= 1 ? 'disabled' : ''}>First</button>
                <button type="button" class="btn btn-secondary btn-page" onclick="goInvoicePage(${invoicePage - 1})" ${invoicePage <= 1 ? 'disabled' : ''}>Prev</button>
                <span class="pagination-info" style="align-self:center">Page ${invoicePage} of ${invoiceTotalPages}</span>
                <button type="button" class="btn btn-secondary btn-page" onclick="goInvoicePage(${invoicePage + 1})" ${invoicePage >= invoiceTotalPages ? 'disabled' : ''}>Next</button>
                <button type="button" class="btn btn-secondary btn-page" onclick="goInvoicePage(${invoiceTotalPages})" ${invoicePage >= invoiceTotalPages ? 'disabled' : ''}>Last</button>
            </div>
        `;
    } else {
        pagEl.innerHTML = '';
    }
}

function goInvoicePage(p) {
    invoicePage = Math.max(1, Math.min(p, invoiceTotalPages));
    loadInvoices();
}

function applyInvoiceFilters() {
    invoicePage = 1;
    loadInvoices();
}

document.getElementById('invoiceSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') applyInvoiceFilters();
});

async function updateStatus(id, status) {
    try {
        const res = await fetch(base + '/invoices.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.error || 'Failed');
        }
        loadInvoices();
    } catch (e) {
        alert(e.message || 'Failed to update status');
        loadInvoices();
    }
}

document.addEventListener('DOMContentLoaded', loadInvoices);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
