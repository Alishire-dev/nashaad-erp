<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreTables extends Migration
{
    public function up()
    {
        // ---------------- branches ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'address'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('branches', true);

        // ---------------- roles ----------------
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_superadmin'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['active', 'restricted'], 'default' => 'active'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('roles', true);

        // ---------------- permissions (module registry) ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'module_key' => ['type' => 'VARCHAR', 'constraint' => 60],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('permissions', true);

        // ---------------- role_permissions ----------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'       => ['type' => 'INT', 'unsigned' => true],
            'permission_id' => ['type' => 'INT', 'unsigned' => true],
            'can_view'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'can_add'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_edit'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_delete'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['role_id', 'permission_id']);
        $this->forge->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', '', 'CASCADE');
        $this->forge->createTable('role_permissions', true);

        // ---------------- users ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'  => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'role_id'    => ['type' => 'INT', 'unsigned' => true],
            'full_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 60],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'photo'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'last_login' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('username');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('role_id', 'roles', 'id');
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('branches', true);
    }
}
