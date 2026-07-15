<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriceChangeFlagAndConversions extends Migration
{
    public function up()
    {
        // "Price Change Affect All Branches?" — informational/forward-compatible
        // until multi-branch actually exists; stored now so the choice isn't lost.
        $this->forge->addColumn('items', [
            'price_change_all_branches' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'after'      => 'profit_margin',
            ],
        ]);

        // Item Conversion: defines a "recipe" — e.g. 1 case (parent) = 24 pieces (child).
        // Creates a real child item in the catalog, linked back to its parent + rate.
        // Executing an actual stock conversion (moving qty from parent to child) is the
        // separate "Stock Conversion" feature (still pending) — this table only stores
        // the recipe/relationship, matching what the original's "Item Conversion" modal
        // actually captures (no quantity-to-convert field, just the definition).
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'       => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'parent_item_id'  => ['type' => 'INT', 'unsigned' => true],
            'child_item_id'   => ['type' => 'INT', 'unsigned' => true],
            'conversion_rate' => ['type' => 'DECIMAL', 'constraint' => '12,3'], // how many child units per 1 parent unit
            'description'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'      => ['type' => 'INT', 'unsigned' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('parent_item_id', 'items', 'id');
        $this->forge->addForeignKey('child_item_id', 'items', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('item_conversions', true);
    }

    public function down()
    {
        $this->forge->dropTable('item_conversions', true);
        $this->forge->dropColumn('items', ['price_change_all_branches']);
    }
}
