<h2>Accounts Type List</h2>
<a class="btn green" href="<?= site_url('accounting/account-types/add') ?>">+ Add Account Type</a>
<br><br>

<table id="accountTypesTable" class="display" style="width:100%;">
    <thead><tr><th>#</th><th>Name</th><th>Status</th></tr></thead>
    <tbody>
        <?php foreach ($accountTypes as $t): ?>
        <tr><td><?= $t['id'] ?></td><td><?= esc($t['name']) ?></td><td><?= ucfirst($t['status']) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#accountTypesTable'); });</script>
