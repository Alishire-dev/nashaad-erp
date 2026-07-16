<h2>Sales List</h2>

<table id="salesTable" class="display" style="width:100%;">
    <thead>
        <tr><th>#</th><th>Invoice No</th><th>Customer</th><th>Date</th><th>Grand Total</th><th>Paid</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= esc($s['invoice_no']) ?></td>
            <td><?= esc($s['customer_name'] ?? 'WALK-IN') ?></td>
            <td><?= esc($s['sale_date']) ?></td>
            <td><?= number_format((float) $s['grand_total'], 2) ?></td>
            <td><?= number_format((float) $s['amount_paid'], 2) ?></td>
            <td><a class="btn" href="<?= site_url('sales/view/' . $s['id']) ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($sales)): ?>
        <tr><td colspan="7">No sales yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () { initDataTable('#salesTable', { columnDefs: [{ orderable: false, targets: [6] }] }); });
</script>
