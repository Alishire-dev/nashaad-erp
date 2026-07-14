<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchases extends Migration
{
    public function up()
    {
        // ---------------- purchases (header) ----------------
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'      => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'supplier_id'    => ['type' => 'INT', 'unsigned' => true],
            'reference_no'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'purchase_date'  => ['type' => 'DATE'],
            'due_date'       => ['type' => 'DATE', 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'received', 'partial'], 'default' => 'received'],
            'subtotal'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'grand_total'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'amount_paid'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'note'           => ['type' => 'TEXT', 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('purchases', true);

        // ---------------- purchase_items (lines) ----------------
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'purchase_id'  => ['type' => 'INT', 'unsigned' => true],
            'item_id'      => ['type' => 'INT', 'unsigned' => true],
            'quantity'     => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'cost_price'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'tax_percent'  => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'tax_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'discount_pct' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id');
        $this->forge->createTable('purchase_items', true);

        // ---------------- purchase_returns (header, mirrors purchases) ----------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'     => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'purchase_id'   => ['type' => 'INT', 'unsigned' => true],
            'return_date'   => ['type' => 'DATE'],
            'reason'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'grand_total'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('purchase_returns', true);

        // ---------------- purchase_return_items ----------------
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'purchase_return_id' => ['type' => 'INT', 'unsigned' => true],
            'item_id'            => ['type' => 'INT', 'unsigned' => true],
            'quantity'           => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'cost_price'         => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'total_amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('purchase_return_id', 'purchase_returns', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id');
        $this->forge->createTable('purchase_return_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('purchase_return_items', true);
        $this->forge->dropTable('purchase_returns', true);
        $this->forge->dropTable('purchase_items', true);
        $this->forge->dropTable('purchases', true);
    }
}
