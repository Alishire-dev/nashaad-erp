<h2>Add Account Type</h2>
<form method="post" action="<?= site_url('accounting/account-types/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Name*</label><input type="text" name="name" required placeholder="e.g. ASSETS, LIABILITIES"></div>
    <button class="btn green" type="submit">Save</button>
    <a class="btn" href="<?= site_url('accounting/account-types') ?>">Cancel</a>
</form>
