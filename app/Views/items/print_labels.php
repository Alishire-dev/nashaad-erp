<h2>Print Labels <small style="font-weight:normal; color:#666; font-size:14px;">Add/Update Sales</small></h2>

<div style="position:relative; max-width:600px; margin-bottom:20px;">
    <input type="text" id="itemSearch" placeholder="Item name/Barcode/Itemcode"
           style="padding-left:40px;" autocomplete="off" oninput="searchItems()">
    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%);">📶</span>
    <div id="searchResults" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff;
         border-radius:6px; box-shadow:0 4px 14px rgba(0,0,0,.15); z-index:20; max-height:280px; overflow-y:auto;"></div>
</div>

<table style="width:100%;">
    <thead><tr><th>Item Name</th><th>Quantity</th><th>Action</th></tr></thead>
    <tbody id="labelRows"></tbody>
</table>

<div style="display:flex; justify-content:space-between; align-items:center; margin:16px 0;">
    <div><strong>Total Labels</strong> <span id="totalLabels">0</span></div>
</div>

<div style="display:flex; gap:10px;">
    <button class="btn" style="background:#d6336c; flex:1;" onclick="previewLabels()">Preview</button>
    <button class="btn" style="background:#95a5a6; flex:1;" onclick="window.location='<?= site_url('items/list') ?>'">Close</button>
    <button class="btn" style="background:#3a8fd6; flex:1;" onclick="printLabels()">Print</button>
</div>

<script>
const allItems = <?= json_encode(array_map(static fn ($i) => [
    'id' => $i['id'], 'name' => $i['name'], 'code' => $i['item_code'] ?? '', 'sku' => $i['sku'] ?? '',
], $items)) ?>;

let cart = {}; // { itemId: { name, qty } }

function searchItems() {
    const q = document.getElementById('itemSearch').value.trim().toLowerCase();
    const box = document.getElementById('searchResults');
    if (q.length < 1) { box.style.display = 'none'; return; }

    const matches = allItems.filter(i =>
        i.name.toLowerCase().includes(q) || i.code.toLowerCase().includes(q) || i.sku.toLowerCase().includes(q)
    ).slice(0, 15);

    if (matches.length === 0) { box.innerHTML = '<div style="padding:10px 14px; color:#999;">No matches</div>'; box.style.display = 'block'; return; }

    box.innerHTML = matches.map(i => `
        <div style="padding:10px 14px; cursor:pointer; border-bottom:1px solid #f0f0f0;"
             onmouseover="this.style.background='#f4f5f7'" onmouseout="this.style.background='#fff'"
             onclick="addToLabels(${i.id}, ${JSON.stringify(i.name)})">
            ${i.name} ${i.code ? '<span style="color:#999;">(' + i.code + ')</span>' : ''}
        </div>
    `).join('');
    box.style.display = 'block';
}

function addToLabels(id, name) {
    if (!cart[id]) cart[id] = { name: name, qty: 1 };
    else cart[id].qty += 1;
    document.getElementById('itemSearch').value = '';
    document.getElementById('searchResults').style.display = 'none';
    renderRows();
}

function removeFromLabels(id) {
    delete cart[id];
    renderRows();
}

function updateQty(id, val) {
    cart[id].qty = parseInt(val) || 1;
    renderRows();
}

function renderRows() {
    const tbody = document.getElementById('labelRows');
    tbody.innerHTML = '';
    let total = 0;
    Object.keys(cart).forEach(id => {
        total += cart[id].qty;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${cart[id].name}</td>
            <td><input type="number" min="1" value="${cart[id].qty}" style="width:80px;" onchange="updateQty(${id}, this.value)"></td>
            <td><button type="button" class="btn" style="background:#c0392b;" onclick="removeFromLabels(${id})">Remove</button></td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('totalLabels').innerText = total;
}

function buildSheetUrl() {
    const ids = [];
    Object.keys(cart).forEach(id => { for (let i = 0; i < cart[id].qty; i++) ids.push(id); });
    return '<?= site_url('items/print-labels/sheet') ?>?ids=' + ids.join(',');
}

function previewLabels() {
    if (Object.keys(cart).length === 0) { alert('Add at least one item.'); return; }
    window.open(buildSheetUrl(), '_blank');
}

function printLabels() {
    if (Object.keys(cart).length === 0) { alert('Add at least one item.'); return; }
    const win = window.open(buildSheetUrl(), '_blank');
    win.onload = () => win.print();
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('#itemSearch') && !e.target.closest('#searchResults')) {
        document.getElementById('searchResults').style.display = 'none';
    }
});
</script>
