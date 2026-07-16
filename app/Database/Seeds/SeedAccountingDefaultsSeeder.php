<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SeedAccountingDefaultsSeeder extends Seeder
{
    /**
     * Standalone from InitialDataSeeder on purpose — same reasoning as
     * SeedWalkInCustomerSeeder: that seeder already ran once on the live
     * site, so new inserts belong in their own idempotent seeder instead
     * of risking a duplicate-admin-user error on re-run.
     */
    public function run()
    {
        if ($this->db->table('account_types')->countAllResults() > 0) {
            echo "Accounting defaults already seeded — skipped.\n";
            return;
        }

        $accountTypes = ['ASSETS', 'LIABILITIES', 'Equity Capital', 'Income/Revenue', 'EXPENSES'];
        $typeIds = [];
        foreach ($accountTypes as $name) {
            $this->db->table('account_types')->insert(['name' => $name, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
            $typeIds[$name] = $this->db->insertID();
        }

        $subTypes = [
            ['code' => '1',  'name' => 'Cash & Cash Equivalent', 'type' => 'ASSETS', 'desc' => 'All Payment Accounts'],
            ['code' => '2',  'name' => 'Current Assets',         'type' => 'ASSETS'],
            ['code' => '3',  'name' => 'Non-Current Assets',     'type' => 'ASSETS'],
            ['code' => '4',  'name' => 'Current Liability',      'type' => 'LIABILITIES'],
            ['code' => '5',  'name' => 'Long-term Liability',    'type' => 'LIABILITIES'],
            ['code' => '6',  'name' => 'Equity Accounts',        'type' => 'Equity Capital'],
            ['code' => '7',  'name' => 'Revenues',                'type' => 'Income/Revenue'],
            ['code' => '8',  'name' => 'Operating Expenses',     'type' => 'EXPENSES'],
            ['code' => '9',  'name' => 'Depreciation Expenses',  'type' => 'EXPENSES'],
            ['code' => '10', 'name' => 'Reconciliation Accounts','type' => 'EXPENSES'],
            ['code' => '12', 'name' => 'General Assets',         'type' => 'ASSETS'],
            ['code' => '16', 'name' => 'Furnitures',              'type' => 'ASSETS'],
            ['code' => '17', 'name' => 'VEHICLES',                'type' => 'ASSETS'],
        ];
        $subTypeIds = [];
        foreach ($subTypes as $s) {
            $this->db->table('sub_account_types')->insert([
                'sub_account_code' => $s['code'],
                'name'             => $s['name'],
                'account_type_id'  => $typeIds[$s['type']],
                'description'      => $s['desc'] ?? null,
                'status'           => 'active',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
            $subTypeIds[$s['name']] = $this->db->insertID();
        }

        $accounts = [
            ['name' => 'Sales Revenue',              'gl' => 'GL0002', 'sub' => 'Revenues',                'status' => 'active'],
            ['name' => 'Cost Of Goods Sold',         'gl' => 'GL0003', 'sub' => 'Operating Expenses',      'status' => 'active'],
            ['name' => 'Cash',                        'gl' => 'GL0004', 'sub' => 'Cash & Cash Equivalent',  'status' => 'active'],
            ['name' => 'EVC',                         'gl' => 'GL0005', 'sub' => 'Cash & Cash Equivalent',  'status' => 'active'],
            ['name' => 'Bank',                        'gl' => 'GL0006', 'sub' => 'Cash & Cash Equivalent',  'status' => 'inactive'],
            ['name' => 'POINTS',                      'gl' => 'GL0007', 'sub' => 'Cash & Cash Equivalent',  'status' => 'inactive'],
            ['name' => 'CHEQUE',                      'gl' => 'GL0008', 'sub' => 'Cash & Cash Equivalent',  'status' => 'inactive'],
            ['name' => 'Tax Payable(VAT OUTPUT)',    'gl' => 'GL0009', 'sub' => 'Current Liability',       'status' => 'active', 'desc' => 'Sales and Invoices'],
            ['name' => 'Tax Receivable(VAT Input)',  'gl' => 'GL0010', 'sub' => 'Current Assets',          'status' => 'active'],
            ['name' => 'Withholding Tax',            'gl' => 'GL0011', 'sub' => 'Current Liability',       'status' => 'active'],
            ['name' => 'Commission Payable',         'gl' => 'GL0012', 'sub' => 'Current Liability',       'status' => 'active'],
            ['name' => 'Suppliers Advance',          'gl' => 'GL0013', 'sub' => 'Current Assets',          'status' => 'active'],
            ['name' => 'Issued Products',            'gl' => 'GL0014', 'sub' => 'Operating Expenses',      'status' => 'active'],
        ];
        foreach ($accounts as $a) {
            $this->db->table('chart_of_accounts')->insert([
                'branch_id'           => 1,
                'account_name'        => $a['name'],
                'gl_code'             => $a['gl'],
                'sub_account_type_id' => $subTypeIds[$a['sub']],
                'description'         => $a['desc'] ?? null,
                'status'              => $a['status'],
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        echo "Accounting defaults seeded: " . count($accountTypes) . " account types, "
            . count($subTypes) . " sub-account types, " . count($accounts) . " chart of accounts.\n";
    }
}
