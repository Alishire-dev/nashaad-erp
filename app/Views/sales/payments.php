<style>
    .payments-card { background:#fff; border-radius:8px; overflow:hidden; max-width:950px; }
    .payments-header {
        background:linear-gradient(135deg,#e88a2e,#d96f0f); color:#fff; padding:16px 24px;
        font-size:20px; font-weight:700; text-align:center;
    }
    .payments-body { padding:24px; }
    .payments-info { display:flex; gap:30px; flex-wrap:wrap; margin-bottom:20px; }
    .payments-info .block { flex:1; min-width:220px; }
    .payments-info .block h4 { margin:0 0 8px 0; color:#666; font-size:13px; }
    .payments-table th, .payments-table td { padding:10px; font-size:13px; }
    .pay-row { display:flex; gap:8px; align-items:flex-end; margin-bottom:8px; }
    .pay-row > * { flex:1; }
    .pay-row .remove-btn { flex:0 0 36px; }
    .computed-totals { display:flex; gap:20px; justify-content:flex-end; margin-top:16px; }
    .computed-totals .field { text-align:right; }
    .computed-totals label { color:#27ae60; font-weight:700; font-size:13px; display:block; }
    .computed-totals input { text-align:right; font-weight:700; width:140px; background:#f4f5f7; }
</style>

<div class="payments-card">
    <div class="payments-header">Payments</div>
    <div class="payments-body">
        <div class="payments-info">
            <div class="block">
                <h4>Customer Information</h4>
                <strong><?= esc($sale['customer_name'] ?? 'WALK-IN') ?></strong><br>
                Mobile: <?= esc($sale['customer_phone'] ?? '-') ?><br>
                Phone: <?= esc($sale['customer_phone'] ?? '-') ?><br>
                Email: <?= esc($sale['customer_email'] ?? '-') ?>
            </div>
            <div class="block">
                <h4>Sale Information</h4>
                Invoice #<?= esc($sale['invoice_no']) ?><br>
                Date: <?= esc($sale['sale_date']) ?><br>
                Sales Person: <?= esc($sale['sales_person_name'] ?? '-') ?><br>
                Grand Total: <?= number_format((float) $sale['grand_total'], 2) ?>
            </div>
            <div class="block">
                <h4>&nbsp;</h4>
                Paid Amount: <?= number_format((float) $sale['amount_paid'], 2) ?><br>
                Due Amount: <?= number_format((float) $sale['grand_total'] - (float) $sale['amount_paid'], 2) ?>
            </div>
        </div>

        <table class="payments-table" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th><th>Payment Date</th><th>Payment</th><th>Payment Type</th>
                    <th>Payment Note</th><th>Created by</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($p['payment_date']) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?></td>
                    <td><?= esc(ucfirst($p['payment_type'])) ?></td>
                    <td><?= esc($p['payment_note'] ?? '') ?></td>
                    <td><?= esc($p['created_by_name'] ?? '-') ?></td>
                    <td>
                        <form method="post" action="<?= site_url('sales/payments/delete/' . $p['id']) ?>"
                              onsubmit="return confirm('Delete this payment? Balance will be recalculated.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn" style="background:#c0392b; padding:4px 10px;">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <tr><td colspan="7" style="text-align:center; color:#999;">No Records Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h4 style="margin-top:24px;">Record New Payment</h4>
        <div class="form-group" style="max-width:250px;">
            <label>Branch</label>
            <select disabled><option>Main Branch</option></select>
        </div>

        <form method="post" action="<?= site_url('sales/payments/' . $sale['id'] . '/add') ?>" id="paymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="rows_json" id="rowsJson">

            <div id="payRows"></div>
            <button type="button" class="btn" style="background:#27ae60; padding:4px 12px; font-size:13px;" onclick="addPayRow()">+ Add Row</button>

            <div class="computed-totals">
                <div class="field">
                    <label>TOTAL RECEIVED</label>
                    <input type="text" id="totalReceived" disabled value="0.00">
                </div>
                <div class="field">
                    <label>CHANGE RETURN</label>
                    <input type="text" id="changeReturn" disabled value="0.00">
                </div>
            </div>

            <div style="text-align:right; margin-top:16px;">
                <a class="btn" style="background:#e88a2e;" href="<?= site_url('sales/list') ?>">Close ✕</a>
                <button class="btn green" type="submit">Save ✓</button>
            </div>
        </form>
    </div>
</div>

<script>
const dueAmount = <?= (float) $sale['grand_total'] - (float) $sale['amount_paid'] ?>;
let payRowCount = 0;

function addPayRow() {
    payRowCount++;
    const container = document.getElementById('payRows');
    const row = document.createElement('div');
    row.className = 'pay-row';
    row.dataset.rowId = payRowCount;
    row.innerHTML = `
        <div class="form-group" style="margin:0;">
            <label>Date</label>
            <input type="date" class="row-date" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Payment Type</label>
            <select class="row-type">
                <option value="cash">Cash</option>
                <option value="mpesa">M-Pesa</option>
                <option value="card">Card</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Amount Paid</label>
            <input type="number" step="0.01" class="row-amount" oninput="recalcTotals()">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Payment Note</label>
            <input type="text" class="row-note">
        </div>
        <button type="button" class="btn remove-btn" style="background:#c0392b;" onclick="removePayRow(${payRowCount})">−</button>
    `;
    container.appendChild(row);
}

function removePayRow(rowId) {
    document.querySelector(`[data-row-id="${rowId}"]`).remove();
    recalcTotals();
}

function recalcTotals() {
    let total = 0;
    document.querySelectorAll('.row-amount').forEach(el => total += parseFloat(el.value) || 0);
    document.getElementById('totalReceived').value = total.toFixed(2);
    const change = total - dueAmount;
    document.getElementById('changeReturn').value = (change > 0 ? change : 0).toFixed(2);
}

document.getElementById('paymentForm').addEventListener('submit', function (e) {
    const rows = [];
    document.querySelectorAll('.pay-row').forEach(row => {
        const amount = parseFloat(row.querySelector('.row-amount').value) || 0;
        if (amount <= 0) return;
        rows.push({
            date: row.querySelector('.row-date').value,
            payment_type: row.querySelector('.row-type').value,
            amount: amount,
            note: row.querySelector('.row-note').value,
        });
    });
    if (rows.length === 0) {
        e.preventDefault();
        alert('Add at least one payment row with an amount.');
        return;
    }
    document.getElementById('rowsJson').value = JSON.stringify(rows);
});

addPayRow(); // start with one row, like the original
</script>
