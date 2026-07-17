<h2><?= $unit ? 'Edit Unit' : 'Add Unit' ?></h2>
<p style="color:#666; font-size:13px;">Add/Update Unit</p>

<form method="post" action="<?= $unit ? site_url('units/edit/' . $unit['id']) : site_url('units/add') ?>">
    <?= csrf_field() ?>
    <div style="display:flex; gap:20px;">
        <div class="form-group" style="flex:1;">
            <label>Unit Name*</label>
            <input type="text" name="name" required value="<?= esc($unit['name'] ?? '') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Short Name*</label>
            <input type="text" name="short_name" required value="<?= esc($unit['short_name'] ?? '') ?>">
        </div>
    </div>
    <div style="display:flex; gap:20px;">
        <div class="form-group" style="flex:1;">
            <label>Units <small style="font-weight:normal; color:#999;">(how many Base Unit make 1 of this)</small></label>
            <input type="number" step="0.001" name="conversion_factor" value="<?= esc($unit['conversion_factor'] ?? '0') ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Base Unit</label>
            <select name="base_unit_id">
                <option value="">-Select base unit-</option>
                <?php foreach ($allUnits as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($unit['base_unit_id'] ?? null) == $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= esc($unit['description'] ?? '') ?></textarea></div>

    <button class="btn green" type="submit"><?= $unit ? 'Update' : 'Save' ?></button>
    <a class="btn" style="background:#e07b1e;" href="<?= site_url('units') ?>">Close</a>
</form>
