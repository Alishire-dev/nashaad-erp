<h2>Issued Products</h2>
<p style="color:#666; font-size:14px;">Items issued for internal use (not sold) — each entry decrements stock via the same audit trail as sales/purchases.</p>

<div style="background:#fff; border-radius:8px; padding:16px; margin-bottom:20px; max-width:500px;">
    <h3 style="margin-top:0;">Issue an Item</h3>
    <form method="post" action="<?= site_url('issued-products/add') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Item*</label>
            <select name="item_id" required>
                <option value="">-Select-</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= esc($item['name']) ?> (<?= esc($item['item_code'] ?? '') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Quantity*</label><input type="number" step="0.001" name="quantity" required></div>
        <div class="form-group"><label>Reason / Note</label><input type="text" name="note" placeholder="e.g. staff meal, cleaning supplies"></div>
        <button class="btn green" type="submit">Issue</button>
    </form>
</div>

<table id="issuedTable" class="display" style="width:100%;">
    <thead>
        <tr><th>SN</th><th>Item Name</th><th>Qty</th><th>Note</th><th>Posted By</th><th>Date</th></tr>
    </thead>
    <tbody>
        <?php foreach ($issued as $i => $row): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($row['item_name']) ?></td>
            <td><?= number_format((float) $row['quantity'], 3) ?> <?= esc($row['unit_short'] ?? '') ?></td>
            <td><?= esc($row['note'] ?? '') ?></td>
            <td><?= esc($row['user_name'] ?? '-') ?></td>
            <td><?= esc($row['adjustment_date'] ?? $row['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($issued)): ?>
        <tr><td colspan="6">No issued products yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#issuedTable'); });</script>
