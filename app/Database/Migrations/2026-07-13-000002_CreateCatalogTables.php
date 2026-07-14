<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogTables extends Migration
{
    public function up()
    {
        // ---------------- units ----------------
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'   => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 60],
            'short_name'  => ['type' => 'VARCHAR', 'constraint' => 20],
            'description' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->createTable('units', true);

        // ---------------- brands ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'  => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'status'     => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->createTable('brands', true);

        // ---------------- categories ----------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'     => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'category_code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['branch_id', 'category_code']);
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->createTable('categories', true);

        // ---------------- tax_categories ----------------
        $this->forge->addField([
            'id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'   => ['type' => 'VARCHAR', 'constraint' => 60],
            'rate'   => ['type' => 'DECIMAL', 'constraint' => '6,3', 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tax_categories', true);

        // ---------------- items ----------------
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'           => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'category_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'brand_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'unit_id'             => ['type' => 'INT', 'unsigned' => true],
            'tax_category_id'     => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'tax_type'            => ['type' => 'ENUM', 'constraint' => ['inclusive', 'exclusive'], 'default' => 'inclusive'],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 150],
            'sku'                 => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'barcode'             => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'image'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'purpose'             => ['type' => 'ENUM', 'constraint' => ['for_sale', 'raw_material', 'both'], 'default' => 'for_sale'],
            'manage_stock'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'allow_negative_sale' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'alert_qty'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'purchase_price'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'sales_price'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'wholesale_price'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'minimum_price'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'profit_margin'       => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'sales_commission'    => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'expiry_date'         => ['type' => 'DATE', 'null' => true],
            'current_stock'       => ['type' => 'DECIMAL', 'constraint' => '14,3', 'default' => 0],
            'description'         => ['type' => 'TEXT', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('name');
        $this->forge->addKey('barcode');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('brand_id', 'brands', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('unit_id', 'units', 'id');
        $this->forge->addForeignKey('tax_category_id', 'tax_categories', 'id');
        $this->forge->createTable('items', true);

        // ---------------- item_price_log ----------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_id'    => ['type' => 'INT', 'unsigned' => true],
            'old_price'  => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'new_price'  => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'changed_by' => ['type' => 'INT', 'unsigned' => true],
            'changed_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('item_id', 'items', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id');
        $this->forge->createTable('item_price_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('item_price_log', true);
        $this->forge->dropTable('items', true);
        $this->forge->dropTable('tax_categories', true);
        $this->forge->dropTable('categories', true);
        $this->forge->dropTable('brands', true);
        $this->forge->dropTable('units', true);
    }
}
