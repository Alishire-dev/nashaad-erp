<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemCodeAndOrderFlag extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items', [
            'item_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'id',
            ],
            'order_item' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'purpose',
            ],
        ]);

        // Backfill existing rows with a sequential code so nothing is left blank
        $items = $this->db->table('items')->select('id')->orderBy('id', 'ASC')->get()->getResultArray();
        foreach ($items as $i => $row) {
            $code = 'ITM' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $this->db->table('items')->where('id', $row['id'])->update(['item_code' => $code]);
        }

        $this->db->query('CREATE UNIQUE INDEX idx_items_item_code ON items (item_code)');
    }

    public function down()
    {
        $this->forge->dropColumn('items', ['item_code', 'order_item']);
    }
}
