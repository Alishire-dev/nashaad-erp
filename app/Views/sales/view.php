<style>
    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; border-radius:8px; width:480px; max-width:92vw; overflow:hidden; }
    .modal-header { background:linear-gradient(135deg,#e88a2e,#d96f0f); color:#fff; padding:14px 20px; display:flex; justify-content:space-between; }
    .modal-body { padding:20px; max-height:70vh; overflow-y:auto; }
    .info-block { flex:1; min-width:220px; }
    .info-block h4 { margin:0 0 8px 0; color:#666; font-size:13px; }
</style>

<h2>Invoice</h2>

<div style="background:#fff; border-radius:8px; padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #eee; padding-bottom:12px; margin-bottom:16px;">
        <h3 style="margin:0;">🌐 Sales Invoice</h3>
        <span style="color:#666;">Date: <?= esc(date('d-m-Y H:i:s', strtotime($sale['created_at']))) ?></span>
    </div>

    <div style="display:flex; gap:30px; flex-wrap:wrap; margin-bottom:20px;">
        <div class="info-block">
            <h4>From</h4>
            <strong>NASHAAD</strong><br>Kismaayo<br>
        </div>
        <div class="info-block">
            <h4>Customer Details</h4>
            <strong><?= esc($sale['customer_name'] ?? 'WALK-IN') ?></strong><br>
            <?= esc($sale['customer_address'] ?? '') ?><br>
            <?= esc($sale['customer_phone'] ?? '') ?><br>
            <?= esc($sale['customer_email'] ?? '') ?>
        </div>
        <div class="info-block" style="text-align:right;">
            <strong>Invoice #<?= esc($sale['invoice_no']) ?></strong><br>
            Sales Status: <?= esc(ucfirst($sale['status'])) ?><br>
            Reference No.: <?= esc($sale['lpo_number'] ?? '') ?><br>
            Sales Person: <?= esc($sale['sales_person_name'] ?? '-') ?>
        </div>
    </div>

    <table style="width:100%;">
        <thead>
            <tr><th>#</th><th>Item Name</th><th>Unit Price</th><th>Quantity</th><th>Discount</th><th>Total Amount</th></tr>
        </thead>
        <tbody>
            <?php foreach ($sale['lines'] as $i => $l): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($l['item_name']) ?> <?= esc($l['unit_short'] ?? '') ?></td>
                <td><?= number_format((float) $l['unit_price'], 2) ?></td>
                <td><?= number_format((float) $l['quantity'], 3) ?></td>
                <td><?= number_format((float) $l['discount_pct'], 2) ?>%</td>
                <td><?= number_format((float) $l['total_amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:flex; justify-content:space-between; margin-top:16px; flex-wrap:wrap; gap:20px;">
        <div>
            <p>Discount on All: <?= number_format((float) $sale['discount_pct'], 2) ?><?= $sale['discount_type'] === 'fixed' ? ' (Fixed)' : '%' ?></p>
            <p>Note: <?= esc($sale['note'] ?? '') ?></p>
        </div>
        <div style="text-align:right; min-width:220px;">
            <p>Subtotal: <?= number_format((float) $sale['subtotal'], 2) ?></p>
            <p>Discount on All: <?= number_format((float) $sale['discount_amt'], 2) ?></p>
            <h3>Grand Total: <?= number_format((float) $sale['grand_total'], 2) ?></h3>
        </div>
    </div>

    <h4 style="margin-top:20px;">Payments Information:</h4>
    <table style="width:100%;">
        <thead><tr><th>#</th><th>Date</th><th>Payment Type</th><th>Payment</th></tr></thead>
        <tbody>
            <?php if (empty($paymentsPreview)): ?>
                <tr><td colspan="4" style="text-align:center; color:#999;">No Previous Payments Found!!</td></tr>
            <?php else: ?>
                <?php foreach ($paymentsPreview as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($p['payment_date']) ?></td>
                    <td><?= esc(ucfirst($p['payment_type'])) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="display:flex; gap:10px; margin-top:20px; flex-wrap:wrap;">
        <button class="btn green" onclick="openDetailsModal()">✎ Change Sales Details</button>
        <button class="btn" style="background:#17a2b8;" onclick="openDiscountModal()">✎ Apply Discount</button>
        <a class="btn" style="background:#2c2f38;" href="<?= site_url('sales/payments/' . $sale['id']) ?>">💳 View Payments</a>
        <a class="btn" style="background:#3a8fd6;" href="<?= site_url('sales/return/' . $sale['id']) ?>">🔄 Sales Return</a>
        <?php if ($sale['status'] !== 'cancelled'): ?>
        <form method="post" action="<?= site_url('sales/cancel/' . $sale['id']) ?>" onsubmit="return confirm('Cancel this sale? Stock will be restored.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn" style="background:#c0392b;">✕ Cancel Sale</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Change Sales Details modal -->
<div class="modal-backdrop" id="detailsModal">
    <div class="modal-box">
        <div class="modal-header"><strong>Change Sales Details</strong><span style="cursor:pointer;" onclick="closeDetailsModal()">&times;</span></div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('sales/update-details/' . $sale['id']) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Sales Person*</label>
                    <select name="sales_person_id">
                        <option value="">-Select-</option>
                        <?php foreach ($salesPersons as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u['id'] == $sale['sales_person_id'] ? 'selected' : '' ?>><?= esc($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Sales Date*</label><input type="date" name="sale_date" value="<?= esc($sale['sale_date']) ?>" required></div>
                <div class="form-group"><label>Sales Due Date*</label><input type="date" name="due_date" value="<?= esc($sale['due_date'] ?? '') ?>"></div>
                <div class="form-group"><label>LPO Number</label><input type="text" name="lpo_number" value="<?= esc($sale['lpo_number'] ?? '') ?>"></div>
                <div class="form-group"><label>Sales Note</label><textarea name="note" rows="3"><?= esc($sale['note'] ?? '') ?></textarea></div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" style="background:#c0392b;" type="button" onclick="closeDetailsModal()">Close</button>
            </form>
        </div>
    </div>
</div>

<!-- Apply Discount modal -->
<div class="modal-backdrop" id="discountModal">
    <div class="modal-box">
        <div class="modal-header"><strong>Apply Discount</strong><span style="cursor:pointer;" onclick="closeDiscountModal()">&times;</span></div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('sales/apply-discount/' . $sale['id']) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Discount Type*</label>
                    <select name="discount_type" required>
                        <option value="percentage" <?= $sale['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                        <option value="fixed" <?= $sale['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                    </select>
                </div>
                <div class="form-group"><label>Discount*</label><input type="number" step="0.01" name="discount_value" value="<?= esc($sale['discount_pct']) ?>" required></div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" style="background:#c0392b;" type="button" onclick="closeDiscountModal()">Close</button>
            </form>
        </div>
    </div>
</div>

<script>
function openDetailsModal() { document.getElementById('detailsModal').classList.add('show'); }
function closeDetailsModal() { document.getElementById('detailsModal').classList.remove('show'); }
function openDiscountModal() { document.getElementById('discountModal').classList.add('show'); }
function closeDiscountModal() { document.getElementById('discountModal').classList.remove('show'); }
</script>
