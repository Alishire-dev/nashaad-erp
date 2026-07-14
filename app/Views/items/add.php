<h2>New Item</h2>

<?php if (! empty($validation)): ?>
    <div class="error" style="color:#c0392b; margin-bottom:14px;">
        <ul>
        <?php foreach ($validation as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= site_url('items/add') ?>">
    <?= csrf_field() ?>

    <div class="form-group"><label>Item Name*</label><input type="text" name="name" required></div>

    <div class="form-group">
        <label>Category</label>
        <select name="category_id">
            <option value="">-Select-</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Brand</label>
        <select name="brand_id">
            <option value="">-Select-</option>
            <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
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

    <div class="form-group"><label>Alert Quantity</label><input type="number" step="0.01" name="alert_qty" value="0"></div>

    <div class="form-group">
        <label>Purpose</label>
        <select name="purpose">
            <option value="for_sale">For Sale</option>
            <option value="raw_material">Raw Material</option>
            <option value="both">Both</option>
        </select>
    </div>

    <div class="form-group">
        <label>Manage Stock</label>
        <select name="manage_stock"><option value="yes">Yes</option><option value="no">No</option></select>
    </div>

    <div class="form-group">
        <label>Allow -ve Sale</label>
        <select name="allow_negative_sale"><option value="no">No</option><option value="yes">Yes</option></select>
    </div>

    <div class="form-group"><label>Purchase Price*</label><input type="number" step="0.01" name="purchase_price" required></div>
    <div class="form-group"><label>Sales/Retail Price*</label><input type="number" step="0.01" name="sales_price" required></div>
    <div class="form-group"><label>Wholesale Price</label><input type="number" step="0.01" name="wholesale_price" value="0"></div>
    <div class="form-group"><label>Promo/Minimum Price</label><input type="number" step="0.01" name="minimum_price" value="0"></div>
    <div class="form-group"><label>Profit Margin(%)</label><input type="number" step="0.01" name="profit_margin" value="0"></div>
    <div class="form-group"><label>New Opening Stock</label><input type="number" step="0.01" name="opening_stock" value="0"></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>

    <button class="btn green" type="submit">Save Item</button>
</form>
