<h2>Price Change Log</h2>
<p style="color:#666; font-size:14px;">Every sales-price change made via Stock Manager's Update Price is recorded here automatically.</p>

<table id="priceLogTable" class="display" style="width:100%;">
    <thead>
        <tr><th>Item</th><th>Old Price</th><th>New Price</th><th>Change</th><th>Changed By</th><th>Date</th></tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <?php $diff = (float) $log['new_price'] - (float) $log['old_price']; ?>
        <tr>
            <td><?= esc($log['item_name']) ?></td>
            <td><?= number_format((float) $log['old_price'], 2) ?></td>
            <td><?= number_format((float) $log['new_price'], 2) ?></td>
            <td style="color:<?= $diff >= 0 ? '#27ae60' : '#c0392b' ?>; font-weight:600;">
                <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 2) ?>
            </td>
            <td><?= esc($log['user_name'] ?? '-') ?></td>
            <td><?= esc($log['changed_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#priceLogTable'); });</script>
