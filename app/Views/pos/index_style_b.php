<style>
    .pos-layout { display:flex; gap:20px; }
    .pos-cart-panel { flex:1; min-width:380px; }
    .pos-item-panel { flex:1.4; }
    .cat-tabs { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
    .cat-tab {
        background:#fff; border:1px solid #dde1e8; padding:8px 14px; border-radius:6px;
        cursor:pointer; font-size:13px; font-weight:500;
    }
    .cat-tab.active { background:#e88a2e; color:#fff; border-color:#e88a2e; }
    .item-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:10px; }
    .item-card {
        background:#fff; border-radius:8px; padding:12px 8px; text-align:center; cursor:pointer;
        box-shadow:0 1px 4px rgba(0,0,0,.08); transition: transform .1s ease;
    }
    .item-card:hover { transform:translateY(-2px); box-shadow:0 4px 10px rgba(0,0,0,.12); }
    .item-card .name { font-size:13px; font-weight:600; color:#1a2036; margin-bottom:4px; }
    .item-card .price { font-size:12px; color:#e88a2e; font-weight:600; }
    .cart-table th, .cart-table td { padding:8px; font-size:13px; }
    .cart-qty, .cart-price { width:70px; padding:4px; border:1px solid #dde1e8; border-radius:4px; }
    .pos-totals { background:#fff; border-radius:8px; padding:16px; margin-top:14px; }
    .pos-totals .row { display:flex; justify-content:space-between; padding:4px 0; font-size:14px; }
    .pos-totals .grand { font-size:20px; font-weight:700; color:#e88a2e; border-top:2px solid #eee; margin-top:8px; padding-top:8px; }
    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:100; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff; padding:0; border-radius:8px; width:420px; max-width:92vw; overflow:hidden; }
    .modal-header { background:linear-gradient(135deg,#e88a2e,#d96f0f); color:#fff; padding:14px 20px; display:flex; justify-content:space-between; }
    .modal-body { padding:20px; max-height:70vh; overflow-y:auto; }
</style>

<h2>POS <a href="<?= site_url('pos/toggle-theme') ?>" style="font-size:13px; font-weight:normal; color:#3a8fd6;">(switch to image-tile theme)</a></h2>

<div class="pos-layout">
    <div class="pos-cart-panel">
        <div class="form-group">
            <label>Customer</label>
            <div style="display:flex; gap:6px;">
                <select id="customerId" data-searchable style="flex:1;">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $walkIn['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn" onclick="openCustomerModal()">+</button>
            </div>
        </div>

        <table class="cart-table" style="width:100%; background:#fff; border-radius:6px;">
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr>
            </thead>
            <tbody id="cartBody"></tbody>
        </table>

        <div class="pos-totals">
            <div class="row"><span>Subtotal</span><span id="totalSubtotal">0.00</span></div>
            <div class="row">
                <span>Discount %</span>
                <input type="number" id="discountPct" value="0" style="width:70px;" onchange="renderCart()">
            </div>
            <div class="row grand"><span>Grand Total</span><span id="totalGrand">0.00</span></div>
        </div>

        <div style="display:flex; gap:8px; margin-top:14px;">
            <button class="btn green" style="flex:1;" onclick="openPaymentModal()">💰 Payment (F11)</button>
            <button class="btn" style="background:#e08b1e; flex:1;" onclick="holdCart()">✋ Hold (F4)</button>
            <button class="btn" style="background:#3a8fd6;" onclick="openHeldModal()">Hold List (<?= $heldCount ?>)</button>
        </div>
    </div>

    <div class="pos-item-panel">
        <input type="text" id="itemSearch" placeholder="Item name/Barcode (F10)" style="margin-bottom:12px;" onkeyup="filterItems()">

        <div class="cat-tabs">
            <div class="cat-tab active" data-cat="all" onclick="filterByCategory('all', this)">All</div>
            <?php foreach ($categories as $cat): ?>
                <div class="cat-tab" data-cat="<?= $cat['id'] ?>" onclick="filterByCategory('<?= $cat['id'] ?>', this)"><?= esc($cat['name']) ?></div>
            <?php endforeach; ?>
        </div>

        <div class="item-grid" id="itemGrid">
            <?php foreach ($items as $item): ?>
                <div class="item-card"
                     data-cat="<?= $item['category_id'] ?? '' ?>"
                     data-name="<?= esc(strtolower($item['name'])) ?>"
                     data-sku="<?= esc(strtolower($item['sku'] ?? '')) ?>"
                     onclick='addToCart(<?= $item["id"] ?>, <?= json_encode($item["name"]) ?>, <?= (float) $item["sales_price"] ?>)'>
                    <div class="name"><?= esc($item['name']) ?></div>
                    <div class="price"><?= number_format((float) $item['sales_price'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Payment modal -->
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
                    <select name="pay_mode">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div class="form-group"><label>Amount Paid*</label><input type="number" step="0.01" name="amount_paid" id="amountPaid" required></div>
                <button class="btn green" type="submit">Complete Sale</button>
                <button class="btn" type="button" onclick="closePaymentModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- Hold list modal -->
<div class="modal-backdrop" id="heldModal">
    <div class="modal-box" style="width:500px;">
        <div class="modal-header"><strong>Held Sales</strong><span style="cursor:pointer;" onclick="closeHeldModal()">&times;</span></div>
        <div class="modal-body" id="heldListBody">Loading...</div>
    </div>
</div>

<!-- Quick add customer modal -->
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
let cart = []; // [{item_id, name, quantity, unit_price}]

function addToCart(itemId, name, price) {
    const existing = cart.find(c => c.item_id === itemId);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ item_id: itemId, name: name, quantity: 1, unit_price: price });
    }
    renderCart();
}

function removeFromCart(itemId) {
    cart = cart.filter(c => c.item_id !== itemId);
    renderCart();
}

function renderCart() {
    const body = document.getElementById('cartBody');
    body.innerHTML = '';
    let subtotal = 0;

    cart.forEach((line, idx) => {
        const lineTotal = line.quantity * line.unit_price;
        subtotal += lineTotal;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${line.name}</td>
            <td><input type="number" step="0.01" class="cart-qty" value="${line.quantity}" onchange="updateQty(${idx}, this.value)"></td>
            <td><input type="number" step="0.01" class="cart-price" value="${line.unit_price.toFixed(2)}" onchange="updatePrice(${idx}, this.value)"></td>
            <td>${lineTotal.toFixed(2)}</td>
            <td><button type="button" class="btn" style="background:#c0392b;" onclick="removeFromCart(${line.item_id})">x</button></td>
        `;
        body.appendChild(row);
    });

    const discountPct = parseFloat(document.getElementById('discountPct').value) || 0;
    const grandTotal = subtotal - (subtotal * discountPct / 100);

    document.getElementById('totalSubtotal').innerText = subtotal.toFixed(2);
    document.getElementById('totalGrand').innerText = grandTotal.toFixed(2);
}

function updateQty(idx, val) { cart[idx].quantity = parseFloat(val) || 0; renderCart(); }
function updatePrice(idx, val) { cart[idx].unit_price = parseFloat(val) || 0; renderCart(); }

function filterByCategory(catId, el) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.item-card').forEach(card => {
        card.style.display = (catId === 'all' || card.dataset.cat === catId) ? '' : 'none';
    });
}

function filterItems() {
    const q = document.getElementById('itemSearch').value.toLowerCase();
    document.querySelectorAll('.item-card').forEach(card => {
        const matches = card.dataset.name.includes(q) || card.dataset.sku.includes(q);
        card.style.display = matches ? '' : 'none';
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
    document.getElementById('checkoutDiscountPct').value = document.getElementById('discountPct').value;
    document.getElementById('checkoutLinesJson').value = JSON.stringify(cart.map(c => ({
        item_id: c.item_id, quantity: c.quantity, unit_price: c.unit_price, discount_pct: 0,
    })));
});

function holdCart() {
    if (cart.length === 0) { alert('Cart is empty.'); return; }
    const form = document.createElement('form');
    form.method = 'post';
    form.action = '<?= site_url('pos/hold') ?>';

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
    if (held.length === 0) {
        body.innerHTML = '<p>No held sales.</p>';
        return;
    }
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

    cart = sale.lines.map(l => ({
        item_id: l.item_id, name: l.item_name, quantity: parseFloat(l.quantity), unit_price: parseFloat(l.unit_price),
    }));
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
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ name, phone }),
    });
    const data = await res.json();

    const select = document.getElementById('customerId');
    const opt = document.createElement('option');
    opt.value = data.id; opt.text = data.name; opt.selected = true;
    select.appendChild(opt);
    const display = select.parentElement.querySelector('.searchable-select-display');
    if (display) display.value = data.name;
    closeCustomerModal();
}

renderCart();
</script>
