<h2>Purchase Return List</h2>
<a class="btn green" href="<?= site_url('purchase/return/add') ?>">+ New Return</a>
<br><br>

<table id="returnsTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th><input type="checkbox"></th>
            <th>Purchase code</th><th>Purchase Date</th><th>Item Name</th><th>Purchase Qty</th>
            <th>Total</th><th>Purchased Person</th><th>Returned By</th><th>Supplier</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($returns as $r): ?>
        <tr>
            <td><input type="checkbox"></td>
            <td><?= esc($r['purchase_code'] ?? '-') ?></td>
            <td><?= esc($r['return_date']) ?></td>
            <td><?= esc($r['item_name']) ?></td>
            <td><?= number_format((float) $r['quantity'], 0) ?></td>
            <td><?= number_format((float) $r['total_amount'], 2) ?></td>
            <td><?= esc($r['purchased_person'] ?? '-') ?></td>
            <td><?= esc($r['returned_by'] ?? '-') ?></td>
            <td><?= esc($r['supplier_name'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#returnsTable', { columnDefs: [{ orderable: false, targets: [0] }] });
});
</script>
