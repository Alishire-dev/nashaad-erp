<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlaggedAndCreditNotes extends Migration
{
    public function up()
    {
        // Flagged: a real business rule, not a random cosmetic pill —
        // a cancelled sale is flagged when it had already collected money
        // (pay_status was paid/partial) BEFORE being cancelled. Cancelling
        // an order that was never paid is routine; cancelling one that
        // already took payment is the case worth a manager's attention.
        $this->forge->addColumn('sales', [
            'flagged' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'pay_status'],
        ]);

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'    => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'serial_no'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'sale_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true], // null = manually raised, not tied to a sale
            'invoice_no'   => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'customer_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'credit_date'  => ['type' => 'DATE'],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'note'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('sale_id', 'sales', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('credit_notes', true);
    }

    public function down()
    {
        $this->forge->dropTable('credit_notes', true);
        $this->forge->dropColumn('sales', ['flagged']);
    }
}
