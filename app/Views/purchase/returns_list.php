<h2>Purchase Returns</h2>
<a class="btn green" href="<?= site_url('purchase/return/add') ?>">+ New Return</a>
<br><br>

<table id="returnsTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Purchase Ref</th><th>Return Date</th><th>Reason</th><th>Grand Total</th></tr>
    </thead>
    <tbody>
        <?php foreach ($returns as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= esc($r['reference_no'] ?? ('Purchase #' . $r['purchase_id'])) ?></td>
            <td><?= esc($r['return_date']) ?></td>
            <td><?= esc($r['reason'] ?? '-') ?></td>
            <td><?= number_format((float) $r['grand_total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($returns)): ?>
        <tr><td colspan="5">No purchase returns yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#returnsTable');
});
</script>
