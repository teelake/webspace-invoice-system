<?php
$currentPage = 'settings';
$pageTitle = 'Settings';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="settings-form">
    <div class="card">
        <h2 style="margin-bottom:1rem">Company Info</h2>
        <form id="companyForm">
        <div class="form-row">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" id="companyName" name="company_name">
            </div>
            <div class="form-group">
                <label>Logo URL</label>
                <input type="url" id="logoUrl" name="logo_url" placeholder="https://...">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea id="address" name="address" rows="2"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label>Website</label>
                <input type="url" id="website" name="website" placeholder="https://...">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Currency</label>
                <input type="text" id="currency" name="currency" placeholder="NGN">
            </div>
            <div class="form-group">
                <label>Tax Label</label>
                <input type="text" id="taxLabel" name="tax_label" placeholder="VAT">
            </div>
            <div class="form-group">
                <label>Tax Rate (%)</label>
                <input type="number" id="taxRate" name="tax_rate" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Invoice Prefix</label>
                <input type="text" id="invoicePrefix" name="invoice_prefix" placeholder="INV">
            </div>
            <div class="form-group">
                <label>Next Invoice #</label>
                <input type="number" id="invoiceNextNumber" name="invoice_next_number" min="1">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Company Settings</button>
    </form>
    </div>

    <div class="card">
    <h2 style="margin-bottom:1rem">Payment Terms</h2>
    <div class="section-header">
        <span></span>
        <button type="button" class="btn btn-primary" onclick="openTermModal()">Add Term</button>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Days</th>
                    <th>Description</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="termsTable">
                <tr><td colspan="5" class="text-muted">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    </div>
</div>

<div id="termModal" class="modal-overlay" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3 id="termModalTitle">Add Payment Term</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeTermModal()">&times;</button>
        </div>
        <form id="termForm" class="modal-body">
            <input type="hidden" id="termId">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" id="termName" required placeholder="e.g. Net 30">
            </div>
            <div class="form-group">
                <label>Days</label>
                <input type="number" id="termDays" min="0" value="0" placeholder="0">
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" id="termDesc" placeholder="e.g. Payment due within 30 days">
            </div>
            <div class="form-group">
                <label><input type="checkbox" id="termDefault"> Set as default</label>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeTermModal()">Cancel</button>
            <button type="submit" form="termForm" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>

<script>
const base = '<?= APP_URL ?>/api';

async function loadCompany() {
    const c = await fetch(base + '/company.php').then(r => r.json());
    document.getElementById('companyName').value = c.company_name || '';
    document.getElementById('logoUrl').value = c.logo_url || '';
    document.getElementById('address').value = c.address || '';
    document.getElementById('phone').value = c.phone || '';
    document.getElementById('email').value = c.email || '';
    document.getElementById('website').value = c.website || '';
    document.getElementById('currency').value = c.currency || 'NGN';
    document.getElementById('taxLabel').value = c.tax_label || 'VAT';
    document.getElementById('taxRate').value = c.tax_rate ?? 7.5;
    document.getElementById('invoicePrefix').value = c.invoice_prefix || 'INV';
    document.getElementById('invoiceNextNumber').value = c.invoice_next_number ?? 1;
}

async function loadTerms() {
    const terms = await fetch(base + '/payment-terms.php').then(r => r.json());
    const tbody = document.getElementById('termsTable');
    tbody.innerHTML = (terms || []).length ? terms.map(t => `
        <tr>
            <td>${t.name}</td>
            <td>${t.days}</td>
            <td>${t.description || '-'}</td>
            <td>${t.is_default ? 'Yes' : ''}</td>
            <td>
                <button type="button" class="btn btn-sm btn-secondary" onclick="editTerm(${t.id})">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteTerm(${t.id})">Delete</button>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5" class="text-muted">No payment terms yet.</td></tr>';
}

document.getElementById('companyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        company_name: document.getElementById('companyName').value,
        logo_url: document.getElementById('logoUrl').value,
        address: document.getElementById('address').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        website: document.getElementById('website').value,
        currency: document.getElementById('currency').value,
        tax_label: document.getElementById('taxLabel').value,
        tax_rate: parseFloat(document.getElementById('taxRate').value) || 0,
        invoice_prefix: document.getElementById('invoicePrefix').value,
        invoice_next_number: parseInt(document.getElementById('invoiceNextNumber').value) || 1
    };
    try {
        await fetch(base + '/company.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        alert('Settings saved');
    } catch (err) {
        alert(err.message || 'Failed to save');
    }
});

function openTermModal(editId = null) {
    document.getElementById('termModal').style.display = 'flex';
    document.getElementById('termModalTitle').textContent = editId ? 'Edit Payment Term' : 'Add Payment Term';
    document.getElementById('termForm').reset();
    document.getElementById('termId').value = editId || '';
    document.getElementById('termDays').value = 0;
}

function closeTermModal() {
    document.getElementById('termModal').style.display = 'none';
}

async function editTerm(id) {
    const terms = await fetch(base + '/payment-terms.php').then(r => r.json());
    const t = terms.find(x => x.id == id);
    if (!t) return;
    openTermModal(id);
    document.getElementById('termName').value = t.name;
    document.getElementById('termDays').value = t.days;
    document.getElementById('termDesc').value = t.description || '';
    document.getElementById('termDefault').checked = !!t.is_default;
}

document.getElementById('termForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('termId').value;
    const data = {
        name: document.getElementById('termName').value.trim(),
        days: parseInt(document.getElementById('termDays').value) || 0,
        description: document.getElementById('termDesc').value.trim(),
        is_default: document.getElementById('termDefault').checked
    };
    try {
        if (id) {
            data.id = id;
            await fetch(base + '/payment-terms.php', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        } else {
            await fetch(base + '/payment-terms.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        }
        closeTermModal();
        loadTerms();
    } catch (err) {
        alert(err.message || 'Failed');
    }
});

async function deleteTerm(id) {
    if (!confirm('Delete this payment term?')) return;
    try {
        await fetch(base + '/payment-terms.php?id=' + id, { method: 'DELETE' });
        loadTerms();
    } catch (err) {
        alert(err.message || 'Failed');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadCompany();
    loadTerms();
});
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
