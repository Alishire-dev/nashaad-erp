<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuppliers extends Migration
{
    public function up()
    {
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
        $this->forge->createTable('suppliers', true);
    }

    public function down()
    {
        $this->forge->dropTable('suppliers', true);
    }
}
