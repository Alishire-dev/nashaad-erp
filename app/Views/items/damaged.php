<h2>Damaged Products</h2>
<p style="color:#666; font-size:14px;">Items written off as damaged/expired — each entry decrements stock via the same audit trail as sales/purchases.</p>

<div style="background:#fff; border-radius:8px; padding:16px; margin-bottom:20px; max-width:500px;">
    <h3 style="margin-top:0;">Report Damage</h3>
    <form method="post" action="<?= site_url('damaged-products/add') ?>">
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
        <div class="form-group"><label>Reason / Note</label><input type="text" name="note" placeholder="e.g. expired, broken in transit"></div>
        <button class="btn" style="background:#c0392b;" type="submit">Report Damage</button>
    </form>
</div>

<table id="damagedTable" class="display" style="width:100%;">
    <thead>
        <tr><th>SN</th><th>Item Name</th><th>Qty</th><th>Reason</th><th>Posted By</th><th>Date</th></tr>
    </thead>
    <tbody>
        <?php foreach ($damaged as $i => $row): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($row['item_name']) ?></td>
            <td><?= number_format((float) $row['quantity'], 3) ?> <?= esc($row['unit_short'] ?? '') ?></td>
            <td><?= esc($row['note'] ?? '') ?></td>
            <td><?= esc($row['user_name'] ?? '-') ?></td>
            <td><?= esc($row['adjustment_date'] ?? $row['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($damaged)): ?>
        <tr><td colspan="6">No damaged products reported yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#damagedTable'); });</script>
