<?php
$currentPage = 'invoices';
$pageTitle = 'Edit Invoice';
$id = (int)($_GET['id'] ?? 0);
if ($id) $pageTitle = 'Edit Invoice'; else $pageTitle = 'New Invoice';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="invoice-form">
    <form id="invoiceForm">
        <input type="hidden" id="invoiceId" value="<?= $id ?>">
        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label>Client *</label>
                <select id="clientId" required>
                    <option value="">Select client...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Invoice #</label>
                <input type="text" id="invoiceNumber" placeholder="Auto-generated">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="status">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Issue Date</label>
                <input type="date" id="issueDate" required>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" id="dueDate" required>
            </div>
            <div class="form-group">
                <label>Payment Type</label>
                <select id="paymentType">
                    <option value="full">Full Payment</option>
                    <option value="installment">Installment</option>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Terms</label>
                <select id="paymentTermsId">
                    <option value="">-- Select --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Template</label>
                <select id="templateId">
                </select>
            </div>
        </div>
        <h3 style="margin:1rem 0 0.5rem">Line Items</h3>
        <div class="table-wrap">
            <table class="data-table items-table">
                <thead>
                    <tr>
                        <th class="col-desc">Description</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-price">Unit Price</th>
                        <th class="col-amount">Amount</th>
                        <th class="col-actions"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="addItem()" style="margin-top:0.5rem">+ Add Item</button>
        <div class="invoice-totals">
            <div class="total-row"><span>Subtotal</span><span id="subtotal">NGN 0.00</span></div>
            <div class="total-row"><span>Tax (<span id="taxLabel">7.5</span>%)</span><span id="taxAmount">NGN 0.00</span></div>
            <div class="total-row grand"><span>Total</span><span id="grandTotal">NGN 0.00</span></div>
        </div>
        <div id="paymentsSection" style="display:none; margin-top:1rem">
            <h3>Payments (Installments)</h3>
            <div id="paymentsList"></div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addPayment()">+ Add Payment</button>
        </div>
        <div class="form-group" style="margin-top:1rem">
            <label>Notes</label>
            <textarea id="notes" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label>Terms & Conditions</label>
            <textarea id="termsConditions" rows="2"></textarea>
        </div>
        <div style="margin-top:1.5rem; display:flex; gap:0.5rem; flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">Save Invoice</button>
            <a href="invoices.php" class="btn btn-secondary">Cancel</a>
            <?php if ($id): ?>
            <a href="invoice-view.php?id=<?= $id ?>" class="btn btn-secondary">View</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
const base = '<?= APP_URL ?>/api';
let clients = [], paymentTerms = [], templates = [], company = {};
let itemIndex = 0, paymentIndex = 0;

async function loadData() {
    [clients, paymentTerms, templates, company] = await Promise.all([
        fetch(base + '/clients.php').then(r => r.json()),
        fetch(base + '/payment-terms.php').then(r => r.json()),
        fetch(base + '/templates.php').then(r => r.json()),
        fetch(base + '/company.php').then(r => r.json())
    ]);
    const sel = document.getElementById('clientId');
    sel.innerHTML = '<option value="">Select client...</option>' + clients.map(c => {
        const label = c.company_name ? `${c.company_name} (${c.name})` : c.name;
        return `<option value="${c.id}">${label}</option>`;
    }).join('');
    const pt = document.getElementById('paymentTermsId');
    pt.innerHTML = '<option value="">-- Select --</option>' + (paymentTerms || []).map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    const tmpl = document.getElementById('templateId');
    tmpl.innerHTML = (templates || []).map(t => `<option value="${t.id}" ${t.is_default ? 'selected' : ''}>${t.name}</option>`).join('');
    document.getElementById('taxLabel').textContent = company.tax_rate || 7.5;
}

