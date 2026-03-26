<?php
$currentPage = 'clients';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pageTitle = 'Edit Client';
    $pageSubtitle = 'Update client details';
} else {
    $pageTitle = 'Add Client';
    $pageSubtitle = 'Create a new client';
}
require_once __DIR__ . '/includes/layout.php';
?>
<div class="content-card" style="padding: 1.5rem; max-width: 560px;">
    <form id="clientForm">
        <input type="hidden" id="clientId" value="<?= $id ?>">
        <div class="form-group">
            <label for="clientName">Contact Name *</label>
            <input type="text" id="clientName" name="name" required placeholder="e.g. John Doe">
        </div>
        <div class="form-group">
            <label for="clientCompany">Company / Business Name</label>
            <input type="text" id="clientCompany" name="company_name" placeholder="e.g. Acme Ltd">
        </div>
        <div class="form-group">
            <label for="clientEmail">Email</label>
            <input type="email" id="clientEmail" name="email" placeholder="email@example.com">
        </div>
        <div class="form-group">
            <label for="clientPhone">Phone</label>
            <input type="text" id="clientPhone" name="phone" placeholder="+234 800 000 0000">
        </div>
        <div class="form-group">
            <label for="clientAddress">Address</label>
            <textarea id="clientAddress" name="address" rows="2" placeholder="Full address"></textarea>
        </div>
        <div class="form-group">
            <label for="clientNotes">Notes</label>
            <textarea id="clientNotes" name="notes" rows="2" placeholder="Internal notes (not shown on invoice)"></textarea>
        </div>
        <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-primary">Save Client</button>
            <a href="clients" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const base = '<?= APP_URL ?>/api';
const clientId = <?= $id ?>;

async function loadClient() {
    if (!clientId) return;
    const c = await fetch(base + '/clients.php?id=' + clientId).then(r => r.json());
    document.getElementById('clientName').value = c.name || '';
    document.getElementById('clientCompany').value = c.company_name || '';
    document.getElementById('clientEmail').value = c.email || '';
    document.getElementById('clientPhone').value = c.phone || '';
    document.getElementById('clientAddress').value = c.address || '';
    document.getElementById('clientNotes').value = c.notes || '';
}

document.getElementById('clientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    // "0" is truthy as a string — must use numeric check or POST is mistaken for PUT
    const id = parseInt(document.getElementById('clientId').value, 10) || 0;
    const isEdit = id > 0;
    const data = {
        name: document.getElementById('clientName').value.trim(),
        company_name: document.getElementById('clientCompany').value.trim(),
        email: document.getElementById('clientEmail').value.trim(),
        phone: document.getElementById('clientPhone').value.trim(),
        address: document.getElementById('clientAddress').value.trim(),
        notes: document.getElementById('clientNotes').value.trim()
    };
    try {
        const res = isEdit
            ? await fetch(base + '/clients.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...data, id })
            })
            : await fetch(base + '/clients.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
        const body = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(body.error || 'Request failed (' + res.status + ')');
        }
        if (typeof showToast === 'function') showToast('Client saved', 'success');
        else alert('Client saved');
        window.location.href = 'clients';
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed to save', 'error');
        else alert(err.message || 'Failed to save');
    }
});

document.addEventListener('DOMContentLoaded', loadClient);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
