<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label Sheet</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
    <style>
        * { box-sizing:border-box; }
        body { font-family: Arial, sans-serif; margin:0; padding:10px; }
        .sheet { display:flex; flex-wrap:wrap; gap:8px; }
        .label {
            width:220px; border:1px solid #ccc; border-radius:4px; padding:8px;
            text-align:center; page-break-inside: avoid;
        }
        .label .name { font-size:12px; font-weight:bold; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .label .price { font-size:14px; font-weight:bold; margin-top:2px; }
        @media print {
            .no-print { display:none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print</button>
    <div class="sheet">
        <?php foreach ($items as $item): ?>
        <?php $code = $item['sku'] ?: ('ITM' . str_pad((string) $item['id'], 6, '0', STR_PAD_LEFT)); ?>
        <div class="label">
            <div class="name"><?= esc($item['name']) ?></div>
            <svg class="barcode"
                 data-code="<?= esc($code) ?>"></svg>
            <div class="price">$<?= number_format((float) $item['sales_price'], 2) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
        <p>No items selected.</p>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.barcode').forEach(function (el) {
            JsBarcode(el, el.getAttribute('data-code'), {
                format: 'CODE128', width: 1.5, height: 40, fontSize: 12, margin: 4,
            });
        });
    </script>
</body>
</html>
