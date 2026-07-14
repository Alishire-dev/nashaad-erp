<h2>Add Brand</h2>
<form method="post" action="<?= site_url('brands/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Name*</label><input type="text" name="name" required></div>
    <button class="btn green" type="submit">Save</button>
</form>
