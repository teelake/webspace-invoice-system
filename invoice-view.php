<?php
$currentPage = 'invoices';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: invoices.php');
    exit;
}
$pageTitle = 'View Invoice';
$pageSubtitle = 'Invoice details and actions';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="invoice-actions no-print">
    <a href="invoice-edit.php?id=<?= $id ?>" class="btn btn-primary">Edit</a>
    <button type="button" class="btn btn-secondary" onclick="window.print()">Print</button>
    <a href="invoice-pdf.php?id=<?= $id ?>&print=1" class="btn btn-secondary" target="_blank">Download PDF</a>
    <button type="button" class="btn btn-secondary" onclick="openEmailModal()">Email</button>
    <a href="invoices.php" class="btn btn-secondary">Back to List</a>
</div>
<div id="invoiceDisplay" class="invoice-print">
    <p class="text-muted">Loading invoice...</p>
</div>

<div id="emailModal" class="modal-overlay" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3>Email Invoice</h3>
            <button type="button" class="btn btn-secondary btn-sm modal-close" onclick="closeEmailModal()" aria-label="Close">&times;</button>
        </div>
        <form id="emailForm" class="modal-body">
            <div class="form-group">
                <label>To (Email)</label>
                <input type="email" id="emailTo" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" id="emailSubject" value="Invoice from <?= htmlspecialchars(getCompanySettings()['company_name'] ?? 'Company') ?>">
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea id="emailBody" rows="4">Please find your invoice attached.</textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEmailModal()">Cancel</button>
            <button type="submit" form="emailForm" class="btn btn-primary">Send Email</button>
        </div>
    </div>
</div>

<script>
const base = '<?= APP_URL ?>/api';
const invoiceId = <?= $id ?>;

