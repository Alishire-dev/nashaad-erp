<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchasePayments extends Migration
{
    public function up()
    {
        // Real itemized payment history — the original's "View Payments"
        // modal shows a real table (Payment Date/Payment/Payment Type/
        // Voucher No/Payment Note/Created by), not a single total. Our
        // purchases.amount_paid stays as a denormalized running total
        // (kept in sync on insert/delete here) so existing Purchase List
        // balance/pay_status logic doesn't need to change.
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'purchase_id'   => ['type' => 'INT', 'unsigned' => true],
            'payment_date'  => ['type' => 'DATE'],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_type'  => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'cash'],
            'voucher_no'    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'payment_note'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('purchase_payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('purchase_payments', true);
    }
}
