<h2><?= $category ? 'Edit Category' : 'Add Category' ?></h2>
<p style="color:#666;">Please enter valid data</p>

<form method="post"
      action="<?= $category ? site_url('category/edit/' . $category['id']) : site_url('category/add') ?>">
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
        <?php $showOnPos = $category['show_on_pos'] ?? 1; ?>
        <select name="show_on_pos" required>
            <option value="yes" <?= $showOnPos == 1 ? 'selected' : '' ?>>Yes</option>
            <option value="no" <?= $showOnPos == 0 ? 'selected' : '' ?>>No</option>
        </select>
    </div>

    <div class="form-group"><label>Category Name*</label>
        <input type="text" name="name" required value="<?= esc($category['name'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Parent</label>
        <select name="parent_id">
            <option value="">-- No Parent (Top Level) --</option>
            <?php foreach ($allCategories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($category['parent_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                    <?= esc($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"><label>Description</label>
        <textarea name="description" rows="3"><?= esc($category['description'] ?? '') ?></textarea>
    </div>

    <button class="btn green" type="submit"><?= $category ? 'Update' : 'Save' ?></button>
    <a class="btn" style="background:#e07b1e;" href="<?= site_url('category/view') ?>">Close</a>
</form>
