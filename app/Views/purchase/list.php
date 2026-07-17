<style>
    .pay-badge { padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; color:#fff; }
    .pay-paid { background:#27ae60; }
    .pay-partial { background:#e88a2e; }
    .pay-unpaid { background:#95a5a6; }
    .pay-cancelled { background:#c0392b; }
    .pay-requisition { background:#8e44ad; }
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:180px; z-index:10;
    }
    .action-dropdown-menu a {
        display:block; width:100%; text-align:left; padding:8px 14px; color:#333;
        text-decoration:none; font-size:13px;
    }
    .action-dropdown-menu a:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Purchase List</h2>
<a class="btn green" href="<?= site_url('purchase/add') ?>">+ New Purchase</a>
<a class="btn" href="<?= site_url('purchase/returns') ?>">Purchase Returns</a>
<br><br>

<table id="purchaseTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th></th><th>Date</th><th>Invoice No</th><th>Reference</th><th>Supplier Name</th>
            <th>Total Amt</th><th>W/Tax</th><th>Paid Amt</th><th>Balance</th><th>Pay Status</th>
            <th>Created by</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($purchases as $p): ?>
        <?php $balance = (float) $p['grand_total'] - (float) $p['amount_paid']; ?>
        <tr>
            <td><input type="checkbox"></td>
            <td><?= esc(date('d-m-Y', strtotime($p['purchase_date']))) ?></td>
            <td><?= esc($p['reference_no'] ?? '-') ?></td>
            <td></td>
            <td><?= esc($p['supplier_name'] ?? '-') ?></td>
            <td><?= number_format((float) $p['grand_total'], 2) ?></td>
            <td>0.00</td>
            <td><?= number_format((float) $p['amount_paid'], 2) ?></td>
            <td><?= number_format($balance, 2) ?></td>
            <td><span class="pay-badge pay-<?= esc($p['pay_status']) ?>"><?= esc(ucfirst($p['pay_status'])) ?></span></td>
            <td><?= esc($p['created_by_name'] ?? '-') ?></td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('purchase/view/' . $p['id']) ?>">👁 View Purchase</a>
                        <a href="<?= site_url('purchase/payments/' . $p['id']) ?>">👁 View Payments</a>
                        <a href="<?= site_url('purchase/lpo/' . $p['id']) ?>" target="_blank">📄 Purchase Order</a>
                        <a href="<?= site_url('purchase/lpo-no-price/' . $p['id']) ?>" target="_blank">📄 Purchase Order (No Price)</a>
                        <a href="<?= site_url('purchase/thermal/' . $p['id']) ?>" target="_blank">🖨 Thermal Print</a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
function toggleDropdown(btn) {
    const dropdown = btn.closest('.action-dropdown');
    document.querySelectorAll('.action-dropdown.open').forEach(d => { if (d !== dropdown) d.classList.remove('open'); });
    dropdown.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
$(document).ready(function () {
    initDataTable('#purchaseTable', { columnDefs: [{ orderable: false, targets: [0, 11] }] });
});
</script>
