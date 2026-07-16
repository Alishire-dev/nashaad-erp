<h2>Sale <?= esc($sale['invoice_no']) ?></h2>
<p>
    <strong>Customer:</strong> <?= esc($sale['customer_name'] ?? 'WALK-IN') ?><br>
    <strong>Date:</strong> <?= esc($sale['sale_date']) ?><br>
    <strong>Payment Mode:</strong> <?= esc(ucfirst($sale['pay_mode'] ?? 'cash')) ?>
</p>

<table>
    <tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Discount%</th><th>Total</th></tr>
    <?php foreach ($sale['lines'] as $l): ?>
    <tr>
        <td><?= esc($l['item_name']) ?> <?= esc($l['unit_short'] ?? '') ?></td>
        <td><?= number_format((float) $l['quantity'], 3) ?></td>
        <td><?= number_format((float) $l['unit_price'], 2) ?></td>
        <td><?= number_format((float) $l['discount_pct'], 2) ?></td>
        <td><?= number_format((float) $l['total_amount'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div style="text-align:right; margin-top:16px;">
    <p>Subtotal: <?= number_format((float) $sale['subtotal'], 2) ?></p>
    <p>Discount: <?= number_format((float) $sale['discount_amt'], 2) ?></p>
    <h3>Grand Total: <?= number_format((float) $sale['grand_total'], 2) ?></h3>
    <p>Paid: <?= number_format((float) $sale['amount_paid'], 2) ?></p>
</div>
