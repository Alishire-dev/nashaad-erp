<h2>Items List</h2>
<a class="btn green" href="<?= site_url('items/add') ?>">+ New Item</a>
<br><br>
<table>
    <tr>
        <th>#</th><th>Name</th><th>Category</th><th>Unit</th>
        <th>Purchase Price</th><th>Sales Price</th><th>Stock</th><th>Status</th>
    </tr>
    <?php foreach ($items as $i => $item): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['category_name'] ?? '-') ?></td>
        <td><?= esc($item['unit_short'] ?? '-') ?></td>
        <td><?= number_format((float) $item['purchase_price'], 2) ?></td>
        <td><?= number_format((float) $item['sales_price'], 2) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td><?= ucfirst($item['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <tr><td colspan="8">No items yet.</td></tr>
    <?php endif; ?>
</table>
