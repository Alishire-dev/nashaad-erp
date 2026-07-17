<h2>Cancelled Sales (Voids)</h2>

<table id="cancelledTable" class="display" style="width:100%;">
    <thead>
        <tr><th>Invoice No</th><th>Date</th><th>Customer</th><th>Grand Total</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $s): ?>
        <tr>
            <td><?= esc($s['invoice_no']) ?></td>
            <td><?= esc($s['sale_date']) ?></td>
            <td><?= esc($s['customer_name'] ?? 'WALK-IN') ?></td>
            <td><?= number_format((float) $s['grand_total'], 2) ?></td>
            <td><a class="btn" href="<?= site_url('sales/view/' . $s['id']) ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#cancelledTable', { columnDefs: [{ orderable: false, targets: [4] }] }); });</script>