async function loadInvoice() {
    const inv = await fetch(base + '/invoices.php?id=' + invoiceId).then(r => r.json());
    const company = await fetch(base + '/company.php').then(r => r.json());
    const template = (inv.template_id && inv.template_config) ? inv.template_config : { accentColor: '#2563eb', layout: 'two-column' };
    const taxRate = parseFloat(company.tax_rate || 7.5);
    const subtotal = (inv.items || []).reduce((s, i) => s + parseFloat(i.amount || 0), 0);
    const tax = subtotal * (taxRate / 100);
    const total = subtotal + tax;
    const paid = (inv.payments || []).reduce((s, p) => s + parseFloat(p.amount || 0), 0);
    const balance = total - paid;

    const layout = template.layout || 'two-column';
    const accent = template.accentColor || '#2563eb';

    const today = new Date().toISOString().slice(0, 10);
    const isOverdue = inv.status === 'unpaid' && inv.due_date && inv.due_date < today;
    const statusLabel = isOverdue ? 'Overdue' : (inv.status ? inv.status.charAt(0).toUpperCase() + inv.status.slice(1) : 'Draft');
    const statusClass = isOverdue ? 'overdue' : (inv.status || 'draft');

    let html = `
    <div class="invoice-paper" style="max-width:800px; margin:0 auto; font-family:${template.fontFamily || 'system-ui'}; padding:2rem; background:#fff; border-radius:8px; border:1px solid #e2e8f0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
            <div>
                ${company.logo_url ? `<img src="${company.logo_url}" alt="Logo" style="max-height:80px; margin-bottom:0.5rem;">` : ''}
                <h2 style="margin:0; font-size:1.5rem; color:${accent}">${company.company_name || 'Company'}</h2>
                ${company.address ? `<p style="margin:0.25rem 0; color:#64748b; font-size:0.9rem;">${company.address}</p>` : ''}
                ${company.phone ? `<p style="margin:0; color:#64748b; font-size:0.9rem;">${company.phone}</p>` : ''}
                ${company.email ? `<p style="margin:0; color:#64748b; font-size:0.9rem;">${company.email}</p>` : ''}
            </div>
            <div style="text-align:right;">
                <h1 style="margin:0; font-size:1.75rem; color:${accent}">INVOICE</h1>
                <p style="margin:0.5rem 0; font-weight:600;">#${inv.invoice_number}</p>
                <p style="margin:0; color:#64748b; font-size:0.9rem;">Status: <span class="badge badge-${statusClass}">${statusLabel}</span></p>
            </div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:2rem; margin-bottom:2rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <h3 style="font-size:0.85rem; color:#64748b; margin-bottom:0.5rem;">BILL TO</h3>
                ${inv.client_company_name ? `<p style="margin:0; font-weight:600;">${inv.client_company_name}</p>` : ''}
                <p style="margin:0; font-weight:${inv.client_company_name ? '500' : '600'};">${inv.client_name || '-'}</p>
                ${inv.client_address ? `<p style="margin:0.25rem 0; font-size:0.9rem;">${inv.client_address}</p>` : ''}
                ${inv.client_email ? `<p style="margin:0; font-size:0.9rem;">${inv.client_email}</p>` : ''}
                ${inv.client_phone ? `<p style="margin:0; font-size:0.9rem;">${inv.client_phone}</p>` : ''}
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <p style="margin:0;"><strong>Issue Date:</strong> ${inv.issue_date}</p>
                <p style="margin:0.25rem 0;"><strong>Due Date:</strong> ${inv.due_date}</p>
                ${inv.payment_terms_name ? `<p style="margin:0;"><strong>Terms:</strong> ${inv.payment_terms_name}</p>` : ''}
            </div>
        </div>
        <table style="width:100%; border-collapse:collapse; margin-bottom:1.5rem;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:2px solid ${accent};">
                    <th style="padding:0.75rem; text-align:left;">Description</th>
                    <th style="padding:0.75rem; text-align:right;">Qty</th>
                    <th style="padding:0.75rem; text-align:right;">Unit Price</th>
                    <th style="padding:0.75rem; text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ${(inv.items || []).map(i => `
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:0.75rem;">${i.description}</td>
                    <td style="padding:0.75rem; text-align:right;">${parseFloat(i.quantity).toLocaleString()}</td>
                    <td style="padding:0.75rem; text-align:right;">NGN ${parseFloat(i.unit_price).toLocaleString('en-NG', {minimumFractionDigits:2})}</td>
                    <td style="padding:0.75rem; text-align:right;">NGN ${parseFloat(i.amount).toLocaleString('en-NG', {minimumFractionDigits:2})}</td>
                </tr>
                `).join('')}
            </tbody>
        </table>
        <div style="max-width:280px; margin-left:auto;">
            <div style="display:flex; justify-content:space-between; padding:0.35rem 0;"><span>Subtotal</span><span>NGN ${subtotal.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            <div style="display:flex; justify-content:space-between; padding:0.35rem 0;"><span>Tax (${taxRate}%)</span><span>NGN ${tax.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            ${inv.payment_type === 'installment' && (inv.payments || []).length ? `
            <div style="display:flex; justify-content:space-between; padding:0.35rem 0;"><span>Total</span><span>NGN ${total.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            <div style="display:flex; justify-content:space-between; padding:0.35rem 0;"><span>Paid</span><span>NGN ${paid.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            <div style="display:flex; justify-content:space-between; padding:0.5rem 0; font-weight:700; border-top:2px solid #e2e8f0;"><span>Balance Due</span><span>NGN ${balance.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            ` : `
            <div style="display:flex; justify-content:space-between; padding:0.5rem 0; font-weight:700; font-size:1.1rem; border-top:2px solid ${accent};"><span>Total</span><span>NGN ${total.toLocaleString('en-NG', {minimumFractionDigits:2})}</span></div>
            `}
        </div>
        ${(company.bank_name || company.bank_account_number) ? `
        <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
            <p style="font-size:0.8rem; color:#64748b; margin:0 0 0.5rem 0; text-transform:uppercase; letter-spacing:0.05em;">Payment Details</p>
            ${company.bank_name ? `<p style="margin:0;"><strong>Bank:</strong> ${company.bank_name}</p>` : ''}
            ${company.bank_account_name ? `<p style="margin:0.25rem 0;"><strong>Account Name:</strong> ${company.bank_account_name}</p>` : ''}
            ${company.bank_account_number ? `<p style="margin:0;"><strong>Account Number:</strong> ${company.bank_account_number}</p>` : ''}
        </div>
        ` : ''}
        ${inv.notes ? `<p style="margin-top:1.5rem; color:#64748b; font-size:0.9rem;">${inv.notes}</p>` : ''}
        ${inv.terms_conditions ? `<p style="margin-top:1rem; font-size:0.85rem; color:#64748b;">${inv.terms_conditions}</p>` : ''}
    </div>
    `;
    document.getElementById('invoiceDisplay').innerHTML = html;
    document.getElementById('emailTo').value = inv.client_email || '';
}

function openEmailModal() {
    document.getElementById('emailModal').style.display = 'flex';
}
function closeEmailModal() {
    document.getElementById('emailModal').style.display = 'none';
}

document.getElementById('emailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const res = await fetch(base + '/invoice-email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                invoice_id: invoiceId,
                to: document.getElementById('emailTo').value,
                subject: document.getElementById('emailSubject').value,
                body: document.getElementById('emailBody').value
            })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed');
        alert('Email sent successfully');
        closeEmailModal();
    } catch (err) {
        alert(err.message || 'Failed to send email');
    }
});

document.addEventListener('DOMContentLoaded', loadInvoice);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
