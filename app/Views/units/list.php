<h2>Units List</h2>
<a class="btn green" href="<?= site_url('units/add') ?>">+ New Unit</a>
<br><br>

<table id="unitsTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Name</th><th>Short Name</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($units as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= esc($u['name']) ?></td>
            <td><?= esc($u['short_name']) ?></td>
            <td><?= ucfirst($u['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($units)): ?>
        <tr><td colspan="4">No units yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#unitsTable');
});
</script>
