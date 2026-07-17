<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendSalesForInvoiceDetail extends Migration
{
    public function up()
    {
        // ---- extra header fields the Sales Invoice / Change Sales Details
        //      modal / Apply Discount modal all need ----
        $this->forge->addColumn('sales', [
            'sales_person_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'customer_id'],
            'due_date'        => ['type' => 'DATE', 'null' => true, 'after' => 'sale_date'],
            'lpo_number'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true, 'after' => 'due_date'],
            'note'            => ['type' => 'TEXT', 'null' => true, 'after' => 'lpo_number'],
            'discount_type'   => ['type' => 'ENUM', 'constraint' => ['percentage', 'fixed'], 'default' => 'percentage', 'after' => 'discount_pct'],
            'pay_status'      => ['type' => 'ENUM', 'constraint' => ['paid', 'partial', 'unpaid', 'cancelled'], 'default' => 'unpaid', 'after' => 'amount_paid'],
        ]);
        // No formal FK constraint on sales_person_id: CI4's addForeignKey()
        // is designed to run during createTable(), not as a standalone
        // ALTER on an existing table — attempting it here threw a real
        // "SQLite does not support foreign key names" error. A plain
        // nullable column reference is enough; several other soft
        // references in this schema already work this way.

        // ---- sale_payments (itemized history, same pattern as purchase_payments) ----
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sale_id'      => ['type' => 'INT', 'unsigned' => true],
            'payment_date' => ['type' => 'DATE'],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'cash'],
            'payment_note' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('sale_id', 'sales', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('sale_payments', true);

        // ---- sale_returns (header, mirrors sales) ----
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'   => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'sale_id'     => ['type' => 'INT', 'unsigned' => true],
            'return_date' => ['type' => 'DATE'],
            'receipt_ref' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'narrative'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'created_by'  => ['type' => 'INT', 'unsigned' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('sale_id', 'sales', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('sale_returns', true);

        // ---- sale_return_items ----
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sale_return_id'  => ['type' => 'INT', 'unsigned' => true],
            'item_id'         => ['type' => 'INT', 'unsigned' => true],
            'quantity'        => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'good_condition'  => ['type' => 'ENUM', 'constraint' => ['yes', 'no'], 'default' => 'yes'],
            'unit_price'      => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'total_amount'    => ['type' => 'DECIMAL', 'constraint' => '12,2'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('sale_return_id', 'sale_returns', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id');
        $this->forge->createTable('sale_return_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('sale_return_items', true);
        $this->forge->dropTable('sale_returns', true);
        $this->forge->dropTable('sale_payments', true);
        $this->forge->dropColumn('sales', ['sales_person_id', 'due_date', 'lpo_number', 'note', 'discount_type', 'pay_status']);
    }
}
