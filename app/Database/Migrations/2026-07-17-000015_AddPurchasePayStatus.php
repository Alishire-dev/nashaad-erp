<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchasePayStatus extends Migration
{
    public function up()
    {
        // The original's Purchase List has a "Pay Status" column with
        // values Paid/Partial/Cancelled/Requisition — a DIFFERENT concept
        // from our existing `status` field (which tracks whether goods
        // were physically received: pending/received/partial). A purchase
        // can be fully received but unpaid, or cancelled after being
        // partially paid — these need to be tracked independently.
        $this->forge->addColumn('purchases', [
            'pay_status' => [
                'type'       => 'ENUM',
                'constraint' => ['paid', 'partial', 'unpaid', 'cancelled', 'requisition'],
                'default'    => 'unpaid',
                'after'      => 'amount_paid',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('purchases', ['pay_status']);
    }
}
