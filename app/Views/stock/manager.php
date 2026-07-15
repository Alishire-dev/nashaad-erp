<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:6px; box-shadow:0 4px 14px rgba(0,0,0,.18);
        min-width:160px; z-index:10;
    }
    .action-dropdown-menu a, .action-dropdown-menu button {
        display:block; width:100%; text-align:left; padding:9px 14px; color:#2c3038;
        text-decoration:none; font-size:13px; background:none; border:none; cursor:pointer;
    }
    .action-dropdown-menu a:hover, .action-dropdown-menu button:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:0; border-radius:8px; width:420px; max-width:92vw; overflow:hidden; }
    .modal-header {
        background: linear-gradient(135deg, #e88a2e, #d96f0f); color:#fff;
        padding:14px 20px; display:flex; justify-content:space-between; align-items:center;
    }
    .modal-body { padding:20px; }

    /* Higher-contrast grid: darker text, stronger borders, zebra striping */
    #stockTable td { color:#1a2036; border-bottom:1px solid #e2e5ea; }
    #stockTable tbody tr:nth-child(even) { background:#f8f9fb; }
    #stockTable tbody tr:hover td { background:#fdeee0; }
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
                        <a href="<?= site_url('items/edit/' . $item['id']) ?>">✏️ Edit</a>
                        <button onclick='openAdjustModal(<?= $item["id"] ?>, <?= json_encode($item["name"]) ?>, <?= (float) $item["current_stock"] ?>)'>⚖️ Adjust Stock</button>
                        <button onclick='openPriceModal(<?= $item["id"] ?>, <?= json_encode($item["name"]) ?>, <?= (float) $item["purchase_price"] ?>, <?= (float) $item["sales_price"] ?>, <?= (float) $item["wholesale_price"] ?>, <?= (float) $item["minimum_price"] ?>)'>💲 Update Price</button>
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
    <tr><th>Date</th><th>Item</th><th>Direction</th><th>Qty</th><th>Reason</th><th>Account</th><th>Narrative</th><th>By</th></tr>
    <?php foreach ($recent as $r): ?>
    <tr>
        <td><?= esc($r['adjustment_date'] ?? $r['created_at']) ?></td>
        <td><?= esc($r['item_name']) ?></td>
        <td><?= $r['direction'] === 'in' ? '+ IN' : '- OUT' ?></td>
        <td><?= number_format((float) $r['quantity'], 3) ?></td>
        <td><?= esc(str_replace('_', ' ', ucfirst($r['reason']))) ?></td>
        <td><?= esc($r['account_name'] ?? '-') ?></td>
        <td><?= esc($r['note'] ?? '') ?></td>
        <td><?= esc($r['user_name'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?>
    <tr><td colspan="8">No stock movements yet.</td></tr>
    <?php endif; ?>
</table>

<!-- Adjust Stock modal -->
<div class="modal-backdrop" id="adjustModal">
    <div class="modal-box">
        <div class="modal-header">
            <strong>✏️ Adjust Stock: <span id="adjustItemName"></span></strong>
            <span style="cursor:pointer;" onclick="closeAdjustModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('stock/adjust') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" id="adjustItemId">

                <div class="form-group">
                    <label>Date*</label>
                    <input type="date" name="adjustment_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Status*</label>
                    <select name="status" required>
                        <option value="">~~Select Type~~</option>
                        <option value="increase">Increase (Stock In)</option>
                        <option value="decrease">Decrease (Stock Out)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Migration Control Account* <small>(affects equity accounts)</small></label>
                    <select name="migration_control_account_id" required>
                        <option value="">-Select-</option>
                        <?php foreach ($migrationAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= esc($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Current Stock</label>
                    <input type="text" id="adjustCurrentStock" disabled>
                </div>
                <div class="form-group">
                    <label>Adjust Qty*</label>
                    <input type="number" step="0.001" name="adjust_qty" placeholder="Type number, e.g 20,30..." required>
                </div>
                <div class="form-group">
                    <label>Narrative</label>
                    <textarea name="narrative" rows="2" placeholder="Remarks"></textarea>
                </div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" type="button" onclick="closeAdjustModal()">Close</button>
            </form>
        </div>
    </div>
</div>

<!-- Update Price modal -->
<div class="modal-backdrop" id="priceModal">
    <div class="modal-box">
        <div class="modal-header">
            <strong>💲 Update Stock Price: <span id="priceItemName"></span></strong>
            <span style="cursor:pointer;" onclick="closePriceModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="priceForm">
                <?= csrf_field() ?>
                <div class="form-group"><label>Purchase Price*</label><input type="number" step="0.01" name="purchase_price" id="priceCost" required></div>
                <div class="form-group"><label>Sales Price (Retail)*</label><input type="number" step="0.01" name="sales_price" id="priceRetail" required></div>
                <div class="form-group"><label>Wholesale Price*</label><input type="number" step="0.01" name="wholesale_price" id="priceWholesale" required></div>
                <div class="form-group"><label>Promotion Price*</label><input type="number" step="0.01" name="promotion_price" id="pricePromo" required></div>
                <div class="form-group">
                    <label>Migration Control Account* <small>(affects equity accounts)</small></label>
                    <select name="migration_control_account_id" required>
                        <option value="">-Select-</option>
                        <?php foreach ($migrationAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= esc($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" type="button" onclick="closePriceModal()">Close</button>
            </form>
        </div>
    </div>
</div>

<!-- Quick Add Stock modal (from Quick Links - item picker, unlike the per-row Adjust Stock) -->
<div class="modal-backdrop" id="quickAddStockModal">
    <div class="modal-box">
        <div class="modal-header">
            <strong>➕ Add Stock</strong>
            <span style="cursor:pointer;" onclick="closeQuickAddStock()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('stock/adjust') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Item*</label>
                    <select name="item_id" required>
                        <option value="">-Select Item-</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= esc($item['name']) ?> (<?= esc($item['item_code'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date*</label>
                    <input type="date" name="adjustment_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <input type="hidden" name="status" value="increase">
                <div class="form-group">
                    <label>Migration Control Account*</label>
                    <select name="migration_control_account_id" required>
                        <option value="">-Select-</option>
                        <?php foreach ($migrationAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= esc($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Quantity to Add*</label><input type="number" step="0.001" name="adjust_qty" required></div>
                <div class="form-group"><label>Narrative</label><textarea name="narrative" rows="2" placeholder="e.g. new stock received"></textarea></div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" type="button" onclick="closeQuickAddStock()">Close</button>
            </form>
        </div>
    </div>
</div>

<script>
function openQuickAddStock() {
    document.getElementById('quickAddStockModal').classList.add('show');
    document.querySelectorAll('.quick-links-menu.open').forEach(m => m.classList.remove('open'));
}
function closeQuickAddStock() { document.getElementById('quickAddStockModal').classList.remove('show'); }
</script>

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
function closeAdjustModal() { document.getElementById('adjustModal').classList.remove('show'); }

function openPriceModal(id, name, cost, retail, wholesale, promo) {
    document.getElementById('priceItemName').innerText = name;
    document.getElementById('priceForm').action = '<?= site_url('stock/update-price') ?>/' + id;
    document.getElementById('priceCost').value = cost.toFixed(2);
    document.getElementById('priceRetail').value = retail.toFixed(2);
    document.getElementById('priceWholesale').value = wholesale.toFixed(2);
    document.getElementById('pricePromo').value = promo.toFixed(2);
    document.getElementById('priceModal').classList.add('show');
    document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
}
function closePriceModal() { document.getElementById('priceModal').classList.remove('show'); }

$(document).ready(function () {
    initDataTable('#stockTable', { columnDefs: [{ orderable: false, targets: [0, 13] }] });
});
</script>
