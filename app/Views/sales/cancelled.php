<style>
    .pay-badge { padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; color:#fff; }
    .pay-cancelled { background:#7f8c8d; }
    .flag-badge { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
    .flag-no { background:#3a8fd6; color:#fff; }
    .flag-yes { background:#e88a2e; color:#fff; }
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:180px; z-index:10;
    }
    .action-dropdown-menu a { display:block; width:100%; text-align:left; padding:8px 14px; color:#333; text-decoration:none; font-size:13px; }
    .action-dropdown-menu a:hover { background:#f4f5f7; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Cancelled Sales List</h2>
<p style="color:#666; font-size:13px;">View/Search Sold Items</p>

<table id="cancelledTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>SN</th><th>Sales Date</th><th>Invoice No.</th><th>Sales Status</th><th>Created by</th>
            <th>Total</th><th>Paid Amt</th><th>Balance</th><th>Pay Status</th><th>Flagged</th>
            <th>Customer Name</th><th>Branch</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $i => $s): ?>
        <?php $balance = (float) $s['grand_total'] - (float) $s['amount_paid']; ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($s['sale_date']) ?></td>
            <td><a href="<?= site_url('sales/view/' . $s['id']) ?>"><?= esc($s['invoice_no']) ?></a></td>
            <td style="color:#e88a2e;">Order</td>
            <td><?= esc($s['created_by_name'] ?? '-') ?></td>
            <td><?= number_format((float) $s['grand_total'], 2) ?></td>
            <td><?= number_format((float) $s['amount_paid'], 2) ?></td>
            <td><?= number_format($balance, 2) ?></td>
            <td><span class="pay-badge pay-cancelled">Cancelled</span></td>
            <td><span class="flag-badge <?= $s['flagged'] ? 'flag-yes' : 'flag-no' ?>"><?= $s['flagged'] ? 'Yes ⚠' : 'No' ?></span></td>
            <td><?= esc($s['customer_name'] ?? 'WALK-IN') ?></td>
            <td>Main Branch</td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a href="<?= site_url('sales/view/' . $s['id']) ?>">👁 View sales</a>
                        <a href="<?= site_url('sales/credit-notes/from-cancelled/' . $s['id'] . '/thermal') ?>" target="_blank">🖨 Thermal Credit Note</a>
                        <a href="<?= site_url('sales/credit-notes/from-cancelled/' . $s['id'] . '/a4') ?>" target="_blank">🖨 A4 Credit Note</a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>
$(document).ready(function () {
    initDataTable('#cancelledTable', { columnDefs: [{ orderable: false, targets: [12] }] });
});
</script>
