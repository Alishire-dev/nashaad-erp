<h2>Raise Credit Note</h2>
<p style="color:#666; font-size:14px;">For credit not tied to a cancelled sale (e.g. a goodwill adjustment). Credit notes from cancelled sales are created automatically from the Cancelled Sales list.</p>

<form method="post" action="<?= site_url('sales/credit-notes/raise') ?>" style="max-width:500px;">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Customer</label>
        <select name="customer_id">
            <option value="">-Select-</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group"><label>Amount*</label><input type="number" step="0.01" name="amount" required></div>
    <div class="form-group"><label>Note</label><textarea name="note" rows="3" placeholder="Reason for this credit note"></textarea></div>
    <button class="btn green" type="submit">Submit</button>
    <a class="btn" href="<?= site_url('sales/credit-notes') ?>">Cancel</a>
</form>
