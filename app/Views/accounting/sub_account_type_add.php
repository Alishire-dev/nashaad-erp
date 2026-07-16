<h2>Add Sub Account Type</h2>
<form method="post" action="<?= site_url('accounting/sub-account-types/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Sub Account Name*</label><input type="text" name="name" required></div>
    <div class="form-group">
        <label>Account Type*</label>
        <select name="account_type_id" required>
            <option value="">-Select-</option>
            <?php foreach ($accountTypes as $t): ?>
                <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
    <button class="btn green" type="submit">Save</button>
    <a class="btn" href="<?= site_url('accounting/sub-account-types') ?>">Cancel</a>
</form>
