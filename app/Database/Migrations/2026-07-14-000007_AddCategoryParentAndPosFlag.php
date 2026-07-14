<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryParentAndPosFlag extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categories', [
            'parent_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'category_code',
            ],
            'show_on_pos' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'name',
            ],
        ]);

        // Not adding an enforced FK constraint here: altering an existing
        // production table to add a self-referencing FK is fragile across
        // MySQL versions/engines. parent_id integrity is enforced at the
        // application layer (dropdown only ever offers real category IDs).
    }

    public function down()
    {
        $this->forge->dropColumn('categories', ['parent_id', 'show_on_pos']);
    }
}
