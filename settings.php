<?php
$currentPage = 'settings';
$pageTitle = 'Settings';
$pageSubtitle = 'Company info and payment terms';
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
                <label>Company Logo</label>
                <input type="file" id="logoFile" name="logo" accept="image/png,image/jpeg,image/gif,image/webp">
                <p class="form-hint">PNG, JPG, GIF or WebP. Max 2MB.</p>
                <div id="logoPreview" class="logo-preview" style="display:none; margin-top:0.5rem;">
                    <span class="logo-preview-label" id="logoPreviewLabel">Current logo (80px on invoice):</span>
                    <img id="logoPreviewImg" src="" alt="Logo preview" class="logo-preview-img">
                </div>
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
        <div class="form-group">
            <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Bank Account (for invoice)</label>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem;">Payment details shown on invoices</p>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Bank Name</label>
                <input type="text" id="bankName" name="bank_name" placeholder="e.g. GTBank">
            </div>
            <div class="form-group">
                <label>Account Name</label>
                <input type="text" id="bankAccountName" name="bank_account_name" placeholder="e.g. Acme Ltd">
            </div>
            <div class="form-group">
                <label>Account Number</label>
                <input type="text" id="bankAccountNumber" name="bank_account_number" placeholder="e.g. 0123456789">
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
        <button type="button" class="btn btn-primary" onclick="openTermModal()">+ Add Term</button>
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
            <button type="button" class="btn btn-secondary btn-sm modal-close" onclick="closeTermModal()" aria-label="Close">&times;</button>
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
    document.getElementById('logoFile').value = '';
    document.getElementById('address').value = c.address || '';
    document.getElementById('phone').value = c.phone || '';
    document.getElementById('email').value = c.email || '';
    document.getElementById('website').value = c.website || '';
    document.getElementById('bankName').value = c.bank_name || '';
    document.getElementById('bankAccountName').value = c.bank_account_name || '';
    document.getElementById('bankAccountNumber').value = c.bank_account_number || '';
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
    const logoInput = document.getElementById('logoFile');
    try {
        if (logoInput.files && logoInput.files[0]) {
            const fd = new FormData();
            fd.append('logo', logoInput.files[0]);
            const upRes = await fetch(base + '/company-logo.php', {
                method: 'POST',
                body: fd
            });
            const upData = await upRes.json();
            if (!upRes.ok) throw new Error(upData.error || 'Logo upload failed');
        }
        const data = {
            company_name: document.getElementById('companyName').value,
            address: document.getElementById('address').value,
            phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        website: document.getElementById('website').value,
        bank_name: document.getElementById('bankName').value,
        bank_account_name: document.getElementById('bankAccountName').value,
        bank_account_number: document.getElementById('bankAccountNumber').value,
        currency: document.getElementById('currency').value,
        tax_label: document.getElementById('taxLabel').value,
        tax_rate: parseFloat(document.getElementById('taxRate').value) || 0,
        invoice_prefix: document.getElementById('invoicePrefix').value,
        invoice_next_number: parseInt(document.getElementById('invoiceNextNumber').value) || 1
        };
        await fetch(base + '/company.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (typeof showToast === 'function') showToast('Settings saved', 'success');
        else alert('Settings saved');
        logoInput.value = '';
        loadCompany();
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed to save', 'error');
        else alert(err.message || 'Failed to save');
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
        if (typeof showToast === 'function') showToast('Payment term saved', 'success');
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed', 'error');
        else alert(err.message || 'Failed');
    }
});

async function deleteTerm(id) {
    if (!confirm('Delete this payment term?')) return;
    try {
        await fetch(base + '/payment-terms.php?id=' + id, { method: 'DELETE' });
        loadTerms();
        if (typeof showToast === 'function') showToast('Payment term deleted', 'success');
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed', 'error');
        else alert(err.message || 'Failed');
    }
}

function updateLogoPreview(url) {
    const preview = document.getElementById('logoPreview');
    const img = document.getElementById('logoPreviewImg');
    const label = document.getElementById('logoPreviewLabel');
    if (!url) {
        preview.style.display = 'none';
        return;
    }
    img.src = url;
    label.textContent = 'Current logo (80px on invoice):';
    img.style.display = 'block';
    img.onload = () => {
        preview.style.display = 'block';
    };
    img.onerror = () => {
        preview.style.display = 'none';
    };
}

document.getElementById('logoFile').addEventListener('change', async function() {
    if (this.files && this.files[0]) {
        const url = URL.createObjectURL(this.files[0]);
        document.getElementById('logoPreviewLabel').textContent = 'New logo preview:';
        updateLogoPreview(url);
    } else {
        const c = await fetch(base + '/company.php').then(r => r.json());
        updateLogoPreview(c.logo_url || null);
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    await loadCompany();
    loadTerms();
    const c = await fetch(base + '/company.php').then(r => r.json());
    updateLogoPreview(c.logo_url || null);
});
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
