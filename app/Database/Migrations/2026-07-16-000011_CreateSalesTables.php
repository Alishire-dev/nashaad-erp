<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesTables extends Migration
{
    public function up()
    {
        // ---------------- customers ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'  => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'address'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->createTable('customers', true);

        // NOTE: the default WALK-IN customer is NOT inserted here — the
        // default branch row (id=1) only exists after InitialDataSeeder
        // runs, which happens AFTER migrations. Inserting it here caused
        // a real FK constraint failure on a fresh database. Moved to
        // InitialDataSeeder instead, same place branches/roles are seeded.

        // ---------------- sales (header) ----------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'     => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'customer_id'   => ['type' => 'INT', 'unsigned' => true],
            'invoice_no'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'sale_date'     => ['type' => 'DATE'],
            'status'        => ['type' => 'ENUM', 'constraint' => ['completed', 'hold', 'cancelled'], 'default' => 'completed'],
            'subtotal'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'discount_pct'  => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'discount_amt'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'tax_amount'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'grand_total'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'amount_paid'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'pay_mode'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['branch_id', 'invoice_no']);
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('customer_id', 'customers', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('sales', true);

        // ---------------- sale_items (lines) ----------------
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sale_id'      => ['type' => 'INT', 'unsigned' => true],
            'item_id'      => ['type' => 'INT', 'unsigned' => true],
            'quantity'     => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'unit_price'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'discount_pct' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'tax_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('sale_id', 'sales', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id');
        $this->forge->createTable('sale_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('sale_items', true);
        $this->forge->dropTable('sales', true);
        $this->forge->dropTable('customers', true);
    }
}
