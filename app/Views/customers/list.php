<h2>Customers List</h2>
<a class="btn green" href="<?= site_url('customers/add') ?>">+ Add Customer</a>
<br><br>

<table id="customersTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= esc($c['name']) ?></td>
            <td><?= esc($c['phone'] ?? '-') ?></td>
            <td><?= esc($c['email'] ?? '-') ?></td>
            <td><?= ucfirst($c['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () { initDataTable('#customersTable'); });
</script>
