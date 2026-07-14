<h2>Items List</h2>
<a class="btn green" href="<?= site_url('items/add') ?>">+ New Item</a>
<br><br>
<table>
    <tr>
        <th></th><th>Name</th><th>SKU</th><th>Category</th><th>Unit</th>
        <th>Purchase Price</th><th>Sales Price</th><th>Stock</th><th>Status</th><th></th>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td>
            <?php if (! empty($item['image'])): ?>
                <img src="<?= base_url('uploads/items/' . $item['image']) ?>" style="width:36px; height:36px; object-fit:cover; border-radius:4px;">
            <?php else: ?>
                <div style="width:36px; height:36px; background:#eee; border-radius:4px;"></div>
            <?php endif; ?>
        </td>
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['sku'] ?: '-') ?></td>
        <td><?= esc($item['category_name'] ?? '-') ?></td>
        <td><?= esc($item['unit_short'] ?? '-') ?></td>
        <td><?= number_format((float) $item['purchase_price'], 2) ?></td>
        <td><?= number_format((float) $item['sales_price'], 2) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td><?= ucfirst($item['status']) ?></td>
        <td><a class="btn" href="<?= site_url('items/edit/' . $item['id']) ?>">Edit</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <tr><td colspan="10">No items yet.</td></tr>
    <?php endif; ?>
</table>
