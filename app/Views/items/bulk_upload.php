<h2>Products Bulk Upload</h2>

<p style="color:#666; font-size:14px;">
    File must be a CSV matching the <a href="<?= site_url('items/download-template') ?>">Download Upload Template</a> format.
    Items are matched by <strong>item_code</strong> — existing codes get updated, blank codes create new items.
</p>

<form method="post" action="<?= site_url('items/bulk-upload/process') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:220px;">
            <label>Browse File*</label>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>
        <div class="form-group" style="flex:1; min-width:220px;">
            <label>Auth Code* <small style="font-weight:normal; color:#999;">(not yet enforced — visual parity only for now)</small></label>
            <input type="text" name="auth_code" placeholder="Authorization Code">
        </div>
        <div class="form-group" style="flex:1; min-width:220px;">
            <label>Migration Control Account*</label>
            <select name="migration_control_account_id" required>
                <option value="">~~Select Account~~</option>
                <?php foreach ($migrationAccounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>"><?= esc($acc['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group" style="max-width:220px;">
        <label>Transaction Date*</label>
        <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
    </div>

    <button class="btn" style="background:#3a8fd6;" type="submit">Upload Data ⬆</button>
    <a class="btn" style="background:#c0392b;" href="<?= site_url('items/list') ?>">✕ Cancel</a>
</form>
