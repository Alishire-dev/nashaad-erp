<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportStockDataSeeder extends Seeder
{
    public function run()
    {
        $path = APPPATH . 'Database/Seeds/data/original_stock.tsv';

        if (! is_file($path)) {
            echo "Data file not found: {$path}\n";
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $updated  = 0;
        $notFound = 0;
        $skipped  = 0;

        foreach ($lines as $line) {
            $cols = explode("\t", $line);
            // code, name, category, unit, stock, reorder, cost, r_price, w_price, prom_price, tax, expiry
            if (count($cols) < 12) {
                $skipped++;
                continue;
            }

            [$code, , , , $stock, , $cost, $rPrice, $wPrice, $promPrice, , $expiry] = $cols;

            $code   = trim($code);
            $stock  = trim($stock);
            $expiry = trim($expiry);

            if ($code === '') {
                $skipped++;
                continue;
            }

            $item = $this->db->table('items')->where('item_code', $code)->get()->getRowArray();

            if (! $item) {
                $notFound++;
                continue;
            }

            // "-" in the Stock column means this item isn't stock-tracked in the
            // original system (matches manage_stock=0 in ours) — everything else
            // is a real number, including negatives (over-sold items) and decimals.
            $manageStock  = $stock === '-' ? 0 : 1;
            $currentStock = $stock === '-' ? 0 : (float) $stock;

            $this->db->table('items')->where('id', $item['id'])->update([
                'manage_stock'    => $manageStock,
                'current_stock'   => $currentStock,
                'purchase_price'  => (float) trim($cost),
                'sales_price'     => (float) trim($rPrice),
                'wholesale_price' => (float) trim($wPrice),
                'minimum_price'   => (float) trim($promPrice),
                'expiry_date'     => $expiry === '-' ? null : $expiry,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $updated++;
        }

        echo "Stock data import complete: {$updated} items updated, {$notFound} item_codes not found, {$skipped} malformed rows skipped.\n";
        if ($notFound > 0) {
            echo "NOTE: {$notFound} item_codes in this file don't exist in the items table — run\n";
            echo "ImportOriginalItemsSeeder first if you haven't, or those codes genuinely aren't in\n";
            echo "the earlier Items List export.\n";
        }
    }
}
