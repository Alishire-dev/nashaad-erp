<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:120px; z-index:10;
    }
    .action-dropdown-menu a, .action-dropdown-menu button {
        display:block; width:100%; text-align:left; padding:8px 14px; color:#333;
        text-decoration:none; font-size:13px; background:none; border:none; cursor:pointer;
    }
    .action-dropdown-menu a:hover, .action-dropdown-menu button:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Units List</h2>
<a class="btn green" href="<?= site_url('units/add') ?>">+ New Unit</a>
<br><br>

<table id="unitsTable" class="display" style="width:100%;">
    <thead>
        <tr><th>Unit ID</th><th>Unit Name</th><th>Short Name</th><th>Description</th><th>Status</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php foreach ($units as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= esc($u['name']) ?></td>
            <td><?= esc($u['short_name']) ?></td>
            <td><?= esc($u['description'] ?? '') ?></td>
            <td><?= ucfirst($u['status']) ?></td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('units/edit/' . $u['id']) ?>">✏️ Edit</a>
                        <form method="post" action="<?= site_url('units/delete/' . $u['id']) ?>" onsubmit="return confirm('Delete this unit?');">
                            <?= csrf_field() ?>
                            <button type="submit" style="color:#c0392b;">🗑 Delete</button>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($units)): ?>
        <tr><td colspan="6">No units yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
function toggleDropdown(btn) {
    const dropdown = btn.closest('.action-dropdown');
    document.querySelectorAll('.action-dropdown.open').forEach(d => { if (d !== dropdown) d.classList.remove('open'); });
    dropdown.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
$(document).ready(function () { initDataTable('#unitsTable', { columnDefs: [{ orderable: false, targets: [5] }] }); });
</script>
