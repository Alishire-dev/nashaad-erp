<h2>New Invoice</h2>

<form method="post" action="<?= site_url('invoices/add') ?>" style="max-width:700px;">
    <?= csrf_field() ?>
    <div style="display:flex; gap:20px;">
        <div class="form-group" style="flex:1;">
            <label>Branch/Station*</label>
            <select disabled><option>Main Branch</option></select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Customer Name*</label>
            <select name="customer_id" required>
                <option value="">~Select Customer~</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Invoice No. <small style="font-weight:normal; color:#999;">(if left blank will be auto generated)</small></label>
        <input type="text" name="invoice_no" placeholder="If left blank will be auto generated">
    </div>

    <div style="display:flex; gap:20px;">
        <div class="form-group" style="flex:1;"><label>Invoice Date*</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="form-group" style="flex:1;"><label>Due Date*</label><input type="date" name="due_date"></div>
    </div>

    <div class="form-group"><label>Invoice Note</label><textarea name="note" rows="3" placeholder="Invoice Note"></textarea></div>

    <div style="display:flex; gap:20px; align-items:flex-end;">
        <div class="form-group" style="flex:1;">
            <label>Select Revenue/Income Account</label>
            <select name="revenue_account_id">
                <option value="">~Select Revenue/Income Account~</option>
                <?php foreach ($accounts as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= esc($a['account_name']) ?> (<?= esc($a['gl_code']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;"><label>Grand Total*</label><input type="number" step="0.01" name="grand_total" required></div>
    </div>

    <button class="btn green" type="submit">Submit</button>
    <a class="btn" style="background:#e88a2e;" href="<?= site_url('invoices') ?>">Close</a>
</form>
