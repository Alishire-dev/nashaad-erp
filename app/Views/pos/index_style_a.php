<style>
    .posA-layout { display:flex; gap:0; background:#fff; border-radius:8px; overflow:hidden; }
    .posA-cart { flex:1; min-width:380px; padding:16px; border-right:1px solid #eee; }
    .posA-catalog { flex:1.5; padding:16px; position:relative; }
    .posA-row { display:flex; gap:10px; margin-bottom:10px; }
    .posA-row > * { flex:1; }

    .posA-cart-table th, .posA-cart-table td { padding:6px; font-size:12px; }
    .posA-cart-table thead th { background:#c9962e; color:#fff; }
    .posA-cart-table thead th:last-child { background:#c0392b; text-align:center; }

    .posA-totals { background:#f4f5f7; border-radius:6px; padding:12px; margin-top:10px; }
    .posA-totals .row { display:flex; justify-content:space-between; font-size:13px; padding:3px 0; }
    .posA-totals .row.grand { font-size:18px; font-weight:700; color:#e88a2e; }
    .disc-edit-btn { background:#e88a2e; color:#fff; border:none; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer; }

    .posA-cat-strip { display:flex; gap:8px; overflow-x:auto; padding-bottom:8px; align-items:center; }
    .posA-cat-tile {
        background:#eee; border-radius:6px; padding:10px 18px; font-weight:700; font-size:13px;
        white-space:nowrap; cursor:pointer; flex-shrink:0;
    }
    .posA-cat-tile.active { background:#e88a2e; color:#fff; }
    .posA-cat-underline { height:3px; background:#e74c3c; margin-bottom:12px; border-radius:2px; }

    .posA-product-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; max-height:520px; overflow-y:auto; }
    .posA-tile { border-radius:8px; overflow:hidden; cursor:pointer; transition:transform .1s ease; }
    .posA-tile:hover { transform:translateY(-2px); }
    .posA-tile .tile-head { background:#eee; color:#333; font-size:11px; padding:4px 8px; text-align:center; }
    .posA-tile .tile-body {
        height:70px; display:flex; align-items:center; justify-content:center;
    }
    .posA-tile.teal .tile-body { background:#1c6e7e; }
    .posA-tile.purple .tile-body { background:#c3b4e8; }
    .posA-tile .tile-name {
        background:rgba(0,0,0,.55); color:#fff; font-weight:700; text-transform:uppercase;
        font-size:11px; text-align:center; padding:6px 4px;
    }
    .broken-img-icon { width:32px; height:32px; opacity:.6; }

    .posA-icon-rail {
        position:absolute; right:0; top:16px; width:44px; display:flex; flex-direction:column;
        gap:8px; align-items:center;
    }
    .posA-icon-rail button {
        width:34px; height:34px; border-radius:50%; border:none; background:#3a8fd6; color:#fff;
        cursor:pointer; font-size:14px;
    }
    .posA-icon-rail button.thumb { background:#3a8fd6; }
    .posA-icon-rail button.refresh { background:#27ae60; }
</style>

<div style="text-align:right; margin-bottom:8px; font-size:13px;">
    <a href="<?= site_url('pos/toggle-theme') ?>" style="color:#3a8fd6;">(switch to list/pill theme)</a>
</div>
<div class="posA-layout">
    <div class="posA-cart">
        <div class="posA-row">
            <select disabled><option>Main Branch</option></select>
            <div style="display:flex; gap:4px;">
                <select id="customerId" data-searchable style="flex:1;">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $walkIn['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn" style="background:#b8956a;" onclick="openCustomerModal()">+</button>
            </div>
        </div>
        <div class="posA-row">
            <input type="date" id="saleDate" value="<?= date('Y-m-d') ?>">
            <input type="text" id="itemSearch" placeholder="🔍 Item name/Barcode/Itemcode(F10)" onkeyup="filterItems()">
        </div>

        <table class="posA-cart-table" style="width:100%;">
            <thead>
                <tr><th>ITEM NAME</th><th>QTY</th><th>PRICE</th><th>TYPE</th><th>SUBTOTAL</th><th>✕</th></tr>
            </thead>
            <tbody id="cartBody"></tbody>
        </table>

        <div class="posA-totals">
            <div class="row"><span>Total Item Qty</span><span id="totalQty">0</span></div>
            <div class="row"><span>Total Amount</span><span id="totalAmount">0.00</span></div>
            <div class="row">
                <span>Total Discount <button type="button" class="disc-edit-btn" onclick="editDiscount()">✎</button></span>
                <span id="totalDiscount">0.00</span>
            </div>
            <div class="row grand"><span>Grand Total</span><span id="totalGrand">0.00</span></div>
        </div>

        <div class="posA-row" style="margin-top:10px;">
            <div>
                <label style="font-size:12px; font-weight:600;">📅 DUE DATE</label>
                <input type="date" id="dueDate" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600;">InvNo</label>
                <select disabled><option>~~Invoice~~</option></select>
            </div>
        </div>

        <div style="display:flex; gap:8px; margin-top:14px;">
            <button class="btn green" style="flex:1;" onclick="openPaymentModal()">💰 PAYMENT(F11)</button>
            <button class="btn" style="background:#e08b1e; flex:1;" onclick="holdCart()">✋ HOLD(F4)</button>
            <button class="btn" style="background:#3a8fd6; flex:1;" onclick="openHeldModal()">👤 CUST(F7)</button>
        </div>
    </div>

    <div class="posA-catalog">
        <div class="posA-cat-strip" id="catStrip">
            <div class="posA-cat-tile active" data-cat="all" onclick="filterByCategory('all', this)">All</div>
            <?php foreach ($categories as $cat): ?>
                <div class="posA-cat-tile" data-cat="<?= $cat['id'] ?>" onclick="filterByCategory('<?= $cat['id'] ?>', this)"><?= esc($cat['name']) ?></div>
            <?php endforeach; ?>
        </div>
        <div class="posA-cat-underline"></div>

        <div class="posA-product-grid" id="itemGrid">
            <?php foreach ($items as $i => $item): ?>
                <div class="posA-tile <?= $i % 2 === 0 ? 'teal' : 'purple' ?>"
                     data-cat="<?= $item['category_id'] ?? '' ?>"
                     data-name="<?= esc(strtolower($item['name'])) ?>"
                     data-sku="<?= esc(strtolower($item['sku'] ?? '')) ?>"
                     onclick='addToCart(<?= $item["id"] ?>, <?= json_encode($item["name"]) ?>, <?= (float) $item["sales_price"] ?>, <?= json_encode($item["category_name"] ?? "") ?>)'>
                    <div class="tile-head">Qty: <?= number_format((float) $item['current_stock'], 0) ?> Price: <?= number_format((float) $item['sales_price'], 2) ?></div>
                    <div class="tile-body">
                        <svg class="broken-img-icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5">
                            <path d="M3 3l18 18M9 9a3 3 0 104.24 4.24M21 7v10a2 2 0 01-2 2H7m-4-4V6a2 2 0 012-2h10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="tile-name"><?= esc($item['name']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="posA-icon-rail">
            <button onclick="document.getElementById('catStrip').scrollBy({left:-150, behavior:'smooth'})">◀</button>
            <button onclick="document.getElementById('catStrip').scrollBy({left:150, behavior:'smooth'})">▶</button>
            <button class="thumb">👍</button>
            <button class="thumb">👎</button>
            <button class="refresh" onclick="window.location.reload()">⟳</button>
        </div>
    </div>
</div>

<!-- Modals reused from Style B's markup/behavior -->
<style>
    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:0; border-radius:8px; width:420px; max-width:92vw; overflow:hidden; }
    .modal-header { background:linear-gradient(135deg,#e88a2e,#d96f0f); color:#fff; padding:14px 20px; display:flex; justify-content:space-between; }
    .modal-body { padding:20px; max-height:70vh; overflow-y:auto; }
</style>

<div class="modal-backdrop" id="paymentModal">
    <div class="modal-box">
        <div class="modal-header"><strong>💰 Payment</strong><span style="cursor:pointer;" onclick="closePaymentModal()">&times;</span></div>
        <div class="modal-body">
            <form method="post" action="<?= site_url('pos/checkout') ?>" id="checkoutForm">
                <?= csrf_field() ?>
                <input type="hidden" name="customer_id" id="checkoutCustomerId">
                <input type="hidden" name="discount_pct" id="checkoutDiscountPct">
                <input type="hidden" name="lines_json" id="checkoutLinesJson">
                <div class="form-group"><label>Grand Total</label><input type="text" id="paymentGrandTotal" disabled></div>
                <div class="form-group">
                    <label>Pay Mode</label>
                    <select name="pay_mode"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option><option value="card">Card</option></select>
                </div>
                <div class="form-group"><label>Amount Paid*</label><input type="number" step="0.01" name="amount_paid" id="amountPaid" required></div>
                <button class="btn green" type="submit">Complete Sale</button>
                <button class="btn" type="button" onclick="closePaymentModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="heldModal">
    <div class="modal-box" style="width:500px;">
        <div class="modal-header"><strong>Held Sales</strong><span style="cursor:pointer;" onclick="closeHeldModal()">&times;</span></div>
        <div class="modal-body" id="heldListBody">Loading...</div>
    </div>
</div>

<div class="modal-backdrop" id="customerModal">
    <div class="modal-box">
        <div class="modal-header"><strong>+ New Customer</strong><span style="cursor:pointer;" onclick="closeCustomerModal()">&times;</span></div>
        <div class="modal-body">
            <div class="form-group"><label>Name*</label><input type="text" id="newCustomerName"></div>
            <div class="form-group"><label>Phone</label><input type="text" id="newCustomerPhone"></div>
            <button class="btn green" type="button" onclick="submitQuickCustomer()">Save</button>
            <button class="btn" type="button" onclick="closeCustomerModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(itemId, name, price, categoryName) {
    const existing = cart.find(c => c.item_id === itemId);
    if (existing) existing.quantity += 1;
    else cart.push({ item_id: itemId, name: name, quantity: 1, unit_price: price, type: categoryName });
    renderCart();
}
function removeFromCart(itemId) { cart = cart.filter(c => c.item_id !== itemId); renderCart(); }

let discountAmount = 0;
function editDiscount() {
    const val = prompt('Total Discount amount:', discountAmount);
    if (val !== null) { discountAmount = parseFloat(val) || 0; renderCart(); }
}

function renderCart() {
    const body = document.getElementById('cartBody');
    body.innerHTML = '';
    let totalQty = 0, subtotal = 0;

    cart.forEach((line, idx) => {
        const lineTotal = line.quantity * line.unit_price;
        subtotal += lineTotal;
        totalQty += line.quantity;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${line.name}</td>
            <td><input type="number" step="0.01" value="${line.quantity}" style="width:55px;" onchange="updateQty(${idx}, this.value)"></td>
            <td><input type="number" step="0.01" value="${line.unit_price.toFixed(2)}" style="width:65px;" onchange="updatePrice(${idx}, this.value)"></td>
            <td>${line.type || '-'}</td>
            <td>${lineTotal.toFixed(2)}</td>
            <td style="text-align:center;"><button type="button" onclick="removeFromCart(${line.item_id})" style="background:#c0392b; color:#fff; border:none; border-radius:3px; cursor:pointer;">✕</button></td>
        `;
        body.appendChild(row);
    });

    const grandTotal = subtotal - discountAmount;
    document.getElementById('totalQty').innerText = totalQty;
    document.getElementById('totalAmount').innerText = subtotal.toFixed(2);
    document.getElementById('totalDiscount').innerText = discountAmount.toFixed(2);
    document.getElementById('totalGrand').innerText = grandTotal.toFixed(2);
}
function updateQty(idx, val) { cart[idx].quantity = parseFloat(val) || 0; renderCart(); }
function updatePrice(idx, val) { cart[idx].unit_price = parseFloat(val) || 0; renderCart(); }

function filterByCategory(catId, el) {
    document.querySelectorAll('.posA-cat-tile').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.posA-tile').forEach(tile => {
        tile.style.display = (catId === 'all' || tile.dataset.cat === catId) ? '' : 'none';
    });
}
function filterItems() {
    const q = document.getElementById('itemSearch').value.toLowerCase();
    document.querySelectorAll('.posA-tile').forEach(tile => {
        tile.style.display = (tile.dataset.name.includes(q) || tile.dataset.sku.includes(q)) ? '' : 'none';
    });
}

function openPaymentModal() {
    if (cart.length === 0) { alert('Cart is empty.'); return; }
    document.getElementById('paymentGrandTotal').value = document.getElementById('totalGrand').innerText;
    document.getElementById('paymentModal').classList.add('show');
}
function closePaymentModal() { document.getElementById('paymentModal').classList.remove('show'); }

document.getElementById('checkoutForm').addEventListener('submit', function () {
    document.getElementById('checkoutCustomerId').value = document.getElementById('customerId').value;
    document.getElementById('checkoutDiscountPct').value = 0; // fixed-amount discount tracked separately above
    document.getElementById('checkoutLinesJson').value = JSON.stringify(cart.map(c => ({
        item_id: c.item_id, quantity: c.quantity, unit_price: c.unit_price, discount_pct: 0,
    })));
});

function holdCart() {
    if (cart.length === 0) { alert('Cart is empty.'); return; }
    const form = document.createElement('form');
    form.method = 'post'; form.action = '<?= site_url('pos/hold') ?>';
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '<?= csrf_token() ?>'; csrf.value = '<?= csrf_hash() ?>';
    form.appendChild(csrf);
    const custInput = document.createElement('input');
    custInput.type = 'hidden'; custInput.name = 'customer_id'; custInput.value = document.getElementById('customerId').value;
    form.appendChild(custInput);
    const linesInput = document.createElement('input');
    linesInput.type = 'hidden'; linesInput.name = 'lines_json';
    linesInput.value = JSON.stringify(cart.map(c => ({ item_id: c.item_id, quantity: c.quantity, unit_price: c.unit_price })));
    form.appendChild(linesInput);
    document.body.appendChild(form);
    form.submit();
}

async function openHeldModal() {
    document.getElementById('heldModal').classList.add('show');
    const res = await fetch('<?= site_url('pos/held-list') ?>');
    const held = await res.json();
    const body = document.getElementById('heldListBody');
    if (held.length === 0) { body.innerHTML = '<p>No held sales.</p>'; return; }
    body.innerHTML = held.map(h => `
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;">
            <span>${h.customer_name || 'WALK-IN'} — ${h.created_at}</span>
            <button class="btn" onclick="recallSale(${h.id})">Recall</button>
        </div>
    `).join('');
}
function closeHeldModal() { document.getElementById('heldModal').classList.remove('show'); }

async function recallSale(saleId) {
    const res = await fetch('<?= site_url('pos/recall') ?>/' + saleId);
    const sale = await res.json();
    if (sale.error) { alert('Could not recall that sale.'); return; }
    cart = sale.lines.map(l => ({ item_id: l.item_id, name: l.item_name, quantity: parseFloat(l.quantity), unit_price: parseFloat(l.unit_price) }));
    document.getElementById('customerId').value = sale.customer_id;
    renderCart();
    closeHeldModal();
}

function openCustomerModal() { document.getElementById('customerModal').classList.add('show'); }
function closeCustomerModal() { document.getElementById('customerModal').classList.remove('show'); }

async function submitQuickCustomer() {
    const name = document.getElementById('newCustomerName').value.trim();
    if (!name) { alert('Enter a name.'); return; }
    const phone = document.getElementById('newCustomerPhone').value.trim();
    const res = await fetch('<?= site_url('customers/quick-add') ?>', {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ name, phone }),
    });
    const data = await res.json();
    const select = document.getElementById('customerId');
    const opt = document.createElement('option');
    opt.value = data.id; opt.text = data.name; opt.selected = true;
    select.appendChild(opt);
    // Keep the searchable-select's visible display in sync — it's a
    // separate input layered over the real (now-hidden) select, so
    // appending an option to the select alone doesn't update what's shown.
    const display = select.parentElement.querySelector('.searchable-select-display');
    if (display) display.value = data.name;
    closeCustomerModal();
}

renderCart();
</script>
