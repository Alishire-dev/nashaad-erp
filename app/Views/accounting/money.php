<style>
    .money-tabs { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
    .money-tabs a {
        background:#fff; border:1px solid #dde1e8; padding:9px 16px; border-radius:6px;
        color:#2c3038; text-decoration:none; font-size:14px; display:flex; align-items:center; gap:6px;
    }
    .money-tabs a.active { background:#e88a2e; color:#fff; border-color:#e88a2e; }
    .money-tabs a.pending { opacity:.45; cursor:not-allowed; }
</style>

<h2>Manage Money</h2>

<div class="money-tabs">
    <a href="<?= site_url('accounting/money') ?>" class="active">📋 Payments</a>
    <a href="<?= site_url('accounting/money/make-payment') ?>">💸 Make Payment</a>
    <a href="<?= site_url('accounting/money/receive-payment') ?>">💰 Receive Payment</a>
    <a href="#" class="pending" onclick="return false;" title="Not yet scheduled">🔁 Funds Transfer <span class="soon-badge">soon</span></a>
    <a href="#" class="pending" onclick="return false;" title="Not yet scheduled">↩ Payment Refunds <span class="soon-badge">soon</span></a>
    <a href="#" class="pending" onclick="return false;" title="Not yet scheduled">🏦 Unpaid Cheques <span class="soon-badge">soon</span></a>
    <a href="#" class="pending" onclick="return false;" title="Not yet scheduled">⚠ Cheque Fines <span class="soon-badge">soon</span></a>
</div>

<h3>Payments</h3>
<table id="paymentsTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>##</th><th>TransBy</th><th>PaymentDate</th><th>PayMode</th><th>Description</th>
            <th>Account</th><th>VoucherNo</th><th>In</th><th>Out</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $t): ?>
        <tr>
            <td><a class="btn" style="padding:4px 10px; font-size:12px;">Action ▾</a></td>
            <td><?= esc($t['trans_by_name'] ?? '-') ?></td>
            <td><?= esc($t['payment_date']) ?></td>
            <td><?= esc(strtoupper($t['pay_mode'] ?? '-')) ?></td>
            <td><?= esc($t['description'] ?? '') ?></td>
            <td><?= esc($t['account_name'] ?? '-') ?></td>
            <td><?= esc($t['voucher_no'] ?? '') ?></td>
            <td style="color:#27ae60; font-weight:600;"><?= number_format((float) $t['amount_in'], 2) ?></td>
            <td style="color:#c0392b; font-weight:600;"><?= number_format((float) $t['amount_out'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?>
        <tr><td colspan="9">No transactions yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#paymentsTable', { columnDefs: [{ orderable: false, targets: [0] }] }); });</script>
