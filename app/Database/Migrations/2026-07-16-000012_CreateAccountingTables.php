<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountingTables extends Migration
{
    public function up()
    {
        // ---------------- account_types (top tier: ASSETS, LIABILITIES, etc) ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('account_types', true);

        // ---------------- sub_account_types (mid tier: Cash & Cash Equivalent, etc) ----------------
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sub_account_code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'account_type_id'  => ['type' => 'INT', 'unsigned' => true],
            'description'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('account_type_id', 'account_types', 'id');
        $this->forge->createTable('sub_account_types', true);

        // ---------------- chart_of_accounts (bottom tier: actual GL accounts) ----------------
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'          => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'account_name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'gl_code'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'sub_account_type_id'=> ['type' => 'INT', 'unsigned' => true],
            'description'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('gl_code');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('sub_account_type_id', 'sub_account_types', 'id');
        $this->forge->createTable('chart_of_accounts', true);

        // ---------------- money_transactions (the ledger itself) ----------------
        // Single-entry per row (In/Out against one account), matching the
        // original's Manage Money > Payments table structure exactly —
        // not full double-entry debit/credit pairs. Every Purchase and Sale
        // auto-posts a row here once fully wired up.
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'          => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'trans_by'           => ['type' => 'INT', 'unsigned' => true],
            'payment_date'       => ['type' => 'DATE'],
            'pay_mode'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'description'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'chart_of_account_id'=> ['type' => 'INT', 'unsigned' => true],
            'voucher_no'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'amount_in'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'amount_out'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'reference_type'     => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'reference_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('trans_by', 'users', 'id');
        $this->forge->addForeignKey('chart_of_account_id', 'chart_of_accounts', 'id');
        $this->forge->createTable('money_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('money_transactions', true);
        $this->forge->dropTable('chart_of_accounts', true);
        $this->forge->dropTable('sub_account_types', true);
        $this->forge->dropTable('account_types', true);
    }
}
