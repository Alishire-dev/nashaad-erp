<h2>Convert Stock</h2>
<p style="color:#666; font-size:14px;">Moves real stock from a parent item into a child item — e.g. break down 1 case into 24 pieces.</p>

<form method="post" action="<?= site_url('stock-conversion/add') ?>" style="max-width:600px;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Parent Product (converting FROM)*</label>
        <select name="parent_item_id" required>
            <option value="">-Select-</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>"><?= esc($item['name']) ?> [Stock: <?= number_format((float) $item['current_stock'], 2) ?>]</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Qty Converted*</label><input type="number" step="0.001" name="qty_converted" required></div>

    <div class="form-group">
        <label>Child Product (converting TO)*</label>
        <select name="child_item_id" required>
            <option value="">-Select-</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>"><?= esc($item['name']) ?> [Stock: <?= number_format((float) $item['current_stock'], 2) ?>]</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Qty Produced*</label><input type="number" step="0.001" name="qty_produced" required></div>

    <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="e.g. broke down 1 case into pieces"></textarea></div>

    <button class="btn green" type="submit">Convert</button>
    <a class="btn" href="<?= site_url('stock-conversion') ?>">Cancel</a>
</form>
