<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportLegacyPurchasesSeeder extends Seeder
{
    /**
     * Imports historical purchase HEADER records (date, invoice no, supplier,
     * totals, pay status, created-by) from a reference export. Deliberately
     * does NOT go through PurchaseModel::createWithLines(): that method
     * posts real stock-in movements and a ledger entry per line, and this
     * data has no item-level breakdown — only header totals. Running it
     * through the normal path would fabricate stock/ledger history we don't
     * actually have. These import as purchase headers only, with no
     * purchase_items rows, no stock_adjustments, no money_transactions.
     *
     * Idempotent: matched by reference_no, safe to re-run.
     */
    public function run()
    {
        $branchId = 1;
        $file = __DIR__ . '/data/legacy_purchases.tsv';

        if (! file_exists($file)) {
            echo "legacy_purchases.tsv not found — skipped.\n";
            return;
        }

        // ---- Resolve/create the users referenced as "Created by" ----
        $userCache = [];
        $resolveUser = function (string $name) use (&$userCache, $branchId) {
            if (isset($userCache[$name])) {
                return $userCache[$name];
            }

            if ($name === 'Admin') {
                $row = $this->db->table('users')->where('username', 'admin')->get()->getRowArray();
                $userCache[$name] = $row['id'] ?? null;
                return $userCache[$name];
            }

            $existing = $this->db->table('users')->where('full_name', $name)->get()->getRowArray();
            if ($existing) {
                $userCache[$name] = $existing['id'];
                return $existing['id'];
            }

            $username = strtolower(str_replace(' ', '.', $name));
            $roleRow  = $this->db->table('roles')->where('name', 'Manager')->get()->getRowArray();

            $this->db->table('users')->insert([
                'branch_id' => $branchId,
                'role_id'   => $roleRow['id'] ?? 1,
                'username'  => $username,
                'full_name' => $name,
                // Imported historical record, not a real login — locked out
                // with an unusable hash rather than a guessable default password.
                'password'  => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'status'    => 'inactive',
            ]);
            $id = $this->db->insertID();
            $userCache[$name] = $id;
            echo "Created historical user record for '{$name}' (inactive, no login).\n";
            return $id;
        };

        // ---- Resolve/create suppliers by name ----
        $supplierCache = [];
        $resolveSupplier = function (string $name) use (&$supplierCache, $branchId) {
            if (isset($supplierCache[$name])) {
                return $supplierCache[$name];
            }
            $existing = $this->db->table('suppliers')->where('branch_id', $branchId)->where('name', $name)->get()->getRowArray();
            if ($existing) {
                $supplierCache[$name] = $existing['id'];
                return $existing['id'];
            }
            $this->db->table('suppliers')->insert([
                'branch_id' => $branchId,
                'name'      => $name,
                'status'    => 'active',
            ]);
            $id = $this->db->insertID();
            $supplierCache[$name] = $id;
            return $id;
        };

        $payStatusMap = [
            'Paid'        => 'paid',
            'Partial'     => 'partial',
            'Cancelled'   => 'cancelled',
            'Requisition' => 'requisition',
        ];

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $imported = 0;
        $skipped  = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = explode("\t", $line);
            if (count($cols) < 9) {
                continue;
            }

            [$date, $invoiceNo, $reference, $supplierName, $totalAmt, $wTax, $paidAmt, $balance, $payStatusText, $createdByName] = $cols;

            $exists = $this->db->table('purchases')->where('branch_id', $branchId)->where('reference_no', $invoiceNo)->countAllResults();
            if ($exists > 0) {
                $skipped++;
                continue;
            }

            [$d, $m, $y] = explode('-', trim($date));
            $isoDate = "{$y}-{$m}-{$d}";

            $supplierId = $resolveSupplier(trim($supplierName));
            $userId     = $resolveUser(trim($createdByName));

            $this->db->table('purchases')->insert([
                'branch_id'     => $branchId,
                'supplier_id'   => $supplierId,
                'reference_no'  => $invoiceNo,
                'purchase_date' => $isoDate,
                'status'        => 'received',
                'subtotal'      => (float) $totalAmt,
                'grand_total'   => (float) $totalAmt,
                'amount_paid'   => (float) $paidAmt,
                'pay_status'    => $payStatusMap[trim($payStatusText)] ?? 'unpaid',
                'note'          => 'Imported from legacy records — header only, no item-level detail available. Does not affect stock or ledger.',
                'created_by'    => $userId ?: 1,
                'created_at'    => $isoDate . ' 00:00:00',
            ]);
            $imported++;
        }

        echo "Legacy purchases: {$imported} imported, {$skipped} already present (skipped).\n";
    }
}
