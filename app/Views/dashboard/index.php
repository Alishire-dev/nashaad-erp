<h2>Dashboard</h2>
<div class="card-row">
    <div class="card" style="background:#2980b9;">
        <div>Total Items</div>
        <h2><?= (int) $totalItems ?></h2>
    </div>
    <div class="card" style="background:#c0392b;">
        <div>Stock Alerts</div>
        <h2><?= count($lowStock) ?></h2>
    </div>
</div>

<h3>Stock Alert</h3>
<table>
    <tr><th>Item</th><th>Current Stock</th><th>Alert Qty</th></tr>
    <?php foreach ($lowStock as $item): ?>
    <tr>
        <td><?= esc($item['name']) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td><?= number_format((float) $item['alert_qty'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($lowStock)): ?>
    <tr><td colspan="3">No items running low.</td></tr>
    <?php endif; ?>
</table>
