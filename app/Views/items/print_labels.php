<h2>Print Labels</h2>
<p>Select items, then open the label sheet to print.</p>

<form method="get" action="<?= site_url('items/print-labels/sheet') ?>" target="_blank" onsubmit="return buildIdsField(this)">
    <input type="hidden" name="ids" id="idsField">
    <table>
        <tr><th></th><th>Item</th><th>SKU/Barcode</th><th>Sales Price</th></tr>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><input type="checkbox" class="label-check" value="<?= $item['id'] ?>"></td>
            <td><?= esc($item['name']) ?></td>
            <td><?= esc($item['sku'] ?: ('ITM' . str_pad((string) $item['id'], 6, '0', STR_PAD_LEFT))) ?></td>
            <td><?= number_format((float) $item['sales_price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <button class="btn green" type="submit">Print Selected Labels</button>
</form>

<script>
function buildIdsField(form) {
    const checked = Array.from(document.querySelectorAll('.label-check:checked')).map(c => c.value);
    if (checked.length === 0) {
        alert('Select at least one item first.');
        return false;
    }
    document.getElementById('idsField').value = checked.join(',');
    return true;
}
</script>
