<h2>Debit Notes Report</h2>
<p style="color:#666; font-size:13px;">A debit note reflects a purchase return — it reduces what's owed to the supplier. This report reuses your Purchase Return records rather than a separate ledger.</p>

<form method="get" action="<?= site_url('purchase/debit-notes') ?>" style="display:flex; gap:16px; align-items:flex-end; margin-bottom:20px; flex-wrap:wrap;">
    <div class="form-group" style="margin:0;"><label>From Date:</label><input type="date" name="from" value="<?= esc($from) ?>"></div>
    <div class="form-group" style="margin:0;"><label>To Date:</label><input type="date" name="to" value="<?= esc($to) ?>"></div>
    <button class="btn" style="background:#3a8fd6;" type="submit">🔍 Filter</button>
    <button class="btn green" type="button" onclick="window.print()">📄 Generate Pdf</button>
    <a class="btn" style="background:#e07b1e;" href="<?= site_url('purchase/list') ?>">Close</a>
</form>

<table id="debitTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>Purchase code</th><th>Date</th><th>Item Name</th><th>Qty</th>
            <th>Total</th><th>Reason</th><th>Returned By</th><th>Supplier</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($notes as $n): ?>
        <tr>
            <td><?= esc($n['purchase_code'] ?? '-') ?></td>
            <td><?= esc($n['return_date']) ?></td>
            <td><?= esc($n['item_name']) ?></td>
            <td><?= number_format((float) $n['quantity'], 0) ?></td>
            <td><?= number_format((float) $n['total_amount'], 2) ?></td>
            <td><?= esc($n['reason'] ?? '') ?></td>
            <td><?= esc($n['returned_by'] ?? '-') ?></td>
            <td><?= esc($n['supplier_name'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#debitTable'); });</script>
