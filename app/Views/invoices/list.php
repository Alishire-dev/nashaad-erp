<style>
    .pay-badge { padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; color:#fff; }
    .pay-paid { background:#27ae60; }
    .pay-partial { background:#e88a2e; }
    .pay-unpaid { background:#c0392b; }
</style>

<h2>Invoices List</h2>
<a class="btn" style="background:#3a8fd6;" href="<?= site_url('invoices/add') ?>">+ New Invoice</a>
<br><br>

<table id="invoicesTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>SN</th><th>Invoice Date</th><th>Invoice No.</th><th>Created by</th>
            <th>Invoice Amt</th><th>Paid Amt</th><th>Balance</th><th>Pay Status</th>
            <th>Customer Name</th><th>Branch</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($invoices as $i => $inv): ?>
        <?php $balance = (float) $inv['grand_total'] - (float) $inv['amount_paid']; ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($inv['invoice_date']) ?></td>
            <td><?= esc($inv['invoice_no']) ?></td>
            <td><?= esc($inv['created_by_name'] ?? '-') ?></td>
            <td><?= number_format((float) $inv['grand_total'], 2) ?></td>
            <td><?= number_format((float) $inv['amount_paid'], 2) ?></td>
            <td><?= number_format($balance, 2) ?></td>
            <td><span class="pay-badge pay-<?= esc($inv['pay_status']) ?>"><?= esc(ucfirst($inv['pay_status'])) ?></span></td>
            <td><?= esc($inv['customer_name'] ?? 'WALK-IN') ?></td>
            <td>Main Branch</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#invoicesTable'); });</script>
