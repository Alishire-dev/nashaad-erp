<h2>Sales Return</h2>

<table id="salesReturnTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>Invoice No</th><th>Return Date</th><th>Item Name</th><th>Qty</th>
            <th>Total</th><th>Condition</th><th>Narrative</th><th>Returned By</th><th>Customer</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($returns as $r): ?>
        <tr>
            <td><?= esc($r['invoice_no'] ?? '-') ?></td>
            <td><?= esc($r['return_date']) ?></td>
            <td><?= esc($r['item_name']) ?></td>
            <td><?= number_format((float) $r['quantity'], 0) ?></td>
            <td><?= number_format((float) $r['total_amount'], 2) ?></td>
            <td><?= $r['good_condition'] === 'yes' ? 'Good' : 'Damaged' ?></td>
            <td><?= esc($r['narrative'] ?? '') ?></td>
            <td><?= esc($r['returned_by'] ?? '-') ?></td>
            <td><?= esc($r['customer_name'] ?? 'WALK-IN') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#salesReturnTable'); });</script>
