<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConversionReasonToStockAdjustments extends Migration
{
    public function up()
    {
        // Stock Conversion (execute a parent->child conversion) needs its
        // own reason value — the original enum only had manual_correction/
        // issued/damaged/purchase/purchase_return/sale/sale_return.
        $this->forge->modifyColumn('stock_adjustments', [
            'reason' => [
                'name'       => 'reason',
                'type'       => 'ENUM',
                'constraint' => [
                    'manual_correction', 'issued', 'damaged', 'purchase', 'purchase_return',
                    'sale', 'sale_return', 'conversion',
                ],
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('stock_adjustments', [
            'reason' => [
                'name'       => 'reason',
                'type'       => 'ENUM',
                'constraint' => [
                    'manual_correction', 'issued', 'damaged', 'purchase', 'purchase_return',
                    'sale', 'sale_return',
                ],
            ],
        ]);
    }
}
