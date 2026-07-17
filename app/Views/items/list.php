<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:160px; z-index:10;
    }
    .action-dropdown-menu a, .action-dropdown-menu button {
        display:block; width:100%; text-align:left; padding:8px 14px; color:#333;
        text-decoration:none; font-size:13px; background:none; border:none; cursor:pointer;
    }
    .action-dropdown-menu a:hover, .action-dropdown-menu button:hover { background:#f4f5f7; }
    .action-dropdown-menu a.danger, .action-dropdown-menu button.danger { color:#c0392b; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
    .badge-yes { background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px; }
    .badge-no { background:#c0392b; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px; }
</style>

<h2>Items List</h2>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <a class="btn green" href="<?= site_url('items/add') ?>">+ New Item</a>
</div>

<table id="itemsTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="toggleAll(this)"></th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Reorder</th>
            <th>Tax</th>
            <th>Order Item</th>
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
            <td><?= esc($item['brand_name'] ?? '') ?></td>
            <td><?= esc($item['category_name'] ?? '') ?></td>
            <td><?= esc($item['unit_short'] ?? '') ?></td>
            <td><?= number_format((float) $item['alert_qty'], 0) ?></td>
            <td><?= number_format((float) ($item['tax_rate'] ?? 0), 0) ?>%</td>
            <td>
                <?php if ((int) ($item['order_item'] ?? 1) === 1): ?>
                    <span class="badge-yes">Yes</span>
                <?php else: ?>
                    <span class="badge-no">No</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($item['status'] === 'active'): ?>
                    <span class="badge-yes">Active</span>
                <?php else: ?>
                    <span style="background:#999; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Inactive</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('items/edit/' . $item['id']) ?>">✏️ Edit Item Details</a>
                        <a href="<?= site_url('items/profile/' . $item['id']) ?>">🚩 Item Profile</a>
                        <a href="<?= site_url('items/profile/' . $item['id']) ?>#conversion">🚩 Item Conversion</a>
                        <form method="post" action="<?= site_url('items/delete/' . $item['id']) ?>"
                              onsubmit="return confirm('Delete this item? It will be marked inactive, not permanently erased.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="danger">🗑 Delete</button>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
}

$(document).ready(function () {
    initDataTable('#itemsTable', {
        columnDefs: [{ orderable: false, targets: [0, 10] }],
        pageLength: 2000,
        lengthMenu: [[25, 50, 100, 2000], [25, 50, 100, 'All (2000)']],
    });
});
</script>
