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

    /**
     * Debit Notes Report — same underlying data as the return list, filtered
     * to a date range. A debit note IS a purchase return in accounting
     * terms: it reduces what's owed to the supplier, so this reuses the
     * same line-item query rather than a separate table.
     */
    public function getForBranchInRange(int $branchId, string $fromDate, string $toDate): array
    {
        return $this->db->table('purchase_return_items')
            ->select('purchase_return_items.*, purchase_returns.return_date, purchase_returns.reason,
                    purchases.reference_no as purchase_code,
                    items.name as item_name,
                    suppliers.name as supplier_name,
                    returner.full_name as returned_by')
            ->join('purchase_returns', 'purchase_returns.id = purchase_return_items.purchase_return_id')
            ->join('purchases', 'purchases.id = purchase_returns.purchase_id')
            ->join('items', 'items.id = purchase_return_items.item_id')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->join('users as returner', 'returner.id = purchase_returns.created_by', 'left')
            ->where('purchase_returns.branch_id', $branchId)
            ->where('purchase_returns.return_date >=', $fromDate)
            ->where('purchase_returns.return_date <=', $toDate)
            ->orderBy('purchase_returns.return_date', 'DESC')
            ->get()->getResultArray();
    }

    public function getForBranch(int $branchId): array
    {
        return $this->db->table('purchase_return_items')
            ->select('purchase_return_items.*, purchase_returns.return_date, purchase_returns.reason,
                    purchases.reference_no as purchase_code,
                    items.name as item_name,
                    suppliers.name as supplier_name,
                    purchaser.full_name as purchased_person,
                    returner.full_name as returned_by')
            ->join('purchase_returns', 'purchase_returns.id = purchase_return_items.purchase_return_id')
            ->join('purchases', 'purchases.id = purchase_returns.purchase_id')
            ->join('items', 'items.id = purchase_return_items.item_id')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->join('users as purchaser', 'purchaser.id = purchases.created_by', 'left')
            ->join('users as returner', 'returner.id = purchase_returns.created_by', 'left')
            ->where('purchase_returns.branch_id', $branchId)
            ->orderBy('purchase_returns.id', 'DESC')
            ->get()->getResultArray();
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
