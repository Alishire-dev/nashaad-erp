<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockAdjustments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'   => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'item_id'     => ['type' => 'INT', 'unsigned' => true],
            'direction'   => ['type' => 'ENUM', 'constraint' => ['in', 'out']],
            'quantity'    => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'reason'      => ['type' => 'ENUM', 'constraint' => [
                'manual_correction', 'issued', 'damaged', 'purchase', 'purchase_return', 'sale', 'sale_return',
            ]],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'  => ['type' => 'INT', 'unsigned' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('item_id', 'items', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('stock_adjustments', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_adjustments', true);
    }
}
