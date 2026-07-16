<h2>Sub Account Type List</h2>
<a class="btn green" href="<?= site_url('accounting/sub-account-types/add') ?>">+ Add Sub Account Type</a>
<br><br>

<table id="subTypesTable" class="display" style="width:100%;">
    <thead>
        <tr><th>Sub Account Code</th><th>Sub Account Name</th><th>Account Type</th><th>Description</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($subTypes as $s): ?>
        <tr>
            <td><?= esc($s['sub_account_code']) ?></td>
            <td><?= esc($s['name']) ?></td>
            <td><?= esc($s['account_type_name'] ?? '-') ?></td>
            <td><?= esc($s['description'] ?? '') ?></td>
            <td><?= ucfirst($s['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($subTypes)): ?>
        <tr><td colspan="5">No sub account types yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#subTypesTable'); });</script>
