<style>
    .pay-badge { padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; color:#fff; }
    .pay-paid { background:#27ae60; }
    .pay-partial { background:#e88a2e; }
    .pay-unpaid { background:#c0392b; }
    .pay-cancelled { background:#95a5a6; }
    .action-dropdown { position:relative; display:inline-block; }
    .action-dropdown-menu {
        display:none; position:absolute; right:0; top:100%; background:#fff;
        border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.15);
        min-width:180px; z-index:10;
    }
    .action-dropdown-menu a { display:block; width:100%; text-align:left; padding:8px 14px; color:#333; text-decoration:none; font-size:13px; }
    .action-dropdown-menu a:hover { background:#f4f5f7; }
    .action-dropdown-menu a.add-sale { background:#27ae60; color:#fff; }
    .action-dropdown-menu a.cancel-sale { color:#c0392b; }
    .action-dropdown.open .action-dropdown-menu { display:block; }
</style>

<h2>Sales List</h2>
<a class="btn green" href="<?= site_url('pos') ?>">+ New Sales</a>
<br><br>

<table id="salesTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th></th><th>Date</th><th>InvNo.</th><th>Status</th><th>CreatedBy</th>
            <th>Total</th><th>Paid</th><th>Balance</th><th>PayStatus</th><th>Customer</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $s): ?>
        <?php $balance = (float) $s['grand_total'] - (float) $s['amount_paid']; ?>
        <tr>
            <td><input type="checkbox"></td>
            <td>
                <?= esc(date('d-m-Y H:i:s', strtotime($s['created_at']))) ?>
                <?php if (! empty($s['due_date'])): ?><br><small style="color:#999;">Due: <?= esc($s['due_date']) ?></small><?php endif; ?>
            </td>
            <td><a href="<?= site_url('sales/view/' . $s['id']) ?>"><?= esc($s['invoice_no']) ?></a></td>
            <td style="color:#e88a2e;">Order</td>
            <td><?= esc($s['sales_person_name'] ?? '-') ?></td>
            <td><?= number_format((float) $s['grand_total'], 2) ?></td>
            <td><?= number_format((float) $s['amount_paid'], 2) ?></td>
            <td><?= number_format($balance, 2) ?></td>
            <td><span class="pay-badge pay-<?= esc($s['pay_status']) ?>"><?= esc(ucfirst($s['pay_status'])) ?></span></td>
            <td><?= esc($s['customer_name'] ?? 'WALK-IN') ?></td>
            <td>
                <div class="action-dropdown">
                    <button class="btn" onclick="toggleDropdown(this)">Action ▾</button>
                    <div class="action-dropdown-menu">
                        <a class="add-sale" href="<?= site_url('pos') ?>">🛒 Add Sale</a>
                        <a href="<?= site_url('sales/view/' . $s['id']) ?>">👁 View Sale</a>
                        <a href="<?= site_url('sales/payments/' . $s['id']) ?>">💳 View Payments</a>
                        <a href="<?= site_url('sales/return/' . $s['id']) ?>">🔄 Sales Return</a>
                        <form method="post" action="<?= site_url('sales/cancel/' . $s['id']) ?>"
                              onsubmit="return confirm('Cancel this sale? Stock will be restored.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="cancel-sale" style="background:none; border:none; width:100%; text-align:left; padding:8px 14px; cursor:pointer; font-size:13px;">✕ Cancel Sale</button>
                        </form>
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
    initDataTable('#salesTable', { columnDefs: [{ orderable: false, targets: [0, 10] }] });
});
</script>
