<h2><?= $direction === 'in' ? 'Receive Payment' : 'Make Payment' ?></h2>

<form method="post" action="<?= site_url('accounting/money/' . ($direction === 'in' ? 'receive-payment' : 'make-payment')) ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Account*</label>
        <select name="account_name" required>
            <option value="">-Select-</option>
            <?php foreach ($accounts as $a): ?>
                <option value="<?= esc($a['account_name']) ?>"><?= esc($a['account_name']) ?> (<?= esc($a['gl_code']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Payment Date*</label><input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required></div>
    <div class="form-group">
        <label>Pay Mode*</label>
        <select name="pay_mode" required>
            <option value="cash">Cash</option>
            <option value="mpesa">M-Pesa</option>
            <option value="card">Card</option>
            <option value="cheque">Cheque</option>
        </select>
    </div>
    <div class="form-group"><label>Amount*</label><input type="number" step="0.01" name="amount" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="e.g. what this payment is for"></textarea></div>
    <button class="btn green" type="submit">Submit</button>
    <a class="btn" href="<?= site_url('accounting/money') ?>">Cancel</a>
</form>
