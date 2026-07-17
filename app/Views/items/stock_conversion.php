<h2>Stock Conversion Report</h2>
<a class="btn green" href="<?= site_url('stock-conversion/add') ?>">+ Convert Stock</a>
<br><br>

<table id="conversionTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>Parent Product</th><th>Qty Converted</th><th>Child Product</th>
            <th>Qty Produced</th><th>Description</th><th>Date/Time</th><th>Created By</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($conversions as $c): ?>
        <tr>
            <td><?= esc($c['parent_name']) ?></td>
            <td><?= number_format((float) $c['qty_converted'], 3) ?></td>
            <td><?= esc($c['child_name']) ?></td>
            <td><?= number_format((float) $c['qty_produced'], 3) ?></td>
            <td><?= esc($c['description'] ?? '') ?></td>
            <td><?= esc($c['created_at']) ?></td>
            <td><?= esc($c['user_name'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($conversions)): ?>
        <tr><td colspan="7">No data available in table</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= view('layout/datatable_assets') ?>
<script>$(document).ready(function () { initDataTable('#conversionTable'); });</script>
