<?php
$currentPage = 'clients';
$pageTitle = 'Clients';
$pageSubtitle = 'Your customer and company contacts';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="section-header">
    <h2>All Clients</h2>
    <a href="client-edit.php" class="btn btn-primary">+ Add Client</a>
</div>
<div class="content-card">
<div class="filters-bar">
    <input type="search" id="clientSearch" class="filter-input" placeholder="Search by name, company, email, phone..." value="">
    <button type="button" class="btn btn-secondary" onclick="applyClientFilters()">Search</button>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Company</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="clientsTable">
            <tr><td colspan="7" class="text-muted">Loading...</td></tr>
        </tbody>
    </table>
</div>
<div class="pagination-bar" id="clientPagination"></div>

<script>
const base = '<?= APP_URL ?>/api';
let clients = [];
let clientPage = 1;
let clientTotalPages = 1;

function buildClientUrl() {
    const params = new URLSearchParams();
    params.set('page', clientPage);
    params.set('limit', 20);
    const q = document.getElementById('clientSearch').value.trim();
    if (q) params.set('search', q);
    return base + '/clients.php?' + params.toString();
}

async function loadClients() {
    const tbody = document.getElementById('clientsTable');
    tbody.innerHTML = '<tr><td colspan="7" class="text-muted">Loading...</td></tr>';
    const data = await fetch(buildClientUrl()).then(r => r.json());
    clients = data.items || [];
    clientTotalPages = data.pages || 1;
    const total = data.total || 0;
    const from = total ? (clientPage - 1) * (data.limit || 20) + 1 : 0;
    const to = Math.min(clientPage * (data.limit || 20), total);
    tbody.innerHTML = clients.length ? clients.map(c => `
        <tr>
            <td>${c.id}</td>
            <td>${c.name}</td>
            <td>${c.company_name || '-'}</td>
            <td>${c.email || '-'}</td>
            <td>${c.phone || '-'}</td>
            <td>${(c.address || '').substring(0, 40)}${(c.address || '').length > 40 ? '...' : ''}</td>
            <td>
                <a href="client-edit.php?id=${c.id}" class="btn btn-sm btn-secondary">Edit</a>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteClient(${c.id}, '${(c.name || '').replace(/'/g, "\\'")}')">Delete</button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">👥</div><div class="empty-state-title">No clients yet</div><div class="empty-state-text">Add your first client to create invoices</div><a href="client-edit.php" class="btn btn-primary" style="margin-top:1rem">+ Add Client</a></div></td></tr>';
    const pagEl = document.getElementById('clientPagination');
    if (total > 0) {
        pagEl.innerHTML = `
            <span class="pagination-info">Showing ${from}–${to} of ${total}</span>
            <div class="pagination-btns">
                <button type="button" class="btn btn-secondary btn-page" onclick="goClientPage(1)" ${clientPage <= 1 ? 'disabled' : ''}>First</button>
                <button type="button" class="btn btn-secondary btn-page" onclick="goClientPage(${clientPage - 1})" ${clientPage <= 1 ? 'disabled' : ''}>Prev</button>
                <span class="pagination-info" style="align-self:center">Page ${clientPage} of ${clientTotalPages}</span>
                <button type="button" class="btn btn-secondary btn-page" onclick="goClientPage(${clientPage + 1})" ${clientPage >= clientTotalPages ? 'disabled' : ''}>Next</button>
                <button type="button" class="btn btn-secondary btn-page" onclick="goClientPage(${clientTotalPages})" ${clientPage >= clientTotalPages ? 'disabled' : ''}>Last</button>
            </div>
        `;
    } else {
        pagEl.innerHTML = '';
    }
}

function goClientPage(p) {
    clientPage = Math.max(1, Math.min(p, clientTotalPages));
    loadClients();
}

function applyClientFilters() {
    clientPage = 1;
    loadClients();
}

document.getElementById('clientSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') applyClientFilters();
});

async function deleteClient(id, name) {
    if (!confirm(`Delete client "${name}"?`)) return;
    try {
        await fetch(base + '/clients.php?id=' + id, { method: 'DELETE' });
        loadClients();
        if (typeof showToast === 'function') showToast('Client deleted', 'success');
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed to delete', 'error');
        else alert(err.message || 'Failed to delete');
    }
}

document.addEventListener('DOMContentLoaded', loadClients);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
