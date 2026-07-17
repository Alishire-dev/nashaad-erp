<h2>Brands List</h2>
<a class="btn green" href="<?= site_url('brands/add') ?>">+ New Brand</a>
<br><br>

<table id="brandsTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Name</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($brands as $b): ?>
        <tr>
            <td><?= $b['id'] ?></td>
            <td><?= esc($b['name']) ?></td>
            <td><?= ucfirst($b['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#brandsTable');
});
</script>
