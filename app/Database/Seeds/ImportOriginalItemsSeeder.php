<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportOriginalItemsSeeder extends Seeder
{
    private int $branchId = 1;

    public function run()
    {
        $path = APPPATH . 'Database/Seeds/data/original_items.tsv';

        if (! is_file($path)) {
            echo "Data file not found: {$path}\n";
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $categoryMap = $this->loadExistingCategories();
        $unitMap     = $this->loadExistingUnits();

        // Ensure a fallback unit exists for rows with no unit (pure services
        // like "AC", "VIP room hire", "Hall Hire" in the real data).
        if (isset($unitMap['N/A'])) {
            $unitMap[''] = $unitMap['N/A'];
        } else {
            $this->db->table('units')->insert([
                'branch_id'   => $this->branchId,
                'name'        => 'N/A',
                'short_name'  => 'n/a',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $naId = $this->db->insertID();
            $unitMap['N/A'] = $naId;
            $unitMap[''] = $naId;
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($lines as $line) {
            $cols = explode("\t", $line);
            // code, name, brand, category, unit, reorder, tax, order_item, status
            if (count($cols) < 9) {
                $skipped++;
                continue;
            }

            [$code, $name, $brand, $category, $unit, $reorder, $tax, $orderItem, $status] = $cols;

            $code     = trim($code);
            $name     = trim($name);
            $category = trim($category);
            $unit     = trim($unit);

            if ($code === '' || $name === '') {
                $skipped++;
                continue;
            }

            // Skip if this exact item_code already exists (safe to re-run the seeder)
            $exists = $this->db->table('items')->where('item_code', $code)->countAllResults();
            if ($exists > 0) {
                $skipped++;
                continue;
            }

            $categoryId = $this->resolveCategory($category, $categoryMap);
            $unitId     = $this->resolveUnit($unit, $unitMap);

            $this->db->table('items')->insert([
                'branch_id'       => $this->branchId,
                'item_code'       => $code,
                'category_id'     => $categoryId,
                'brand_id'        => null,
                'unit_id'         => $unitId,
                'tax_category_id' => 1, // "No Tax" — every row in this export is 0%
                'tax_type'        => 'inclusive',
                'name'            => $name,
                'purpose'         => 'for_sale',
                'order_item'      => strcasecmp(trim($orderItem), 'yes') === 0 ? 1 : 0,
                'manage_stock'    => 1,
                'allow_negative_sale' => 0,
                'alert_qty'       => (float) trim($reorder),
                'purchase_price'  => 0,
                'sales_price'     => 0,
                'wholesale_price' => 0,
                'minimum_price'   => 0,
                'profit_margin'   => 0,
                'sales_commission'=> 0,
                'current_stock'   => 0,
                'status'          => strcasecmp(trim($status), 'active') === 0 ? 'active' : 'inactive',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $imported++;
        }

        echo "Import complete: {$imported} items imported, {$skipped} skipped (already existed or malformed row).\n";
        echo "NOTE: purchase_price and sales_price were NOT in this export (Items List doesn't show pricing) —\n";
        echo "all imported items have price 0.00 until you set real prices via Edit Item Details, or a follow-up\n";
        echo "import if you have a pricing export from Stock Manager.\n";
    }

    private function loadExistingCategories(): array
    {
        $rows = $this->db->table('categories')->where('branch_id', $this->branchId)->get()->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['name']] = $r['id'];
        }
        return $map;
    }

    private function loadExistingUnits(): array
    {
        $rows = $this->db->table('units')->where('branch_id', $this->branchId)->get()->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['name']] = $r['id'];
        }
        return $map;
    }

    private function resolveCategory(string $name, array &$map): ?int
    {
        if ($name === '') {
            return null;
        }
        if (isset($map[$name])) {
            return $map[$name];
        }

        $count = $this->db->table('categories')->where('branch_id', $this->branchId)->countAllResults();
        $code  = 'CAT_' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);

        $this->db->table('categories')->insert([
            'branch_id'     => $this->branchId,
            'category_code' => $code,
            'name'          => $name,
            'show_on_pos'   => 1,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $id = $this->db->insertID();
        $map[$name] = $id;
        return $id;
    }

    private function resolveUnit(string $name, array &$map): ?int
    {
        if ($name === '') {
            return $map[''] ?? null;
        }
        if (isset($map[$name])) {
            return $map[$name];
        }

        $this->db->table('units')->insert([
            'branch_id'  => $this->branchId,
            'name'       => $name,
            'short_name' => $name,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $id = $this->db->insertID();
        $map[$name] = $id;
        return $id;
    }
}
