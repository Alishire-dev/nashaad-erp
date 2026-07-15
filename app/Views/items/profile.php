<style>
    .profile-card { background:#fff; border-radius:6px; overflow:hidden; margin-bottom:20px; }
    .profile-card h3 { background:#e07b1e; color:#fff; padding:10px 16px; margin:0; font-size:15px; }
    .profile-body { padding:16px; }
    .profile-row { display:flex; flex-wrap:wrap; gap:24px; margin-bottom:10px; }
    .profile-field { min-width:180px; }
    .profile-field label { color:#e07b1e; font-weight:bold; font-size:13px; display:block; }
    .profile-field span { font-size:14px; }
    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:0; border-radius:6px; width:600px; max-width:90vw; }
    .modal-header { background:#e07b1e; color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
    .modal-body { padding:20px; }
    .tab-buttons { display:flex; gap:8px; border-bottom:2px solid #eee; margin-bottom:16px; }
    .tab-buttons button { background:none; border:none; padding:10px 4px; cursor:pointer; font-size:14px; color:#666; }
    .tab-buttons button.active { color:#e07b1e; border-bottom:2px solid #e07b1e; margin-bottom:-2px; }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }
</style>

<h2>Item Profile</h2>

<div class="profile-card">
    <h3>Basic Info</h3>
    <div class="profile-body">
        <div style="display:flex; gap:24px;">
            <div>
                <?php if (! empty($item['image'])): ?>
                    <img src="<?= base_url('uploads/items/' . $item['image']) ?>" style="width:100px; height:100px; object-fit:cover; border-radius:6px;">
                <?php else: ?>
                    <div style="width:100px; height:100px; background:#eee; border-radius:6px;"></div>
                <?php endif; ?>
                <br><br>
                <a class="btn" style="background:#2980b9;" href="<?= site_url('items/edit/' . $item['id']) ?>">Edit Info</a>
            </div>
            <div style="flex:1;">
                <div class="profile-row">
                    <div class="profile-field"><label>Name:</label><span><?= esc($item['name']) ?></span></div>
                    <div class="profile-field"><label>Code:</label><span><?= esc($item['item_code'] ?? '-') ?></span></div>
                    <div class="profile-field"><label>Unit:</label><span><?= esc($item['unit_name'] ?? '-') ?></span></div>
                    <div class="profile-field"><label>Brand:</label><span><?= esc($item['brand_name'] ?? '-') ?></span></div>
                </div>
                <div class="profile-row">
                    <div class="profile-field"><label>Active Price:</label><span>KES <?= number_format((float) $item['sales_price'], 2) ?></span></div>
                    <div class="profile-field"><label>Category:</label><span><?= esc($item['category_name'] ?? '-') ?></span></div>
                    <div class="profile-field"><label>Alert Qty:</label><span><?= number_format((float) $item['alert_qty'], 0) ?></span></div>
                    <div class="profile-field"><label>Tax Type:</label><span><?= ucfirst($item['tax_type']) ?></span></div>
                </div>
                <div class="profile-row">
                    <div class="profile-field"><label>Expiry Date:</label><span><?= esc($item['expiry_date'] ?: '-') ?></span></div>
                </div>
                <div class="profile-row">
                    <div class="profile-field" style="min-width:100%;"><label>Description:</label><span><?= esc($item['description'] ?? '') ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="profile-card">
    <h3>Purchase Batches</h3>
    <div class="profile-body">
        <p style="color:#888; font-size:13px; margin-top:0;">
            Note: stock in this system is tracked as one running total per item, not
            per-batch. "Stocked Qty" below is real (from purchase records) — "Balance"
            isn't shown per-batch because it isn't tracked at that level; see Stock
            Manager for the item's current total stock.
        </p>
        <table>
            <tr><th>Branch</th><th>Date</th><th>Cost Price</th><th>Stocked Qty</th></tr>
            <?php foreach ($batches as $b): ?>
            <tr>
                <td><?= esc($b['branch_name'] ?? '-') ?></td>
                <td><?= esc($b['purchase_date']) ?></td>
                <td><?= number_format((float) $b['cost_price'], 2) ?></td>
                <td><?= number_format((float) $b['quantity'], 3) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($batches)): ?>
            <tr><td colspan="4">No purchase history for this item yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="profile-card">
    <h3>Child Items (via Item Conversion)</h3>
    <div class="profile-body">
        <button class="btn green" onclick="openConversionModal()">+ Create Conversion</button>
        <br><br>
        <table>
            <tr><th>Item Name</th><th>Branch</th><th>Purchase Price</th><th>Sale Price</th><th>Stock</th><th>Reorder Level</th><th>Status</th></tr>
            <?php foreach ($children as $c): ?>
            <tr>
                <td><?= esc($c['child_name']) ?></td>
                <td><?= esc($c['branch_name'] ?? '-') ?></td>
                <td><?= number_format((float) $c['child_purchase_price'], 2) ?></td>
                <td><?= number_format((float) $c['child_sales_price'], 2) ?></td>
                <td><?= number_format((float) $c['child_stock'], 2) ?></td>
                <td><?= number_format((float) $c['child_alert_qty'], 0) ?></td>
                <td><?= ucfirst($c['child_status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($children)): ?>
            <tr><td colspan="7">No record found!!!</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Item Conversion modal -->
<div class="modal-backdrop" id="conversionModal">
    <div class="modal-box">
        <div class="modal-header">
            <strong>Item Conversion</strong>
            <span style="cursor:pointer;" onclick="closeConversionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('items/conversion-create/' . $item['id']) ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label>Item Name*</label><input type="text" name="name" required></div>
                <div class="form-group">
                    <label>Conversion Rate* <small>(how many of this item make 1 child unit)</small></label>
                    <input type="number" step="0.001" name="conversion_rate" required>
                </div>
                <div class="form-group">
                    <label>Unit of Measure*</label>
                    <select name="unit_id" required>
                        <option value="">-Select-</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Sales Price*</label><input type="number" step="0.01" name="sales_price" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
                <button class="btn green" type="submit">Submit</button>
                <button class="btn" type="button" onclick="closeConversionModal()">Close</button>
            </form>
        </div>
    </div>
</div>

<script>
function openConversionModal() { document.getElementById('conversionModal').classList.add('show'); }
function closeConversionModal() { document.getElementById('conversionModal').classList.remove('show'); }
</script>
