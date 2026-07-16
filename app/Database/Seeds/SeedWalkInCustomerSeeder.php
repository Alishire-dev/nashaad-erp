<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SeedWalkInCustomerSeeder extends Seeder
{
    /**
     * Standalone from InitialDataSeeder on purpose: that seeder already ran
     * once on any existing install, and re-running it would try to
     * re-insert branches/roles/the admin user, hitting the username unique
     * constraint. This one is safe to run any time — it checks for an
     * existing WALK-IN row per branch before inserting.
     */
    public function run()
    {
        $branches = $this->db->table('branches')->get()->getResultArray();

        foreach ($branches as $branch) {
            $exists = $this->db->table('customers')
                ->where('branch_id', $branch['id'])
                ->where('name', 'WALK-IN')
                ->countAllResults();

            if ($exists > 0) {
                echo "WALK-IN customer already exists for branch {$branch['id']} ({$branch['name']}) — skipped.\n";
                continue;
            }

            $this->db->table('customers')->insert([
                'branch_id'  => $branch['id'],
                'name'       => 'WALK-IN',
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            echo "WALK-IN customer created for branch {$branch['id']} ({$branch['name']}).\n";
        }
    }
}
