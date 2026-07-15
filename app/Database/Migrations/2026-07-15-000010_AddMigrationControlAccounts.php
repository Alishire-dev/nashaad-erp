<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMigrationControlAccounts extends Migration
{
    /**
     * "Migration Control Account" in the original ties a stock adjustment or
     * price change to an equity/GL account. Full chart-of-accounts (Day 5)
     * isn't built yet, so this is a lightweight standalone lookup for now —
     * forward-compatible: when real chart_of_accounts lands, this table (or
     * the FK target) can be swapped without touching stock_adjustments'
     * shape.
     */
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('migration_control_accounts', true);

        $this->db->table('migration_control_accounts')->insertBatch([
            ['name' => 'Migration Control', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Stock Adjustment', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Inventory Write-off', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Opening Balance Equity', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
        ]);

        $this->forge->addColumn('stock_adjustments', [
            'adjustment_date'              => ['type' => 'DATE', 'null' => true, 'after' => 'branch_id'],
            'migration_control_account_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'reason'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('stock_adjustments', ['adjustment_date', 'migration_control_account_id']);
        $this->forge->dropTable('migration_control_accounts', true);
    }
}
