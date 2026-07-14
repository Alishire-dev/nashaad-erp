<h2>Items List</h2>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <a class="btn green" href="<?= site_url('items/add') ?>">+ New Item</a>
    <input type="text" id="itemSearch" placeholder="Search items..." style="width:250px;" onkeyup="filterTable()">
</div>

<table id="itemsTable">
    <tr>
        <th></th><th>Item Code</th><th>Name</th><th>SKU</th><th>Category</th><th>Unit</th>
        <th>Reorder</th><th>Tax%</th><th>Purchase Price</th><th>Sales Price</th>
        <th>Stock</th><th>Order Item</th><th>Status</th><th></th>
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
        <td><?= esc($item['item_code'] ?? '-') ?></td>
        <td><?= esc($item['name']) ?></td>
        <td><?= esc($item['sku'] ?: '-') ?></td>
        <td><?= esc($item['category_name'] ?? '-') ?></td>
        <td><?= esc($item['unit_short'] ?? '-') ?></td>
        <td><?= number_format((float) $item['alert_qty'], 0) ?></td>
        <td><?= number_format((float) ($item['tax_rate'] ?? 0), 0) ?>%</td>
        <td><?= number_format((float) $item['purchase_price'], 2) ?></td>
        <td><?= number_format((float) $item['sales_price'], 2) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td>
            <?php if ((int) ($item['order_item'] ?? 1) === 1): ?>
                <span style="background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Yes</span>
            <?php else: ?>
                <span style="background:#c0392b; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">No</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($item['status'] === 'active'): ?>
                <span style="background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Active</span>
            <?php else: ?>
                <span style="background:#999; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Inactive</span>
            <?php endif; ?>
        </td>
        <td><a class="btn" href="<?= site_url('items/edit/' . $item['id']) ?>">Edit</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <tr><td colspan="14">No items yet.</td></tr>
    <?php endif; ?>
</table>

<script>
function filterTable() {
    const query = document.getElementById('itemSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#itemsTable tr:not(:first-child)');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}
</script>
