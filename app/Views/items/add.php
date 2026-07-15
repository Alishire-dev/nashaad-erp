<style>
    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:20px; border-radius:6px; width:320px; }
    .field-with-add { display:flex; gap:6px; align-items:flex-end; }
    .field-with-add select { flex:1; }
    .add-inline-btn { background:#2980b9; color:#fff; border:none; border-radius:4px; width:36px; height:36px; font-size:18px; cursor:pointer; }
    .img-preview { max-width:100px; max-height:100px; display:block; margin-top:8px; border:1px solid #ccc; border-radius:4px; }
    .info-icon { font-size:11px; color:#888; cursor:help; }
</style>

<h2><?= $item ? 'Edit Item' : 'New Item' ?></h2>

<?php if (! empty($validation)): ?>
    <div style="color:#c0392b; margin-bottom:14px;">
        <ul><?php foreach ($validation as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post"
      action="<?= $item ? site_url('items/edit/' . $item['id']) : site_url('items/add') ?>"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group"><label>Item Name*</label>
        <input type="text" name="name" required value="<?= esc($item['name'] ?? '') ?>">
        <?php if ($item): ?>
            <small>Item Code: <strong><?= esc($item['item_code'] ?? '-') ?></strong> (auto-assigned, not editable)</small>
        <?php endif; ?>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Category</label>
            <div class="field-with-add">
                <select name="category_id" id="categorySelect">
                    <option value="">-Select-</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($item['category_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                            <?= esc($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="add-inline-btn" onclick="openModal('categoryModal')">+</button>
            </div>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Brand</label>
            <div class="field-with-add">
                <select name="brand_id" id="brandSelect">
                    <option value="">-Select-</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($item['brand_id'] ?? null) == $b['id'] ? 'selected' : '' ?>>
                            <?= esc($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="add-inline-btn" onclick="openModal('brandModal')">+</button>
            </div>
        </div>

        <div class="form-group" style="flex:1;">
            <label>Unit of Measure*</label>
            <div class="field-with-add">
                <select name="unit_id" id="unitSelect" required>
                    <option value="">-Select-</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($item['unit_id'] ?? null) == $u['id'] ? 'selected' : '' ?>>
                            <?= esc($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="add-inline-btn" onclick="openModal('unitModal')">+</button>
            </div>
        </div>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>SKU / Serial Key Unit <span class="info-icon" title="Used for barcode scanning at POS">ⓘ</span></label>
            <input type="text" name="sku" placeholder="For scanning" value="<?= esc($item['sku'] ?? '') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Alert Quantity</label>
            <input type="number" step="0.01" name="alert_qty" value="<?= esc($item['alert_qty'] ?? '0') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Expiry Date <small>(dd-mm-yyyy)</small></label>
            <input type="date" name="expiry_date" value="<?= esc($item['expiry_date'] ?? '') ?>">
        </div>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Purpose <span class="info-icon" title="State if for sale or raw material used internally">ⓘ</span></label>
            <select name="purpose">
                <?php $purpose = $item['purpose'] ?? 'for_sale'; ?>
                <option value="for_sale" <?= $purpose === 'for_sale' ? 'selected' : '' ?>>For Sale</option>
                <option value="raw_material" <?= $purpose === 'raw_material' ? 'selected' : '' ?>>Raw Material</option>
                <option value="both" <?= $purpose === 'both' ? 'selected' : '' ?>>Both</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Manage Stock <span class="info-icon" title="No = stock quantity is never tracked or decremented for this item">ⓘ</span></label>
            <?php $manageStock = $item['manage_stock'] ?? 1; ?>
            <select name="manage_stock">
                <option value="yes" <?= $manageStock == 1 ? 'selected' : '' ?>>Yes</option>
                <option value="no" <?= $manageStock == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Allow -ve Sale <span class="info-icon" title="Yes = can sell even when stock shows zero or below">ⓘ</span></label>
            <?php $allowNeg = $item['allow_negative_sale'] ?? 0; ?>
            <select name="allow_negative_sale">
                <option value="no" <?= $allowNeg == 0 ? 'selected' : '' ?>>No</option>
                <option value="yes" <?= $allowNeg == 1 ? 'selected' : '' ?>>Yes</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Order Item <span class="info-icon" title="No = exclude from reorder/purchase-suggestion lists (e.g. discontinued items)">ⓘ</span></label>
            <?php $orderItem = $item['order_item'] ?? 1; ?>
            <select name="order_item">
                <option value="yes" <?= $orderItem == 1 ? 'selected' : '' ?>>Yes</option>
                <option value="no" <?= $orderItem == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Item Image</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(event)">
        <img id="imgPreview" class="img-preview"
             src="<?= ! empty($item['image']) ? base_url('uploads/items/' . $item['image']) : '' ?>"
             style="<?= empty($item['image']) ? 'display:none;' : '' ?>">
    </div>

    <hr>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Tax Category*</label>
            <select name="tax_category_id" required>
                <?php foreach ($taxCategories as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($item['tax_category_id'] ?? 1) == $t['id'] ? 'selected' : '' ?>>
                        <?= esc($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Tax Type*</label>
            <?php $taxType = $item['tax_type'] ?? 'inclusive'; ?>
            <select name="tax_type" required>
                <option value="inclusive" <?= $taxType === 'inclusive' ? 'selected' : '' ?>>Inclusive</option>
                <option value="exclusive" <?= $taxType === 'exclusive' ? 'selected' : '' ?>>Exclusive</option>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Purchase Price* <small>(cost from supplier)</small></label>
            <input type="number" step="0.01" name="purchase_price" id="purchasePrice" required
                   value="<?= esc($item['purchase_price'] ?? '') ?>" onchange="calcMargin()">
        </div>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Sales/Retail Price*</label>
            <input type="number" step="0.01" name="sales_price" id="salesPrice" required
                   value="<?= esc($item['sales_price'] ?? '') ?>" onchange="calcMargin()">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Wholesale Price*</label>
            <input type="number" step="0.01" name="wholesale_price" value="<?= esc($item['wholesale_price'] ?? '0') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Promo/Minimum Price*</label>
            <input type="number" step="0.01" name="minimum_price" value="<?= esc($item['minimum_price'] ?? '0') ?>">
        </div>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Profit Margin(%) <small>auto-calculated, editable</small></label>
            <input type="number" step="0.01" name="profit_margin" id="profitMargin" value="<?= esc($item['profit_margin'] ?? '0') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Price Change Affect All Branches?* <span class="info-icon" title="Single-branch setup right now — stored for when multi-branch is enabled">ⓘ</span></label>
            <?php $priceChangeAll = $item['price_change_all_branches'] ?? 0; ?>
            <select name="price_change_all_branches" required>
                <option value="no" <?= $priceChangeAll == 0 ? 'selected' : '' ?>>No</option>
                <option value="yes" <?= $priceChangeAll == 1 ? 'selected' : '' ?>>Yes</option>
            </select>
        </div>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
            <label>Sales Commission(%)</label>
            <input type="number" step="0.01" name="sales_commission" value="<?= esc($item['sales_commission'] ?? '0') ?>">
        </div>
        <?php if (! $item): ?>
        <div class="form-group" style="flex:1;">
            <label>New Opening Stock</label>
            <input type="number" step="0.01" name="opening_stock" value="0">
        </div>
        <?php else: ?>
        <div class="form-group" style="flex:1;">
            <label>Current Stock</label>
            <input type="text" value="<?= number_format((float) $item['current_stock'], 2) ?>" disabled>
            <small><a href="<?= site_url('stock/manager') ?>">Adjust in Stock Manager</a> — kept separate so every stock change is logged</small>
        </div>
        <?php endif; ?>
    </div>

    <div class="form-group"><label>Description</label>
        <textarea name="description" rows="3"><?= esc($item['description'] ?? '') ?></textarea>
    </div>

    <button class="btn green" type="submit"><?= $item ? 'Update Item' : 'Save Item' ?></button>
</form>

<!-- Quick-add modals: Category -->
<div class="modal-backdrop" id="categoryModal">
    <div class="modal-box">
        <h3>Quick Add Category</h3>
        <input type="text" id="quickCategoryName" placeholder="Category name">
        <br><br>
        <button class="btn green" onclick="quickAdd('category')">Save</button>
        <button class="btn" onclick="closeModal('categoryModal')">Cancel</button>
    </div>
</div>

<!-- Quick-add modals: Brand -->
<div class="modal-backdrop" id="brandModal">
    <div class="modal-box">
        <h3>Quick Add Brand</h3>
        <input type="text" id="quickBrandName" placeholder="Brand name">
        <br><br>
        <button class="btn green" onclick="quickAdd('brand')">Save</button>
        <button class="btn" onclick="closeModal('brandModal')">Cancel</button>
    </div>
</div>

<!-- Quick-add modals: Unit -->
<div class="modal-backdrop" id="unitModal">
    <div class="modal-box">
        <h3>Quick Add Unit</h3>
        <input type="text" id="quickUnitName" placeholder="Unit name (e.g. Pieces)">
        <input type="text" id="quickUnitShort" placeholder="Short name (e.g. pcs)" style="margin-top:8px;">
        <br><br>
        <button class="btn green" onclick="quickAdd('unit')">Save</button>
        <button class="btn" onclick="closeModal('unitModal')">Cancel</button>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function previewImage(event) {
    const preview = document.getElementById('imgPreview');
    const file = event.target.files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}

function calcMargin() {
    const cost = parseFloat(document.getElementById('purchasePrice').value) || 0;
    const price = parseFloat(document.getElementById('salesPrice').value) || 0;
    if (cost > 0) {
        const margin = ((price - cost) / cost) * 100;
        document.getElementById('profitMargin').value = margin.toFixed(2);
    }
}

const quickAddConfig = {
    category: { url: '<?= site_url('category/quick-add') ?>', select: 'categorySelect', modal: 'categoryModal' },
    brand:    { url: '<?= site_url('brands/quick-add') ?>',   select: 'brandSelect',    modal: 'brandModal' },
    unit:     { url: '<?= site_url('units/quick-add') ?>',    select: 'unitSelect',     modal: 'unitModal' },
};

async function quickAdd(type) {
    const cfg = quickAddConfig[type];
    const name = document.getElementById('quick' + type.charAt(0).toUpperCase() + type.slice(1) + 'Name').value.trim();
    if (!name) { alert('Enter a name.'); return; }

    const body = new URLSearchParams({ name });
    if (type === 'unit') {
        body.append('short_name', document.getElementById('quickUnitShort').value.trim() || name);
    }

    const res = await fetch(cfg.url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body,
    });
    const data = await res.json();

    const select = document.getElementById(cfg.select);
    const opt = document.createElement('option');
    opt.value = data.id;
    opt.text = data.name;
    opt.selected = true;
    select.appendChild(opt);

    closeModal(cfg.modal);
}
</script>
