<h2>Add Category</h2>
<p style="color:#666;">Please enter valid data</p>

<form method="post" action="<?= site_url('category/add') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
        <label>Branch Name*</label>
        <select disabled>
            <option><?= esc($branchName) ?></option>
        </select>
        <small>Single-branch setup — this will become selectable when multi-branch is enabled.</small>
    </div>

    <div class="form-group">
        <label>Show On POS*</label>
        <select name="show_on_pos" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select>
    </div>

    <div class="form-group"><label>Category Name*</label><input type="text" name="name" required></div>

    <div class="form-group">
        <label>Parent</label>
        <select name="parent_id">
            <option value="">-- No Parent (Top Level) --</option>
            <?php foreach ($allCategories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>

    <button class="btn green" type="submit">Save</button>
    <a class="btn" style="background:#e07b1e;" href="<?= site_url('category/view') ?>">Close</a>
</form>
