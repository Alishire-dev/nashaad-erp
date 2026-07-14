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

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:20px; border-radius:6px; width:360px; }
</style>

<h2>Stock Management</h2>
<small style="color:#666;">View/Search Items</small>

<table id="stockTable" class="display" style="width:100%; margin-top:14px;">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="toggleAll(this)"></th>
            <th>Code</th>
            <th>Item Name</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Stock</th>
            <th>Reorder</th>
            <th>Cost</th>
            <th>R.Price</th>
            <th>W.Price</th>
            <th>Prom. Price</th>
            <th>Tax</th>
            <th>Expiry</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><input type="checkbox" class="row-check" value="<?= $item['id'] ?>"></td>
            <td><?= esc($item['item_code'] ?? '-') ?></td>
            <td><?= esc($item['name']) ?></td>
            <td><?= esc($item['category_name'] ?? '-') ?></td>
            <td><?= esc($item['unit_short'] ?? '-') ?></td>
            <td><?= (int) $item['manage_stock'] === 0 ? '-' : number_format((float) $item['current_stock'], 2) ?></td>
            <td><?= number_format((float) $item['alert_qty'], 0) ?></td>
            <td><?= number_format((float) $item['purchase_price'], 2) ?></td>
            <td><?= number_format((float) $item['sales_price'], 2) ?></td>
            <td><?= number_format((float) $item['wholesale_price'], 2) ?></td>
            <td><?= number_format((float) $item['minimum_price'], 2) ?></td>
            <td><?= number_format((float) ($item['tax_rate'] ?? 0), 0) ?>%</td>
            <td><?= esc($item['expiry_date'] ?: '-') ?></td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('items/edit/' . $item['id']) ?>">Edit</a>
                        <button onclick="openAdjustModal(<?= $item['id'] ?>, '<?= esc($item['name'], 'js') ?>', <?= (float) $item['current_stock'] ?>)">Adjust Stock</button>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>

<h3 style="margin-top:30px;">Recent Stock Movements</h3>
<table>
    <tr><th>Date</th><th>Item</th><th>Direction</th><th>Qty</th><th>Reason</th><th>Note</th><th>By</th></tr>
    <?php foreach ($recent as $r): ?>
    <tr>
        <td><?= esc($r['created_at']) ?></td>
        <td><?= esc($r['item_name']) ?></td>
        <td><?= $r['direction'] === 'in' ? '+ IN' : '- OUT' ?></td>
        <td><?= number_format((float) $r['quantity'], 3) ?></td>
        <td><?= esc(str_replace('_', ' ', ucfirst($r['reason']))) ?></td>
        <td><?= esc($r['note'] ?? '') ?></td>
        <td><?= esc($r['user_name'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?>
    <tr><td colspan="7">No stock movements yet.</td></tr>
    <?php endif; ?>
</table>

<!-- Adjust Stock modal -->
<div class="modal-backdrop" id="adjustModal">
    <div class="modal-box">
        <h3>Adjust Stock: <span id="adjustItemName"></span></h3>
        <form method="post" action="<?= site_url('stock/adjust') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" id="adjustItemId">
            <div class="form-group">
                <label>Current Stock</label>
                <input type="text" id="adjustCurrentStock" disabled>
            </div>
            <div class="form-group">
                <label>Direction*</label>
                <select name="direction" required>
                    <option value="in">Add Stock (+)</option>
                    <option value="out">Remove Stock (-)</option>
                </select>
            </div>
            <div class="form-group"><label>Quantity*</label><input type="number" step="0.001" name="quantity" required></div>
            <div class="form-group"><label>Reason / Note</label><input type="text" name="note" placeholder="e.g. physical count correction"></div>
            <button class="btn green" type="submit">Apply Adjustment</button>
            <button class="btn" type="button" onclick="closeAdjustModal()">Cancel</button>
        </form>
    </div>
</div>

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

function openAdjustModal(id, name, currentStock) {
    document.getElementById('adjustItemId').value = id;
    document.getElementById('adjustItemName').innerText = name;
    document.getElementById('adjustCurrentStock').value = currentStock.toFixed(2);
    document.getElementById('adjustModal').classList.add('show');
    document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
}
function closeAdjustModal() {
    document.getElementById('adjustModal').classList.remove('show');
}

$(document).ready(function () {
    initDataTable('#stockTable', { columnDefs: [{ orderable: false, targets: [0, 13] }] });
});
</script>
