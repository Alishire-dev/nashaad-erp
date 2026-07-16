<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:140px; z-index:10;
    }
    .action-dropdown-menu a, .action-dropdown-menu button {
        display:block; width:100%; text-align:left; padding:8px 14px; color:#333;
        text-decoration:none; font-size:13px; background:none; border:none; cursor:pointer;
    }
    .action-dropdown-menu a:hover, .action-dropdown-menu button:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Archived Items</h2>

<table id="archivedTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="toggleAll(this)"></th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Sku</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Reorder</th>
            <th>R.Price</th>
            <th>W.Price</th>
            <th>Tax</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><input type="checkbox" class="row-check" value="<?= $item['id'] ?>"></td>
            <td><?= esc($item['item_code'] ?? '-') ?></td>
            <td><?= esc($item['name']) ?></td>
            <td><?= esc($item['sku'] ?? '') ?></td>
            <td><?= esc($item['brand_name'] ?? '') ?></td>
            <td><?= esc($item['category_name'] ?? '') ?></td>
            <td><?= esc($item['unit_short'] ?? '') ?></td>
            <td><?= number_format((float) $item['alert_qty'], 0) ?></td>
            <td><?= number_format((float) $item['sales_price'], 2) ?></td>
            <td><?= number_format((float) $item['wholesale_price'], 2) ?></td>
            <td><?= number_format((float) ($item['tax_rate'] ?? 0), 0) ?>%</td>
            <td><span style="background:#c0392b; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Inactive</span></td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <form method="post" action="<?= site_url('items/restore/' . $item['id']) ?>">
                            <?= csrf_field() ?>
                            <button type="submit">↺ Restore</button>
                        </form>
                        <a href="<?= site_url('items/edit/' . $item['id']) ?>">✏️ Edit Item Details</a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
        <tr><td colspan="13">No archived items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
}
function toggleDropdown(btn) {
    const dropdown = btn.closest('.action-dropdown');
    document.querySelectorAll('.action-dropdown.open').forEach(d => { if (d !== dropdown) d.classList.remove('open'); });
    dropdown.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});

$(document).ready(function () {
    initDataTable('#archivedTable', { columnDefs: [{ orderable: false, targets: [0, 12] }] });
});
</script>
