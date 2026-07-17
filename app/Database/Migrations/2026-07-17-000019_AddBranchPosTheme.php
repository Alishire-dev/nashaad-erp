<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchPosTheme extends Migration
{
    public function up()
    {
        $this->forge->addColumn('branches', [
            'pos_theme' => ['type' => 'ENUM', 'constraint' => ['style_a', 'style_b'], 'default' => 'style_b', 'after' => 'email'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', ['pos_theme']);
    }
}
