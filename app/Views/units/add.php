<h2>Add Unit</h2>
<form method="post" action="<?= site_url('units/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Unit Name*</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Short Name*</label><input type="text" name="short_name" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
    <button class="btn green" type="submit">Save</button>
</form>
