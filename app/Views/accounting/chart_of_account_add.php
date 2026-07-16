<h2>Add Chart of Account</h2>
<p style="color:#666; font-size:13px;">GL Code is auto-assigned sequentially (GLxxxx) — not editable here.</p>
<form method="post" action="<?= site_url('accounting/chart-of-accounts/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Account Name*</label><input type="text" name="account_name" required></div>
    <div class="form-group">
        <label>Sub Account Type*</label>
        <select name="sub_account_type_id" required>
            <option value="">-Select-</option>
            <?php foreach ($subTypes as $s): ?>
                <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?> (<?= esc($s['account_type_name'] ?? '') ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
    <button class="btn green" type="submit">Save</button>
    <a class="btn" href="<?= site_url('accounting/chart-of-accounts') ?>">Cancel</a>
</form>
