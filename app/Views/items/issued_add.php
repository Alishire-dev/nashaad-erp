<h2>Add Issued Products</h2>

<form method="post" action="<?= site_url('issued-products/add') ?>" id="issuedForm">
    <?= csrf_field() ?>
    <input type="hidden" name="lines_json" id="linesJson">

    <table style="width:100%; margin-bottom:0;">
        <thead>
            <tr><th>Item Name</th><th>Unit Price</th><th>Qty</th><th>Sub Total</th><th>Date</th><th></th></tr>
        </thead>
    </table>

    <div class="form-group" style="max-width:300px;">
        <label>Branch/Station</label>
        <select disabled><option>Main Branch</option></select>
    </div>

    <div id="rowsContainer"></div>

    <button type="button" class="btn" style="background:#e88a2e; margin-bottom:16px;" onclick="addRow()">+ Add Row</button>

    <div style="text-align:right; font-size:18px; font-weight:700; margin-bottom:16px;">
        Grand Total: <span id="grandTotal">0.00</span>
    </div>

    <button class="btn green" type="submit">💾 Submit</button>
    <a class="btn" href="<?= site_url('issued-products') ?>">Cancel</a>
</form>

<script>
const items = <?= json_encode(array_map(static fn ($i) => [
    'id' => $i['id'], 'name' => $i['name'], 'code' => $i['item_code'] ?? '',
    'stock' => (float) $i['current_stock'], 'price' => (float) $i['sales_price'],
], $items)) ?>;

let rowCount = 0;

function addRow() {
    rowCount++;
    const container = document.getElementById('rowsContainer');
    const row = document.createElement('div');
    row.className = 'issue-row';
    row.dataset.rowId = rowCount;
    row.style.cssText = 'display:flex; gap:12px; align-items:flex-start; background:#fff; padding:14px; border-radius:8px; margin-bottom:10px;';

    const itemOptions = items.map(i => `<option value="${i.id}" data-price="${i.price}" data-stock="${i.stock}">${i.name}${i.code ? ' - ' + i.code : ''} [Stock: ${i.stock}]</option>`).join('');

    row.innerHTML = `
        <div style="flex:2;">
            <select class="row-item" onchange="updateRow(${rowCount})" style="width:100%;">
                <option value="">~~Select Item~~</option>
                ${itemOptions}
            </select>
            <textarea class="row-note" placeholder="Note" rows="2" style="width:100%; margin-top:6px;"></textarea>
        </div>
        <input type="number" step="0.01" class="row-price" placeholder="Unit Price" style="flex:1;" oninput="updateRow(${rowCount})">
        <input type="number" step="0.001" class="row-qty" value="1" style="flex:1;" oninput="updateRow(${rowCount})">
        <input type="text" class="row-subtotal" disabled style="flex:1;" value="0.00">
        <input type="date" class="row-date" value="<?= date('Y-m-d') ?>" style="flex:1;">
        <button type="button" class="btn" style="background:#c0392b;" onclick="removeRow(${rowCount})">−</button>
    `;
    container.appendChild(row);
}

function updateRow(rowId) {
    const row = document.querySelector(`[data-row-id="${rowId}"]`);
    const select = row.querySelector('.row-item');
    const priceInput = row.querySelector('.row-price');
    const selectedOpt = select.options[select.selectedIndex];

    if (selectedOpt && selectedOpt.dataset.price && !priceInput.dataset.touched) {
        priceInput.value = selectedOpt.dataset.price;
    }
    priceInput.addEventListener('input', () => priceInput.dataset.touched = '1');

    const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    row.querySelector('.row-subtotal').value = (qty * price).toFixed(2);
    recalcGrandTotal();
}

function removeRow(rowId) {
    document.querySelector(`[data-row-id="${rowId}"]`).remove();
    recalcGrandTotal();
}

function recalcGrandTotal() {
    let total = 0;
    document.querySelectorAll('.row-subtotal').forEach(el => total += parseFloat(el.value) || 0);
    document.getElementById('grandTotal').innerText = total.toFixed(2);
}

document.getElementById('issuedForm').addEventListener('submit', function (e) {
    const lines = [];
    document.querySelectorAll('.issue-row').forEach(row => {
        const itemId = row.querySelector('.row-item').value;
        if (!itemId) return;
        lines.push({
            item_id: itemId,
            quantity: row.querySelector('.row-qty').value,
            unit_price: row.querySelector('.row-price').value || 0,
            note: row.querySelector('.row-note').value,
            date: row.querySelector('.row-date').value,
        });
    });
    if (lines.length === 0) {
        e.preventDefault();
        alert('Add at least one item.');
        return;
    }
    document.getElementById('linesJson').value = JSON.stringify(lines);
});

addRow();
</script>
