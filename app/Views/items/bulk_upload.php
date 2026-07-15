<h2>Bulk Items Upload</h2>
<p>Upload a CSV file matching the <a href="<?= site_url('items/download-template') ?>">Download Upload Template</a> format.
Items are matched by <strong>item_code</strong> — existing codes get updated, blank codes create new items.
Categories and Units are matched by name and created automatically if they don't exist yet.</p>

<form method="post" action="<?= site_url('items/bulk-upload/process') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>CSV File*</label>
        <input type="file" name="csv_file" accept=".csv" required>
    </div>
    <button class="btn green" type="submit">Upload</button>
    <a class="btn" href="<?= site_url('items/list') ?>">Cancel</a>
</form>
