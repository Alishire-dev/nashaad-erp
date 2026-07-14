<h2>Stock Manager</h2>

<h3>Manual Adjustment</h3>
<form method="post" action="<?= site_url('stock/adjust') ?>" style="max-width:500px;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Item*</label>
        <select name="item_id" required>
            <option value="">-Select-</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>">
                    <?= esc($item['name']) ?> (current: <?= number_format((float) $item['current_stock'], 2) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Direction*</label>
        <select name="direction" required>
            <option value="in">Add Stock (+)</option>
            <option value="out">Remove Stock (-)</option>
        </select>
    </div>
    <div class="form-group"><label>Quantity*</label><input type="number" step="0.001" name="quantity" required></div>
    <div class="form-group"><label>Reason / Note</label><input type="text" name="note" placeholder="e.g. physical count correction"></div>
    <button class="btn green" type="submit">Apply Adjustment</button>
</form>

<h3 style="margin-top:30px;">Recent Stock Movements</h3>
<table>
    <tr><th>Date</th><th>Item</th><th>Direction</th><th>Qty</th><th>Reason</th><th>Note</th><th>By</th></tr>
    <?php foreach ($recent as $r): ?>
    <tr>
        <td><?= esc($r['created_at']) ?></td>
        <td><?= esc($r['item_name']) ?></td>
        <td><?= $r['direction'] === 'in' ? '+ IN' : '- OUT' ?></td>
        <td><?= number_format((float) $r['quantity'], 3) ?></td>
        <td><?= esc(str_replace('_', ' ', ucfirst($r['reason']))) ?></td>
        <td><?= esc($r['note'] ?? '') ?></td>
        <td><?= esc($r['user_name'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?>
    <tr><td colspan="7">No stock movements yet.</td></tr>
    <?php endif; ?>
</table>
