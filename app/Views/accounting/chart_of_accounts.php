<h2>Charts of Account List</h2>
<a class="btn green" href="<?= site_url('accounting/chart-of-accounts/add') ?>">+ Add Chart of Account</a>
<br><br>

<table id="chartTable" class="display" style="width:100%;">
    <thead>
        <tr><th>Account Name</th><th>Gl Code</th><th>Sub. Acc Type</th><th>Acc. Type</th><th>Description</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($accounts as $a): ?>
        <tr>
            <td><?= esc($a['account_name']) ?></td>
            <td><?= esc($a['gl_code']) ?></td>
            <td><?= esc($a['sub_account_name'] ?? '-') ?></td>
            <td><?= esc($a['account_type_name'] ?? '-') ?></td>
            <td><?= esc($a['description'] ?? '') ?></td>
            <td>
                <?php if ($a['status'] === 'active'): ?>
                    <span style="background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Active</span>
                <?php else: ?>
                    <span style="background:#c0392b; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Inactive</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#chartTable'); });</script>
