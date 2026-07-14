<h2>Add Category</h2>
<form method="post" action="<?= site_url('category/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Name*</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
    <button class="btn green" type="submit">Save</button>
</form>
