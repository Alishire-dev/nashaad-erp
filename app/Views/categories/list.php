<style>
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:120px; z-index:10;
    }
    .action-dropdown-menu a { display:block; padding:8px 14px; color:#333; text-decoration:none; font-size:13px; }
    .action-dropdown-menu a:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
    .badge-active { background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px; }
    .badge-inactive { background:#999; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px; }
</style>

<h2>Categories List</h2>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <a class="btn green" href="<?= site_url('category/add') ?>">+ Add Category</a>
</div>

<table id="categoriesTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="toggleAll(this)"></th>
            <th>Category ID</th>
            <th>Category Code</th>
            <th>Branch Name</th>
            <th>Category Name</th>
            <th>Parent</th>
            <th>Description</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
            <td><input type="checkbox" class="row-check" value="<?= $c['id'] ?>"></td>
            <td><?= $c['id'] ?></td>
            <td><?= esc($c['category_code']) ?></td>
            <td><?= esc($c['branch_name'] ?? $branchName) ?></td>
            <td><?= esc($c['name']) ?></td>
            <td><?= esc($c['parent_name'] ?? '-') ?></td>
            <td><?= esc($c['description'] ?? '') ?></td>
            <td>
                <?php if ($c['status'] === 'active'): ?>
                    <span class="badge-active">Active</span>
                <?php else: ?>
                    <span class="badge-inactive">Inactive</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('category/edit/' . $c['id']) ?>">Edit</a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
function toggleAll(source) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
}

$(document).ready(function () {
    initDataTable('#categoriesTable', { columnDefs: [{ orderable: false, targets: [0, 8] }] });
});
</script>
