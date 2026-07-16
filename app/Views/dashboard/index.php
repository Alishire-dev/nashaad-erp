<h2>Dashboard</h2>

<div class="card-row">
    <div class="card" style="background:#2980b9;">
        <div>Total Items</div>
        <h2><?= (int) $totalItems ?></h2>
    </div>
    <div class="card" style="background:#c0392b;">
        <div>Stock Alerts</div>
        <h2><?= count($lowStock) ?></h2>
    </div>
    <div class="card" style="background:#27ae60;">
        <div>Today's Sales</div>
        <h2><?= number_format($todaySummary['total_sales'], 2) ?></h2>
    </div>
    <div class="card" style="background:#8e44ad;">
        <div>Pending (Accounts Receivable)</div>
        <h2><?= count($pendingSales) ?></h2>
    </div>
</div>

<div style="display:flex; gap:20px; flex-wrap:wrap;">
    <div style="flex:2; min-width:400px; background:#fff; border-radius:8px; padding:16px;">
        <h3 style="margin-top:0;">Purchase | Sales — Last 7 Days</h3>
        <canvas id="salesChart" height="90"></canvas>
    </div>

    <div style="flex:1; min-width:280px; background:#fff; border-radius:8px; padding:16px;">
        <h3 style="margin-top:0;">Today's Sales Summary</h3>
        <table style="box-shadow:none;">
            <tr><td>Total Sales</td><td style="text-align:right;">$ <?= number_format($todaySummary['total_sales'], 2) ?></td></tr>
            <tr><td>Paid Sales</td><td style="text-align:right;">$ <?= number_format($todaySummary['paid_sales'], 2) ?></td></tr>
            <tr><td>Pending Sales</td><td style="text-align:right;">$ <?= number_format($todaySummary['pending_sales'], 2) ?></td></tr>
            <tr><td>Cancelled Sales</td><td style="text-align:right;">$ <?= number_format($todaySummary['cancelled_sales'], 2) ?></td></tr>
        </table>
    </div>
</div>

<div style="display:flex; gap:20px; flex-wrap:wrap; margin-top:20px;">
    <div style="flex:1; min-width:320px;">
        <h3>Top 5 Fast Moving Items</h3>
        <table>
            <tr><th>SN</th><th>Item Name</th><th>Qty Sold</th></tr>
            <?php foreach ($topMovers as $i => $m): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($m['item_name']) ?></td>
                <td><?= number_format((float) $m['total_qty'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topMovers)): ?>
            <tr><td colspan="3">No sales yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div style="flex:1; min-width:320px;">
        <h3>Pending Sales / Accounts Receivable</h3>
        <table>
            <tr><th>Invoice</th><th>Customer</th><th>Due</th></tr>
            <?php foreach (array_slice($pendingSales, 0, 10) as $p): ?>
            <tr>
                <td><?= esc($p['invoice_no']) ?></td>
                <td><?= esc($p['customer_name'] ?? 'WALK-IN') ?></td>
                <td><?= number_format((float) $p['grand_total'] - (float) $p['amount_paid'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($pendingSales)): ?>
            <tr><td colspan="3">No pending sales.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<h3 style="margin-top:20px;">Stock Alert</h3>
<table>
    <tr><th>Item</th><th>Current Stock</th><th>Alert Qty</th></tr>
    <?php foreach ($lowStock as $item): ?>
    <tr>
        <td><?= esc($item['name']) ?></td>
        <td><?= number_format((float) $item['current_stock'], 2) ?></td>
        <td><?= number_format((float) $item['alert_qty'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($lowStock)): ?>
    <tr><td colspan="3">No items running low.</td></tr>
    <?php endif; ?>
</table>

<!-- Verified real cdnjs listing before using this URL — see commit notes
     on the DataTables CDN incident for why that check matters now. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.min.js"></script>
<script>
const salesData = <?= json_encode(array_map(static fn ($d) => $d['total'], $salesDaily)) ?>;
const purchaseData = <?= json_encode(array_map(static fn ($d) => $d['total'], $purchaseDaily)) ?>;
const labels = <?= json_encode(array_map(static fn ($d) => date('M j', strtotime($d['date'])), $salesDaily)) ?>;

if (typeof Chart !== 'undefined') {
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Sales', data: salesData, backgroundColor: '#27ae60' },
                { label: 'Purchases', data: purchaseData, backgroundColor: '#e88a2e' },
            ],
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } },
    });
} else {
    console.error('Chart.js failed to load — chart omitted, rest of page unaffected.');
}
</script>
