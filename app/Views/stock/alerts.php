<h2>Stocks Running Low <small style="font-weight:normal; color:#666; font-size:14px;">View/Search Items</small></h2>

<div style="margin-bottom:16px;">
    <span style="background:#c0392b; color:#fff; padding:8px 16px; border-radius:6px; font-size:14px;">
        ⚠ Stocks Running Low <?= count($lowStock) ?>
    </span>
</div>

<table id="alertTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Category Name</th><th>Brand</th><th>Item Name</th><th>Reorder Level</th><th>Stock Available</th></tr>
    </thead>
    <tbody>
        <?php foreach ($lowStock as $i => $item): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($item['category_name'] ?? '') ?></td>
            <td><?= esc($item['brand_name'] ?? '') ?></td>
            <td><?= esc($item['name']) ?></td>
            <td><?= number_format((float) $item['alert_qty'], 0) ?></td>
            <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lowStock)): ?>
        <tr><td colspan="6">No items running low.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#alertTable'); });</script>
