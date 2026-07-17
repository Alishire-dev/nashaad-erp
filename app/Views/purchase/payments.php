<style>
    .payments-card { background:#fff; border-radius:8px; overflow:hidden; max-width:900px; }
    .payments-header {
        background:linear-gradient(135deg,#e88a2e,#d96f0f); color:#fff; padding:16px 24px;
        font-size:20px; font-weight:700; text-align:center;
    }
    .payments-body { padding:24px; }
    .payments-info { display:flex; gap:30px; flex-wrap:wrap; margin-bottom:20px; }
    .payments-info .block { flex:1; min-width:220px; }
    .payments-info .block h4 { margin:0 0 8px 0; color:#666; font-size:13px; }
    .payments-table th, .payments-table td { padding:10px; font-size:13px; }
</style>

<div class="payments-card">
    <div class="payments-header">Payments</div>
    <div class="payments-body">
        <div class="payments-info">
            <div class="block">
                <h4>Supplier Information</h4>
                <strong><?= esc($purchase['supplier_name'] ?? '-') ?></strong><br>
                Phone: <?= esc($purchase['supplier_phone'] ?? '-') ?><br>
                Email: <?= esc($purchase['supplier_email'] ?? '-') ?>
            </div>
            <div class="block">
                <h4>Purchase Information</h4>
                Invoice #<?= esc($purchase['reference_no'] ?? $purchase['id']) ?><br>
                Date: <?= esc($purchase['purchase_date']) ?><br>
                Grand Total: <?= number_format((float) $purchase['grand_total'], 2) ?>
            </div>
            <div class="block">
                <h4>&nbsp;</h4>
                Paid Amount: <?= number_format((float) $purchase['amount_paid'], 2) ?><br>
                Due Amount: <?= number_format((float) $purchase['grand_total'] - (float) $purchase['amount_paid'], 2) ?>
            </div>
        </div>

        <table class="payments-table" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th><th>Payment Date</th><th>Payment</th><th>Payment Type</th>
                    <th>Voucher No</th><th>Payment Note</th><th>Created by</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($p['payment_date']) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?></td>
                    <td><?= esc(ucfirst($p['payment_type'])) ?></td>
                    <td><?= esc($p['voucher_no'] ?? '') ?></td>
                    <td><?= esc($p['payment_note'] ?? '') ?></td>
                    <td><?= esc($p['created_by_name'] ?? '-') ?></td>
                    <td>
                        <form method="post" action="<?= site_url('purchase/payments/delete/' . $p['id']) ?>"
                              onsubmit="return confirm('Delete this payment? Balance will be recalculated.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn" style="background:#c0392b; padding:4px 10px;">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <tr><td colspan="8">No payments recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h4 style="margin-top:20px;">Record New Payment</h4>
        <form method="post" action="<?= site_url('purchase/payments/' . $purchase['id'] . '/add') ?>" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <?= csrf_field() ?>
            <div class="form-group" style="margin:0;"><label>Date</label><input type="date" name="payment_date" value="<?= date('Y-m-d') ?>"></div>
            <div class="form-group" style="margin:0;"><label>Amount*</label><input type="number" step="0.01" name="amount" required style="width:120px;"></div>
            <div class="form-group" style="margin:0;">
                <label>Type</label>
                <select name="payment_type">
                    <option value="cash">Cash</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="card">Card</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;"><label>Voucher No</label><input type="text" name="voucher_no" style="width:120px;"></div>
            <div class="form-group" style="margin:0; flex:1;"><label>Note</label><input type="text" name="payment_note"></div>
            <button class="btn green" type="submit">Add Payment</button>
        </form>

        <div style="text-align:right; margin-top:20px;">
            <a class="btn" href="<?= site_url('purchase/list') ?>">Close</a>
        </div>
    </div>
</div>
