<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table         = 'purchases';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'supplier_id', 'reference_no', 'purchase_date', 'due_date',
        'status', 'subtotal', 'grand_total', 'amount_paid', 'pay_status', 'note', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->select('purchases.*, suppliers.name as supplier_name, users.full_name as created_by_name')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->join('users', 'users.id = purchases.created_by', 'left')
            ->where('purchases.branch_id', $branchId)
            ->orderBy('purchases.id', 'DESC')
            ->findAll();
    }

    public function getWithLines(int $purchaseId): ?array
    {
        $purchase = $this->select('purchases.*, suppliers.name as supplier_name')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->find($purchaseId);

        if (! $purchase) {
            return null;
        }

        $purchase['lines'] = $this->db->table('purchase_items')
            ->select('purchase_items.*, items.name as item_name, units.short_name as unit_short')
            ->join('items', 'items.id = purchase_items.item_id')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->where('purchase_id', $purchaseId)
            ->get()->getResultArray();

        return $purchase;
    }

    /**
     * Creates a purchase with its line items in one transaction, and posts
     * every line as a stock-in movement through StockAdjustmentModel so the
     * audit trail (stock_adjustments) and items.current_stock stay in sync.
     *
     * @param array $lines each: ['item_id','quantity','cost_price','tax_percent','discount_pct']
     */
    public function createWithLines(array $header, array $lines, int $userId): int|false
    {
        $db = db_connect();
        $db->transStart();

        $subtotal = 0.0;
        foreach ($lines as &$line) {
            $qty       = (float) $line['quantity'];
            $cost      = (float) $line['cost_price'];
            $taxPct    = (float) ($line['tax_percent'] ?? 0);
            $discPct   = (float) ($line['discount_pct'] ?? 0);

            $lineSubtotal = $qty * $cost;
            $discountAmt  = $lineSubtotal * ($discPct / 100);
            $taxable      = $lineSubtotal - $discountAmt;
            $taxAmt       = $taxable * ($taxPct / 100);
            $lineTotal    = $taxable + $taxAmt;

            $line['tax_amount']   = round($taxAmt, 2);
            $line['total_amount'] = round($lineTotal, 2);
            $subtotal += $lineTotal;
        }
        unset($line);

        $header['subtotal']    = round($subtotal, 2);
        $header['grand_total'] = round($subtotal, 2); // round-off/expenses can extend this later
        $header['amount_paid'] = $header['grand_total']; // matches the full-cash-payment assumption below
        $header['pay_status']  = 'paid';
        $header['created_by']  = $userId;
        $header['created_at']  = date('Y-m-d H:i:s');

        $purchaseId = $this->insert($header);

        if (! $purchaseId) {
            $db->transRollback();
            return false;
        }

        $stockModel = model(StockAdjustmentModel::class);

        foreach ($lines as $line) {
            $this->db->table('purchase_items')->insert([
                'purchase_id'  => $purchaseId,
                'item_id'      => $line['item_id'],
                'quantity'     => $line['quantity'],
                'cost_price'   => $line['cost_price'],
                'tax_percent'  => $line['tax_percent'] ?? 0,
                'tax_amount'   => $line['tax_amount'],
                'discount_pct' => $line['discount_pct'] ?? 0,
                'total_amount' => $line['total_amount'],
            ]);

            // Stock-in + update the item's purchase_price to the latest cost
            $stockModel->record(
                $header['branch_id'],
                (int) $line['item_id'],
                (float) $line['quantity'],
                'in',
                'purchase',
                $userId,
                'Purchase #' . $purchaseId
            );

            model(ItemModel::class)->update((int) $line['item_id'], [
                'purchase_price' => $line['cost_price'],
            ]);
        }

        // Every purchase posts a Cash-out ledger entry. Simplified on purpose:
        // this assumes full payment via Cash at purchase time — there's no
        // partial-payment/accounts-payable flow yet (the "amount_paid"
        // column exists on purchases but nothing collects it in the form
        // yet). Real AP aging is future work, not silently faked here.
        model(MoneyTransactionModel::class)->post(
            $header['branch_id'],
            $userId,
            $header['purchase_date'],
            'Cash',
            0,
            $header['grand_total'],
            'Purchase #' . $purchaseId . (! empty($header['reference_no']) ? ' (' . $header['reference_no'] . ')' : ''),
            'cash',
            'purchase',
            $purchaseId
        );

        $db->transComplete();

        return $db->transStatus() ? $purchaseId : false;
    }

    /**
     * Daily purchase totals for the last N days — Dashboard bar chart's
     * purchase series, same shape as SaleModel::getDailyTotals().
     */
    public function getDailyTotals(int $branchId, int $days = 7): array
    {
        $rows = $this->select('purchase_date, SUM(grand_total) as total')
            ->where('branch_id', $branchId)
            ->where('purchase_date >=', date('Y-m-d', strtotime("-{$days} days")))
            ->groupBy('purchase_date')
            ->orderBy('purchase_date', 'ASC')
            ->findAll();

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['purchase_date']] = (float) $r['total'];
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $date, 'total' => $byDate[$date] ?? 0.0];
        }

        return $result;
    }
}
