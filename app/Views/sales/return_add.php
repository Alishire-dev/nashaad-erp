<h2>Sales Return — Invoice <?= esc($sale['invoice_no']) ?></h2>

<div style="background:#fdecea; color:#c0392b; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:14px;">
    ⚠ This action is permanent!! NOTE: The items are returned back to stock upon submission (unless marked damaged). Use the delete button to remove the item you don't want to return.
</div>

<form method="post" action="<?= site_url('sales/return/add') ?>" id="returnForm">
    <?= csrf_field() ?>
    <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
    <input type="hidden" name="lines_json" id="linesJson">

    <table style="width:100%;">
        <thead>
            <tr><th>Item Name</th><th>Sold Qty</th><th>Qty To Return</th><th>Good Condition?</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($sale['lines'] as $l): ?>
            <tr class="return-row" data-item-id="<?= $l['item_id'] ?>" data-price="<?= $l['unit_price'] ?>">
                <td><?= esc($l['item_name']) ?></td>
                <td><input type="text" value="<?= number_format((float) $l['quantity'], 3) ?>" disabled style="background:#f4f5f7;"></td>
                <td><input type="number" step="0.001" max="<?= $l['quantity'] ?>" class="return-qty" placeholder="Qty To Return"></td>
                <td>
                    <select class="return-condition">
                        <option value="">Select</option>
                        <option value="yes">Good</option>
                        <option value="no">Damaged</option>
                    </select>
                </td>
                <td><button type="button" class="btn" style="background:#c0392b;" onclick="this.closest('tr').remove()">🗑</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:flex; gap:20px; margin-top:20px;">
        <div class="form-group" style="flex:1;"><label>Receipt Ref. No.*</label><input type="text" name="receipt_ref" required></div>
        <div class="form-group" style="flex:2;"><label>Narrative*</label><textarea name="narrative" rows="2" required></textarea></div>
    </div>

    <button class="btn" style="background:#c0392b;" type="button" onclick="window.history.back()">✕ Close</button>
    <button class="btn green" type="submit">Submit ➤</button>
</form>

<script>
document.getElementById('returnForm').addEventListener('submit', function (e) {
    const lines = [];
    document.querySelectorAll('.return-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.return-qty').value) || 0;
        if (qty <= 0) return;
        lines.push({
            item_id: row.dataset.itemId,
            quantity: qty,
            unit_price: row.dataset.price,
            good_condition: row.querySelector('.return-condition').value || 'yes',
        });
    });
    if (lines.length === 0) {
        e.preventDefault();
        alert('Enter a Qty To Return for at least one item.');
        return;
    }
    document.getElementById('linesJson').value = JSON.stringify(lines);
});
</script>
