<h2>New Purchase</h2>

<?php if (! empty($error)): ?>
    <div style="color:#c0392b; margin-bottom:14px;"><?= esc($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('purchase/add') ?>" id="purchaseForm">
    <?= csrf_field() ?>
    <input type="hidden" name="lines_json" id="linesJson">

    <div class="form-group" style="max-width:400px;">
        <label>Supplier*</label>
        <select name="supplier_id" required>
            <option value="">-Select-</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <small><a href="<?= site_url('suppliers/add') ?>" target="_blank">+ add new supplier</a></small>
    </div>

    <div style="display:flex; gap:16px;">
        <div class="form-group" style="max-width:200px;">
            <label>Purchase Date*</label>
            <input type="date" name="purchase_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group" style="max-width:200px;">
            <label>Due Date</label>
            <input type="date" name="due_date">
        </div>
        <div class="form-group" style="max-width:250px;">
            <label>Reference No.</label>
            <input type="text" name="reference_no">
        </div>
    </div>

    <h3>Items</h3>
    <table id="cartTable">
        <tr>
            <th>Item</th><th>Qty</th><th>Cost Price</th><th>Tax %</th><th>Discount %</th><th>Line Total</th><th></th>
        </tr>
    </table>

    <div style="margin:10px 0;">
        <select id="itemPicker" style="width:300px; display:inline-block;">
            <option value="">-- Select item to add --</option>
            <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>"
                        data-name="<?= esc($item['name']) ?>"
                        data-cost="<?= $item['purchase_price'] ?>">
                    <?= esc($item['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn" onclick="addRow()">+ Add Item</button>
    </div>

    <h3 style="text-align:right;">Grand Total: <span id="grandTotal">0.00</span></h3>

    <div class="form-group"><label>Note</label><textarea name="note" rows="2"></textarea></div>

    <button class="btn green" type="submit" onclick="return prepareSubmit()">Save Purchase</button>
</form>

<script>
let rowCount = 0;

function addRow() {
    const picker = document.getElementById('itemPicker');
    const opt = picker.options[picker.selectedIndex];
    if (!opt.value) { alert('Select an item first.'); return; }

    rowCount++;
    const table = document.getElementById('cartTable');
    const row = table.insertRow(-1);
    row.dataset.itemId = opt.value;

    row.innerHTML = `
        <td>${opt.dataset.name}</td>
        <td><input type="number" step="0.001" class="qty" value="1" style="width:80px;" onchange="recalc()"></td>
        <td><input type="number" step="0.01" class="cost" value="${opt.dataset.cost}" style="width:90px;" onchange="recalc()"></td>
        <td><input type="number" step="0.01" class="tax" value="0" style="width:70px;" onchange="recalc()"></td>
        <td><input type="number" step="0.01" class="disc" value="0" style="width:70px;" onchange="recalc()"></td>
        <td class="lineTotal">0.00</td>
        <td><button type="button" class="btn" style="background:#c0392b;" onclick="this.closest('tr').remove(); recalc();">x</button></td>
    `;
    picker.selectedIndex = 0;
    recalc();
}

function recalc() {
    const table = document.getElementById('cartTable');
    let grand = 0;
    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        const qty  = parseFloat(row.querySelector('.qty').value) || 0;
        const cost = parseFloat(row.querySelector('.cost').value) || 0;
        const tax  = parseFloat(row.querySelector('.tax').value) || 0;
        const disc = parseFloat(row.querySelector('.disc').value) || 0;

        const subtotal = qty * cost;
        const discAmt  = subtotal * (disc / 100);
        const taxable  = subtotal - discAmt;
        const taxAmt   = taxable * (tax / 100);
        const lineTotal = taxable + taxAmt;

        row.querySelector('.lineTotal').innerText = lineTotal.toFixed(2);
        grand += lineTotal;
    }
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
}

function prepareSubmit() {
    const table = document.getElementById('cartTable');
    if (table.rows.length < 2) {
        alert('Add at least one item.');
        return false;
    }
    const lines = [];
    for (let i = 1; i < table.rows.length; i++) {
        const row = table.rows[i];
        lines.push({
            item_id: row.dataset.itemId,
            quantity: row.querySelector('.qty').value,
            cost_price: row.querySelector('.cost').value,
            tax_percent: row.querySelector('.tax').value,
            discount_pct: row.querySelector('.disc').value,
        });
    }
    document.getElementById('linesJson').value = JSON.stringify(lines);
    return true;
}
</script>
