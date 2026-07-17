<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:120px; z-index:10;
    }
    .action-dropdown-menu a, .action-dropdown-menu button {
        display:block; width:100%; text-align:left; padding:8px 14px; color:#333;
        text-decoration:none; font-size:13px; background:none; border:none; cursor:pointer;
    }
    .action-dropdown-menu a:hover, .action-dropdown-menu button:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Damaged Products</h2>
<a class="btn" style="background:#c0392b;" href="<?= site_url('damaged-products/add') ?>">+ Add Damaged</a>
<br><br>

<table id="damagedTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>SN</th><th>Item Name</th><th>Qty</th><th>Cost Price</th><th>Total</th>
            <th>Posted By</th><th>Date</th><th>Branch</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($damaged as $i => $row): ?>
        <?php $total = (float) $row['quantity'] * (float) ($row['unit_cost'] ?? 0); ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($row['item_name']) ?></td>
            <td><?= number_format((float) $row['quantity'], 0) ?></td>
            <td><?= number_format((float) ($row['unit_cost'] ?? 0), 2) ?></td>
            <td><?= number_format($total, 2) ?></td>
            <td><?= esc($row['user_name'] ?? 'Admin') ?></td>
            <td><?= esc($row['adjustment_date'] ?? $row['created_at']) ?></td>
            <td>Main Branch</td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <form method="post" action="<?= site_url('damaged-products/delete/' . $row['id']) ?>"
                              onsubmit="return confirm('Delete this record? Stock will be restored.');">
                            <?= csrf_field() ?>
                            <button type="submit" style="color:#c0392b;">🗑 Delete</button>
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
$(document).ready(function () { initDataTable('#damagedTable', { columnDefs: [{ orderable: false, targets: [8] }] }); });
</script>
