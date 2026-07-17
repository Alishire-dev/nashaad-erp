<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoices extends Migration
{
    public function up()
    {
        // Standalone, header-only invoicing — distinct from POS Sales
        // (which is itemized via sale_items). The spec's New Invoice form
        // has no line-items table, just a Revenue/Income Account picker
        // and a single Grand Total amount — this is a lightweight
        // accounts-receivable entry, e.g. invoicing a customer for a
        // service rather than itemized goods.
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'         => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'customer_id'       => ['type' => 'INT', 'unsigned' => true],
            'invoice_no'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'invoice_date'      => ['type' => 'DATE'],
            'due_date'          => ['type' => 'DATE', 'null' => true],
            'note'              => ['type' => 'TEXT', 'null' => true],
            'revenue_account_id'=> ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'grand_total'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'amount_paid'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'pay_status'        => ['type' => 'ENUM', 'constraint' => ['paid', 'partial', 'unpaid'], 'default' => 'unpaid'],
            'created_by'        => ['type' => 'INT', 'unsigned' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['branch_id', 'invoice_no']);
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('customer_id', 'customers', 'id');
        $this->forge->addForeignKey('revenue_account_id', 'chart_of_accounts', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('invoices', true);
    }

    public function down()
    {
        $this->forge->dropTable('invoices', true);
    }
}
