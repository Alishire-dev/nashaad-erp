<h2>Stock Alert</h2>
<table>
    <tr><th>Item</th><th>Current Stock</th><th>Alert Qty</th><th>Shortfall</th></tr>
    <?php foreach ($lowStock as $item): ?>
    <tr>
        <td><?= esc($item['name']) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td><?= number_format((float) $item['alert_qty'], 2) ?></td>
        <td style="color:#c0392b; font-weight:bold;">
            <?= number_format((float) $item['alert_qty'] - (float) $item['current_stock'], 2) ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($lowStock)): ?>
    <tr><td colspan="4">No items running low.</td></tr>
    <?php endif; ?>
</table>
