<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleReturnModel extends Model
{
    protected $table         = 'sale_returns';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'sale_id', 'return_date', 'receipt_ref', 'narrative', 'grand_total', 'created_by'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->db->table('sale_return_items')
            ->select('sale_return_items.*, sale_returns.return_date, sale_returns.narrative, sale_returns.receipt_ref,
                    sales.invoice_no, items.name as item_name, customers.name as customer_name,
                    returner.full_name as returned_by')
            ->join('sale_returns', 'sale_returns.id = sale_return_items.sale_return_id')
            ->join('sales', 'sales.id = sale_returns.sale_id')
            ->join('items', 'items.id = sale_return_items.item_id')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users as returner', 'returner.id = sale_returns.created_by', 'left')
            ->where('sale_returns.branch_id', $branchId)
            ->orderBy('sale_returns.id', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Real return: items go back into stock via StockAdjustmentModel
     * (reason='sale_return', direction='in') — the warning in the original
     * UI ("items are returned back to stock upon submission") is enforced
     * here, not just displayed as text.
     *
     * @param array $lines each: ['item_id','quantity','unit_price','good_condition']
     */
    public function createWithLines(array $header, array $lines, int $userId): int|false
    {
        $this->db->transStart();

        $grandTotal = 0.0;
        foreach ($lines as &$line) {
            $line['total_amount'] = round((float) $line['quantity'] * (float) $line['unit_price'], 2);
            $grandTotal += $line['total_amount'];
        }
        unset($line);

        $header['grand_total'] = round($grandTotal, 2);
        $header['created_by']  = $userId;
        $header['created_at']  = date('Y-m-d H:i:s');

        $returnId = $this->insert($header);
        if (! $returnId) {
            $this->db->transRollback();
            return false;
        }

        $stockModel = model(StockAdjustmentModel::class);

        foreach ($lines as $line) {
            $this->db->table('sale_return_items')->insert([
                'sale_return_id' => $returnId,
                'item_id'        => $line['item_id'],
                'quantity'       => $line['quantity'],
                'good_condition' => $line['good_condition'] ?? 'yes',
                'unit_price'     => $line['unit_price'],
                'total_amount'   => $line['total_amount'],
            ]);

            // Only good-condition returns go back to sellable stock —
            // damaged returns are logged but don't inflate stock with
            // items that can't actually be resold.
            if (($line['good_condition'] ?? 'yes') === 'yes') {
                $stockModel->record(
                    $header['branch_id'],
                    (int) $line['item_id'],
                    (float) $line['quantity'],
                    'in',
                    'sale_return',
                    $userId,
                    'Sale Return #' . $returnId
                );
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $returnId : false;
    }
}
