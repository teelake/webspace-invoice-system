<?php
$currentPage = 'clients';
$pageTitle = 'Clients';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="section-header">
    <h2>All Clients</h2>
    <button type="button" class="btn btn-primary" onclick="openClientModal()">Add Client</button>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Company</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="clientsTable">
            <tr><td colspan="6" class="text-muted">Loading...</td></tr>
        </tbody>
    </table>
</div>

<div id="clientModal" class="modal-overlay" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3 id="clientModalTitle">Add Client</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeClientModal()">&times;</button>
        </div>
        <form id="clientForm" class="modal-body">
            <input type="hidden" id="clientId" name="id">
            <div class="form-group">
                <label for="clientName">Contact Name *</label>
                <input type="text" id="clientName" name="name" required>
            </div>
            <div class="form-group">
                <label for="clientCompany">Company / Business Name</label>
                <input type="text" id="clientCompany" name="company_name" placeholder="e.g. Acme Ltd">
            </div>
            <div class="form-group">
                <label for="clientEmail">Email</label>
                <input type="email" id="clientEmail" name="email">
            </div>
            <div class="form-group">
                <label for="clientPhone">Phone</label>
                <input type="text" id="clientPhone" name="phone">
            </div>
            <div class="form-group">
                <label for="clientAddress">Address</label>
                <textarea id="clientAddress" name="address" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="clientNotes">Notes</label>
                <textarea id="clientNotes" name="notes" rows="2"></textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Cancel</button>
            <button type="submit" form="clientForm" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>

<script>
const base = '<?= APP_URL ?>/api';
let clients = [];

async function loadClients() {
    clients = await fetch(base + '/clients.php').then(r => r.json());
    const tbody = document.getElementById('clientsTable');
    tbody.innerHTML = clients.length ? clients.map(c => `
        <tr>
            <td>${c.name}</td>
            <td>${c.company_name || '-'}</td>
            <td>${c.email || '-'}</td>
            <td>${c.phone || '-'}</td>
            <td>${(c.address || '').substring(0, 40)}${(c.address || '').length > 40 ? '...' : ''}</td>
            <td>
                <button type="button" class="btn btn-sm btn-secondary" onclick="editClient(${c.id})">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteClient(${c.id}, '${(c.name || '').replace(/'/g, "\\'")}')">Delete</button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="6" class="text-muted">No clients yet. Add one to get started.</td></tr>';
}

function openClientModal(editId = null) {
    document.getElementById('clientModal').style.display = 'flex';
    document.getElementById('clientModalTitle').textContent = editId ? 'Edit Client' : 'Add Client';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = editId || '';
    if (editId) {
        const c = clients.find(x => x.id == editId);
        if (c) {
            document.getElementById('clientName').value = c.name;
            document.getElementById('clientCompany').value = c.company_name || '';
            document.getElementById('clientEmail').value = c.email || '';
            document.getElementById('clientPhone').value = c.phone || '';
            document.getElementById('clientAddress').value = c.address || '';
            document.getElementById('clientNotes').value = c.notes || '';
        }
    }
}

function closeClientModal() {
    document.getElementById('clientModal').style.display = 'none';
}

document.getElementById('clientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('clientId').value;
    const data = {
        name: document.getElementById('clientName').value.trim(),
        company_name: document.getElementById('clientCompany').value.trim(),
        email: document.getElementById('clientEmail').value.trim(),
        phone: document.getElementById('clientPhone').value.trim(),
        address: document.getElementById('clientAddress').value.trim(),
        notes: document.getElementById('clientNotes').value.trim()
    };
    try {
        if (id) {
            await fetch(base + '/clients.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...data, id })
            });
        } else {
            await fetch(base + '/clients.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
        }
        closeClientModal();
        loadClients();
    } catch (err) {
        alert(err.message || 'Failed to save');
    }
});

async function editClient(id) { openClientModal(id); }
async function deleteClient(id, name) {
    if (!confirm(`Delete client "${name}"?`)) return;
    try {
        await fetch(base + '/clients.php?id=' + id, { method: 'DELETE' });
        loadClients();
    } catch (err) {
        alert(err.message || 'Failed to delete');
    }
}

document.addEventListener('DOMContentLoaded', loadClients);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
