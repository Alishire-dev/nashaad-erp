<h2>Purchase #<?= $purchase['id'] ?></h2>
<p>
    <strong>Supplier:</strong> <?= esc($purchase['supplier_name'] ?? '-') ?><br>
    <strong>Reference No:</strong> <?= esc($purchase['reference_no'] ?? '-') ?><br>
    <strong>Date:</strong> <?= esc($purchase['purchase_date']) ?><br>
    <strong>Status:</strong> <?= ucfirst($purchase['status']) ?>
</p>

<table>
    <tr><th>Item</th><th>Qty</th><th>Cost Price</th><th>Tax%</th><th>Discount%</th><th>Total</th></tr>
    <?php foreach ($purchase['lines'] as $l): ?>
    <tr>
        <td><?= esc($l['item_name']) ?> <?= esc($l['unit_short'] ?? '') ?></td>
        <td><?= number_format((float) $l['quantity'], 3) ?></td>
        <td><?= number_format((float) $l['cost_price'], 2) ?></td>
        <td><?= number_format((float) $l['tax_percent'], 2) ?></td>
        <td><?= number_format((float) $l['discount_pct'], 2) ?></td>
        <td><?= number_format((float) $l['total_amount'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3 style="text-align:right;">Grand Total: <?= number_format((float) $purchase['grand_total'], 2) ?></h3>
