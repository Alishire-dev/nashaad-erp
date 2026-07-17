<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchEmail extends Migration
{
    public function up()
    {
        $this->forge->addColumn('branches', [
            'email' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'phone'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', ['email']);
    }
}
