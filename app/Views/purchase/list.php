<h2>Purchase List</h2>
<a class="btn green" href="<?= site_url('purchase/add') ?>">+ New Purchase</a>
<a class="btn" href="<?= site_url('purchase/returns') ?>">Purchase Returns</a>
<br><br>

<table id="purchaseTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Supplier</th><th>Ref No</th><th>Date</th><th>Status</th><th>Grand Total</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($purchases as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= esc($p['supplier_name'] ?? '-') ?></td>
            <td><?= esc($p['reference_no'] ?? '-') ?></td>
            <td><?= esc($p['purchase_date']) ?></td>
            <td><?= ucfirst($p['status']) ?></td>
            <td><?= number_format((float) $p['grand_total'], 2) ?></td>
            <td><a class="btn" href="<?= site_url('purchase/view/' . $p['id']) ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#purchaseTable', { columnDefs: [{ orderable: false, targets: [6] }] });
});
</script>
