<h2>Credit Notes</h2>
<a class="btn" style="background:#e88a2e;" href="<?= site_url('sales/credit-notes/raise') ?>">🎁 Raise Credit Note</a>
<br><br>

<table id="creditNotesTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>SN</th><th>Creditnote Date</th><th>Serial No.</th><th>Invoice No.</th>
            <th>Customer Name</th><th>Total Amount</th><th>Created By</th><th>Branch</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($creditNotes as $i => $cn): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($cn['credit_date']) ?></td>
            <td><?= esc($cn['serial_no']) ?></td>
            <td><?= esc($cn['invoice_no'] ?? '-') ?></td>
            <td><?= esc($cn['customer_name'] ?? 'WALK-IN') ?></td>
            <td><?= number_format((float) $cn['total_amount'], 2) ?></td>
            <td><?= esc($cn['created_by_name'] ?? '-') ?></td>
            <td>Main Branch</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#creditNotesTable'); });</script>
