<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseReturnModel extends Model
{
    protected $table         = 'purchase_returns';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'purchase_id', 'return_date', 'reason', 'grand_total', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->select('purchase_returns.*, purchases.reference_no')
            ->join('purchases', 'purchases.id = purchase_returns.purchase_id', 'left')
            ->where('purchase_returns.branch_id', $branchId)
            ->orderBy('purchase_returns.id', 'DESC')
            ->findAll();
    }

    /**
     * @param array $lines each: ['item_id','quantity','cost_price']
     */
    public function createWithLines(array $header, array $lines, int $userId): int|false
    {
        $db = db_connect();
        $db->transStart();

        $grandTotal = 0.0;
        foreach ($lines as &$line) {
            $line['total_amount'] = round((float) $line['quantity'] * (float) $line['cost_price'], 2);
            $grandTotal += $line['total_amount'];
        }
        unset($line);

        $header['grand_total'] = round($grandTotal, 2);
        $header['created_by']  = $userId;
        $header['created_at']  = date('Y-m-d H:i:s');

        $returnId = $this->insert($header);

        if (! $returnId) {
            $db->transRollback();
            return false;
        }

        $stockModel = model(StockAdjustmentModel::class);

        foreach ($lines as $line) {
            $this->db->table('purchase_return_items')->insert([
                'purchase_return_id' => $returnId,
                'item_id'            => $line['item_id'],
                'quantity'           => $line['quantity'],
                'cost_price'         => $line['cost_price'],
                'total_amount'       => $line['total_amount'],
            ]);

            // Stock-out: goods physically leave, going back to the supplier
            $stockModel->record(
                $header['branch_id'],
                (int) $line['item_id'],
                (float) $line['quantity'],
                'out',
                'purchase_return',
                $userId,
                'Purchase Return #' . $returnId
            );
        }

        $db->transComplete();

        return $db->transStatus() ? $returnId : false;
    }
}
