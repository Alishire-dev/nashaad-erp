<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // ---------------- branch ----------------
        $this->db->table('branches')->insert([
            'name' => 'Main Branch', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'),
        ]);

        // ---------------- roles ----------------
        $roles = [
            ['name' => 'Administrator', 'description' => 'Administrator', 'is_superadmin' => 1, 'status' => 'restricted'],
            ['name' => 'Manager', 'description' => 'General Manager', 'is_superadmin' => 0, 'status' => 'active'],
            ['name' => 'Stock Manager', 'description' => 'Stock Manager', 'is_superadmin' => 0, 'status' => 'active'],
            ['name' => 'Cashier', 'description' => 'Cashier', 'is_superadmin' => 0, 'status' => 'active'],
            ['name' => 'Sales Person', 'description' => 'Sales Person', 'is_superadmin' => 0, 'status' => 'active'],
        ];
        foreach ($roles as $r) {
            $r['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('roles')->insert($r);
        }

        // ---------------- permission modules ----------------
        $modules = [
            ['module_key' => 'dashboard', 'label' => 'Dashboard'],
            ['module_key' => 'items', 'label' => 'Items/Products'],
            ['module_key' => 'purchase', 'label' => 'Purchase'],
            ['module_key' => 'pos', 'label' => 'POS'],
            ['module_key' => 'sales', 'label' => 'Sales'],
            ['module_key' => 'suppliers', 'label' => 'Suppliers'],
            ['module_key' => 'customers', 'label' => 'Customers'],
            ['module_key' => 'expenses', 'label' => 'Expenses'],
            ['module_key' => 'accounting', 'label' => 'Accounting'],
            ['module_key' => 'users_management', 'label' => 'Users Management'],
        ];
        foreach ($modules as $m) {
            $this->db->table('permissions')->insert($m);
        }

        // ---------------- role_permissions ----------------
        // Manager gets full access to everything except Users Management (edit only, no delete)
        $managerRoleId = 2;
        $permissionIds = $this->db->table('permissions')->get()->getResultArray();
        foreach ($permissionIds as $p) {
            $this->db->table('role_permissions')->insert([
                'role_id'       => $managerRoleId,
                'permission_id' => $p['id'],
                'can_view'      => 1,
                'can_add'       => 1,
                'can_edit'      => 1,
                'can_delete'    => $p['module_key'] === 'users_management' ? 0 : 1,
            ]);
        }

        // Cashier: view + add on pos/sales/customers only
        $cashierRoleId = 4;
        $cashierModules = ['dashboard', 'pos', 'sales', 'customers'];
        foreach ($permissionIds as $p) {
            if (in_array($p['module_key'], $cashierModules, true)) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $cashierRoleId, 'permission_id' => $p['id'],
                    'can_view' => 1, 'can_add' => 1, 'can_edit' => 0, 'can_delete' => 0,
                ]);
            }
        }

        // ---------------- tax categories ----------------
        $this->db->table('tax_categories')->insert(['name' => 'No Tax', 'rate' => 0.000, 'status' => 'active']);
        $this->db->table('tax_categories')->insert(['name' => 'VAT 16%', 'rate' => 16.000, 'status' => 'active']);

        // ---------------- default admin user ----------------
        // username: admin / password: Admin@123 — CHANGE IMMEDIATELY after first login
        $this->db->table('users')->insert([
            'branch_id'  => 1,
            'role_id'    => 1, // Administrator (is_superadmin = 1, bypasses permission checks)
            'full_name'  => 'Administrator',
            'username'   => 'admin',
            'password'   => password_hash('Admin@123', PASSWORD_BCRYPT),
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        echo "Seed complete. Login: admin / Admin@123 — change the password immediately.\n";
    }
}