function addItem(desc = '', qty = 1, price = 0) {
    const tbody = document.getElementById('itemsBody');
    const id = 'item' + (itemIndex++);
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td class="col-desc"><input type="text" id="${id}_desc" value="${desc}" placeholder="Service description" onchange="calcTotals()"></td>
        <td class="col-qty"><input type="number" id="${id}_qty" value="${qty}" min="0.01" step="0.01" onchange="calcRow('${id}')"></td>
        <td class="col-price"><input type="number" id="${id}_price" value="${price}" min="0" step="0.01" onchange="calcRow('${id}')"></td>
        <td class="col-amount"><input type="text" id="${id}_amount" readonly class="amount-display"></td>
        <td class="col-actions"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove();calcTotals()">&times;</button></td>
    `;
    tbody.appendChild(row);
    calcRow(id);
}

function calcRow(id) {
    const qty = parseFloat(document.getElementById(id + '_qty')?.value || 0);
    const price = parseFloat(document.getElementById(id + '_price')?.value || 0);
    const amount = qty * price;
    const amtEl = document.getElementById(id + '_amount');
    if (amtEl) amtEl.value = amount.toFixed(2);
    calcTotals();
}

function calcTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.col-qty input')?.value || 0);
        const price = parseFloat(tr.querySelector('.col-price input')?.value || 0);
        subtotal += qty * price;
    });
    const taxRate = parseFloat(company.tax_rate || 7.5) / 100;
    const tax = subtotal * taxRate;
    const total = subtotal + tax;
    document.getElementById('subtotal').textContent = 'NGN ' + subtotal.toLocaleString('en-NG', { minimumFractionDigits: 2 });
    document.getElementById('taxAmount').textContent = 'NGN ' + tax.toLocaleString('en-NG', { minimumFractionDigits: 2 });
    document.getElementById('grandTotal').textContent = 'NGN ' + total.toLocaleString('en-NG', { minimumFractionDigits: 2 });
}

function addPayment(amount = 0, date = '', notes = '') {
    const div = document.getElementById('paymentsList');
    const id = 'pay' + (paymentIndex++);
    const d = date || new Date().toISOString().slice(0, 10);
    const el = document.createElement('div');
    el.className = 'form-row';
    el.style.marginBottom = '0.5rem';
    el.innerHTML = `
        <input type="number" placeholder="Amount" value="${amount}" step="0.01" id="${id}_amt" style="max-width:120px">
        <input type="date" value="${d}" id="${id}_date" style="max-width:140px">
        <input type="text" placeholder="Notes" value="${notes}" id="${id}_notes" style="flex:1">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">Remove</button>
    `;
    div.appendChild(el);
}

function getItems() {
    const items = [];
    document.querySelectorAll('.item-row').forEach(tr => {
        const desc = tr.querySelector('.col-desc input')?.value?.trim();
        if (!desc) return;
        const qty = parseFloat(tr.querySelector('.col-qty input')?.value || 0);
        const price = parseFloat(tr.querySelector('.col-price input')?.value || 0);
        items.push({ description: desc, quantity: qty, unit_price: price, amount: qty * price });
    });
    return items;
}

function getPayments() {
    const payments = [];
    document.querySelectorAll('#paymentsList > div').forEach(div => {
        const amt = parseFloat(div.querySelector('input[type="number"]')?.value || 0);
        if (amt <= 0) return;
        const date = div.querySelector('input[type="date"]')?.value || '';
        const notes = div.querySelector('input[type="text"]')?.value || '';
        payments.push({ amount: amt, payment_date: date, notes });
    });
    return payments;
}

document.getElementById('paymentType').addEventListener('change', function() {
    document.getElementById('paymentsSection').style.display = this.value === 'installment' ? 'block' : 'none';
});

document.getElementById('invoiceForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('invoiceId').value;
    const isEdit = id && id !== '0';
    const items = getItems();
    if (!items.length) { alert('Add at least one line item'); return; }
    const clientId = document.getElementById('clientId').value;
    if (!clientId) { alert('Select a client'); return; }
    const data = {
        client_id: clientId,
        invoice_number: document.getElementById('invoiceNumber').value || undefined,
        status: document.getElementById('status').value,
        payment_type: document.getElementById('paymentType').value,
        payment_terms_id: document.getElementById('paymentTermsId').value || null,
        issue_date: document.getElementById('issueDate').value,
        due_date: document.getElementById('dueDate').value,
        template_id: document.getElementById('templateId').value,
        notes: document.getElementById('notes').value,
        terms_conditions: document.getElementById('termsConditions').value,
        items
    };
    if (data.payment_type === 'installment') data.payments = getPayments();
    try {
        const url = base + '/invoices.php';
        const method = isEdit ? 'PUT' : 'POST';
        if (isEdit) data.id = id;
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const inv = await res.json();
        if (!res.ok) throw new Error(inv.error || 'Failed');
        window.location.href = 'invoice-view.php?id=' + (inv.id || id);
    } catch (err) {
        alert(err.message || 'Failed to save');
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    await loadData();
    const id = document.getElementById('invoiceId').value;
    document.getElementById('issueDate').value = new Date().toISOString().slice(0, 10);
    const defaultTerms = paymentTerms.find(t => t.is_default);
    if (defaultTerms) {
        document.getElementById('paymentTermsId').value = defaultTerms.id;
        const due = new Date();
        due.setDate(due.getDate() + (defaultTerms.days || 0));
        document.getElementById('dueDate').value = due.toISOString().slice(0, 10);
    } else {
        const due = new Date();
        due.setDate(due.getDate() + 30);
        document.getElementById('dueDate').value = due.toISOString().slice(0, 10);
    }
    if (id) {
        const inv = await fetch(base + '/invoices.php?id=' + id).then(r => r.json());
        document.getElementById('clientId').value = inv.client_id;
        document.getElementById('invoiceNumber').value = inv.invoice_number;
        document.getElementById('status').value = inv.status;
        document.getElementById('paymentType').value = inv.payment_type;
        document.getElementById('paymentTermsId').value = inv.payment_terms_id || '';
        document.getElementById('issueDate').value = inv.issue_date;
        document.getElementById('dueDate').value = inv.due_date;
        document.getElementById('templateId').value = inv.template_id || 1;
        document.getElementById('notes').value = inv.notes || '';
        document.getElementById('termsConditions').value = inv.terms_conditions || '';
        (inv.items || []).forEach(it => addItem(it.description, it.quantity, it.unit_price));
        if (inv.payment_type === 'installment') {
            document.getElementById('paymentsSection').style.display = 'block';
            (inv.payments || []).forEach(p => addPayment(p.amount, p.payment_date, p.notes));
        }
    } else {
        addItem();
    }
});
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
