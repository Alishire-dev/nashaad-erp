<h2>New Purchase Return</h2>

<?php if (! empty($error)): ?>
    <div style="color:#c0392b; margin-bottom:14px;"><?= esc($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('purchase/return/add') ?>" id="returnForm">
    <?= csrf_field() ?>
    <input type="hidden" name="lines_json" id="linesJson">

    <div class="form-group" style="max-width:400px;">
        <label>Original Purchase*</label>
        <select name="purchase_id" id="purchaseSelect" required onchange="loadLines(this.value)">
            <option value="">-Select-</option>
            <?php foreach ($purchases as $p): ?>
                <option value="<?= $p['id'] ?>">
                    #<?= $p['id'] ?> - <?= esc($p['supplier_name'] ?? '-') ?> (<?= esc($p['purchase_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group"><label>Reason</label><input type="text" name="reason" placeholder="e.g. damaged goods, wrong item"></div>

    <h3>Items to Return</h3>
    <table id="returnTable">
        <tr><th>Item</th><th>Purchased Qty</th><th>Return Qty</th><th>Cost Price</th><th>Line Total</th></tr>
    </table>

    <h3 style="text-align:right;">Grand Total: <span id="grandTotal">0.00</span></h3>

    <button class="btn green" type="submit" onclick="return prepareSubmit()">Save Return</button>
</form>

<script>
async function loadLines(purchaseId) {
    const table = document.getElementById('returnTable');
    table.innerHTML = '<tr><th>Item</th><th>Purchased Qty</th><th>Return Qty</th><th>Cost Price</th><th>Line Total</th></tr>';
    if (!purchaseId) return;

    const res = await fetch('<?= site_url('purchase/lines-json') ?>/' + purchaseId);
    const lines = await res.json();

    lines.forEach(line => {
        const row = table.insertRow(-1);
        row.dataset.itemId = line.item_id;
        row.dataset.cost = line.cost_price;
        row.innerHTML = `
            <td>${line.item_name}</td>
            <td>${parseFloat(line.quantity).toFixed(3)}</td>
            <td><input type="number" step="0.001" class="retQty" value="0" max="${line.quantity}" style="width:90px;" onchange="recalc()"></td>
            <td>${parseFloat(line.cost_price).toFixed(2)}</td>
            <td class="lineTotal">0.00</td>
        `;
    });
    recalc();
}

function recalc() {
    const table = document.getElementById('returnTable');
    let grand = 0;
    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        const qty = parseFloat(row.querySelector('.retQty').value) || 0;
        const cost = parseFloat(row.dataset.cost) || 0;
        const total = qty * cost;
        row.querySelector('.lineTotal').innerText = total.toFixed(2);
        grand += total;
    }
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
}

function prepareSubmit() {
    const table = document.getElementById('returnTable');
    const lines = [];
    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        const qty = parseFloat(row.querySelector('.retQty').value) || 0;
        if (qty > 0) {
            lines.push({ item_id: row.dataset.itemId, quantity: qty, cost_price: row.dataset.cost });
        }
    }
    if (lines.length === 0) {
        alert('Enter a return quantity for at least one item.');
        return false;
    }
    document.getElementById('linesJson').value = JSON.stringify(lines);
    return true;
}
</script>
