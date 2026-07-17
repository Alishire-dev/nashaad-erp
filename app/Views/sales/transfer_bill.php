<h2>🔀 Transfer Bill — Invoice <?= esc($sale['invoice_no']) ?></h2>

<form method="post" action="<?= site_url('sales/transfer-bill/' . $sale['id']) ?>" style="max-width:500px;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>From*</label>
        <select disabled><option><?= esc($sale['customer_name'] ?? 'WALK-IN') ?></option></select>
    </div>
    <div class="form-group">
        <label>To*</label>
        <select name="to_customer_id" data-searchable required>
            <option value="">Select To Account</option>
            <?php foreach ($customers as $c): ?>
                <?php if ($c['id'] != $sale['customer_id']): ?>
                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Narrative:</label><textarea name="narrative" rows="3" placeholder="Reason for Transfer"></textarea></div>

    <button class="btn" style="background:#c0392b;" type="button" onclick="window.history.back()">✕ Close</button>
    <button class="btn green" type="submit">Submit ➤</button>
</form>
