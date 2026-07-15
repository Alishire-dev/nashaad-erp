<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WidenAlertQtyColumn extends Migration
{
    /**
     * The original system's real data includes reorder values like
     * 100000000001 and 99999999999 (likely typos/test entries from the
     * live system, but real data nonetheless) — these overflow the
     * original DECIMAL(12,2) column (max ~9,999,999,999.99). Widening
     * to DECIMAL(20,2) so the import doesn't silently truncate or fail.
     */
    public function up()
    {
        $this->forge->modifyColumn('items', [
            'alert_qty' => [
                'name'       => 'alert_qty',
                'type'       => 'DECIMAL',
                'constraint' => '20,2',
                'default'    => 0,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('items', [
            'alert_qty' => [
                'name'       => 'alert_qty',
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
        ]);
    }
}
