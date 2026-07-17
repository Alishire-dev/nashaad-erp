<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table         = 'sales';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'customer_id', 'sales_person_id', 'invoice_no', 'sale_date', 'due_date',
        'lpo_number', 'note', 'status', 'subtotal', 'discount_pct', 'discount_type', 'discount_amt',
        'tax_amount', 'grand_total', 'amount_paid', 'pay_status', 'pay_mode', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * INV/YYYY/NNNNN, sequential per branch per year — same pattern as the
     * existing RCP/YYYY/NNNNN receipt design, just for POS invoices.
     */
    private function nextInvoiceNo(int $branchId): string
    {
        $year  = date('Y');
        $count = $this->where('branch_id', $branchId)
            ->where("invoice_no LIKE 'INV/{$year}/%'", null, false)
            ->countAllResults();

        return sprintf('INV/%s/%05d', $year, $count + 1);
    }

    /**
     * Finalizes a sale: computes totals, inserts the sale + lines, and
     * deducts stock for every manage_stock=1 line via StockAdjustmentModel
     * (never touches ItemModel::adjustStock directly — same rule as
     * Purchase). Items with manage_stock=0 are sold without any stock
     * movement at all, matching how Items List already treats them.
     *
     * @param array $lines each: ['item_id','quantity','unit_price','discount_pct']
     */
    public function checkout(array $header, array $lines, int $userId): int|false
    {
        $this->db->transStart();

        $subtotal = 0.0;
        foreach ($lines as &$line) {
            $qty   = (float) $line['quantity'];
            $price = (float) $line['unit_price'];
            $discPct = (float) ($line['discount_pct'] ?? 0);

            $lineSubtotal = $qty * $price;
            $discAmt      = $lineSubtotal * ($discPct / 100);
            $lineTotal    = $lineSubtotal - $discAmt;

            $line['total_amount'] = round($lineTotal, 2);
            $subtotal += $lineTotal;
        }
        unset($line);

        $overallDiscPct = (float) ($header['discount_pct'] ?? 0);
        $discAmt        = $subtotal * ($overallDiscPct / 100);
        $grandTotal     = $subtotal - $discAmt;
        $amountPaid     = (float) ($header['amount_paid'] ?? $grandTotal);

        $header['subtotal']     = round($subtotal, 2);
        $header['discount_amt'] = round($discAmt, 2);
        $header['grand_total']  = round($grandTotal, 2);
        $header['amount_paid']  = round($amountPaid, 2);
        $header['pay_status']   = $amountPaid <= 0 ? 'unpaid' : ($amountPaid >= $grandTotal ? 'paid' : 'partial');
        $header['invoice_no']   = $this->nextInvoiceNo($header['branch_id']);
        $header['created_by']   = $userId;
        $header['created_at']   = date('Y-m-d H:i:s');
        $header['status']       = 'completed';

        $saleId = $this->insert($header);

        if (! $saleId) {
            $this->db->transRollback();
            return false;
        }

        $itemModel  = model(ItemModel::class);
        $stockModel = model(StockAdjustmentModel::class);

        foreach ($lines as $line) {
            $this->db->table('sale_items')->insert([
                'sale_id'      => $saleId,
                'item_id'      => $line['item_id'],
                'quantity'     => $line['quantity'],
                'unit_price'   => $line['unit_price'],
                'discount_pct' => $line['discount_pct'] ?? 0,
                'tax_amount'   => 0,
                'total_amount' => $line['total_amount'],
            ]);

            $item = $itemModel->find((int) $line['item_id']);
            if ($item && (int) $item['manage_stock'] === 1) {
                $stockModel->record(
                    $header['branch_id'],
                    (int) $line['item_id'],
                    (float) $line['quantity'],
                    'out',
                    'sale',
                    $userId,
                    'Sale ' . $header['invoice_no']
                );
            }
        }

        // Every sale posts a cash-in ledger entry against the account
        // matching how it was paid — cash/mpesa/card map to Cash/EVC/Bank
        // respectively (Bank is seeded inactive by default; still postable,
        // just not offered as a NEW selection elsewhere until activated).
        $accountByPayMode = ['cash' => 'Cash', 'mpesa' => 'EVC', 'card' => 'Bank'];
        $account = $accountByPayMode[$header['pay_mode'] ?? 'cash'] ?? 'Cash';

        model(MoneyTransactionModel::class)->post(
            $header['branch_id'],
            $userId,
            $header['sale_date'],
            $account,
            (float) ($header['amount_paid'] ?? 0),
            0,
            'Sale ' . $header['invoice_no'],
            $header['pay_mode'] ?? 'cash',
            'sale',
            $saleId,
            $header['invoice_no']
        );

        if ($header['amount_paid'] > 0) {
            $this->db->table('sale_payments')->insert([
                'sale_id'      => $saleId,
                'payment_date' => $header['sale_date'],
                'amount'       => $header['amount_paid'],
                'payment_type' => $header['pay_mode'] ?? 'cash',
                'created_by'   => $userId,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $saleId : false;
    }

    /**
     * Holds a cart without touching stock at all — nothing is final until
     * it's recalled and actually checked out.
     */
    public function holdSale(array $header, array $lines, int $userId): int|false
    {
        $header['status']     = 'hold';
        $header['invoice_no'] = 'HOLD-' . time();
        $header['created_by'] = $userId;
        $header['created_at'] = date('Y-m-d H:i:s');
        $header['subtotal']    = 0;
        $header['grand_total'] = 0;

        $saleId = $this->insert($header);
        if (! $saleId) {
            return false;
        }

        foreach ($lines as $line) {
            $this->db->table('sale_items')->insert([
                'sale_id'      => $saleId,
                'item_id'      => $line['item_id'],
                'quantity'     => $line['quantity'],
                'unit_price'   => $line['unit_price'],
                'discount_pct' => $line['discount_pct'] ?? 0,
                'tax_amount'   => 0,
                'total_amount' => (float) $line['quantity'] * (float) $line['unit_price'],
            ]);
        }

        return $saleId;
    }

    public function getHeldSales(int $branchId): array
    {
        return $this->select('sales.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', 'hold')
            ->orderBy('sales.created_at', 'DESC')
            ->findAll();
    }

    public function getHeldSaleWithLines(int $saleId): ?array
    {
        $sale = $this->find($saleId);
        if (! $sale) {
            return null;
        }

        $sale['lines'] = $this->db->table('sale_items')
            ->select('sale_items.*, items.name as item_name')
            ->join('items', 'items.id = sale_items.item_id')
            ->where('sale_id', $saleId)
            ->get()->getResultArray();

        return $sale;
    }

    /**
     * Removes a held sale (called after recalling it back into the cart —
     * the held record's job is done once the cashier is editing it again).
     */
    public function deleteHeldSale(int $saleId): bool
    {
        $this->db->table('sale_items')->where('sale_id', $saleId)->delete();
        return $this->delete($saleId) !== false;
    }

    public function getForBranch(int $branchId): array
    {
        return $this->select('sales.*, customers.name as customer_name, salesperson.full_name as sales_person_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users as salesperson', 'salesperson.id = sales.sales_person_id', 'left')
            ->where('sales.branch_id', $branchId)
            ->whereNotIn('sales.status', ['hold', 'cancelled'])
            ->orderBy('sales.id', 'DESC')
            ->findAll();
    }

    public function getCancelledSales(int $branchId): array
    {
        return $this->select('sales.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', 'cancelled')
            ->orderBy('sales.id', 'DESC')
            ->findAll();
    }

    public function getWithLines(int $saleId): ?array
    {
        $sale = $this->select('sales.*, customers.name as customer_name, customers.phone as customer_phone,
                customers.email as customer_email, customers.address as customer_address,
                salesperson.full_name as sales_person_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users as salesperson', 'salesperson.id = sales.sales_person_id', 'left')
            ->find($saleId);

        if (! $sale) {
            return null;
        }

        $sale['lines'] = $this->db->table('sale_items')
            ->select('sale_items.*, items.name as item_name, units.short_name as unit_short')
            ->join('items', 'items.id = sale_items.item_id')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->where('sale_id', $saleId)
            ->get()->getResultArray();

        return $sale;
    }

    /**
     * Change Sales Details modal — updates the header fields it exposes.
     */
    public function updateDetails(int $saleId, array $fields): bool
    {
        return (bool) $this->update($saleId, array_intersect_key($fields, array_flip([
            'sales_person_id', 'sale_date', 'due_date', 'lpo_number', 'note',
        ])));
    }

    /**
     * Apply Discount modal — recalculates grand_total/discount_amt from a
     * new discount type+value, working off the sale's own subtotal (line
     * items aren't touched, only the header-level discount).
     */
    public function applyDiscount(int $saleId, string $discountType, float $discountValue): bool
    {
        $sale = $this->find($saleId);
        if (! $sale) {
            return false;
        }

        $subtotal = (float) $sale['subtotal'];
        $discAmt  = $discountType === 'fixed' ? $discountValue : $subtotal * ($discountValue / 100);
        $discAmt  = min($discAmt, $subtotal); // never let discount exceed the subtotal
        $grandTotal = $subtotal - $discAmt;

        return $this->update($saleId, [
            'discount_type' => $discountType,
            'discount_pct'  => $discountValue,
            'discount_amt'  => round($discAmt, 2),
            'grand_total'   => round($grandTotal, 2),
        ]) !== false;
    }

    /**
     * Cancel Sale — reverses stock for every manage_stock line (same
     * "restore what a completed action changed" pattern as
     * IssuedProducts/DamagedProducts delete), then marks the sale
     * cancelled. Does not delete the sale — it stays visible with its
     * real history, just flagged.
     */
    public function cancelSale(int $saleId, int $userId): bool
    {
        $sale = $this->getWithLines($saleId);
        if (! $sale || $sale['status'] === 'cancelled') {
            return false;
        }

        $this->db->transStart();

        // Real bug caught in testing: a line's ORIGINAL quantity isn't
        // necessarily what's still "out" — some of it may have already
        // gone back to stock via a Sales Return on this same sale. Only
        // restore what's left, or cancelling after a partial return
        // double-refunds the portion that was already returned.
        $alreadyReturned = $this->db->table('sale_return_items')
            ->select('item_id, SUM(quantity) as qty')
            ->join('sale_returns', 'sale_returns.id = sale_return_items.sale_return_id')
            ->where('sale_returns.sale_id', $saleId)
            ->where('sale_return_items.good_condition', 'yes')
            ->groupBy('item_id')
            ->get()->getResultArray();

        $returnedByItem = [];
        foreach ($alreadyReturned as $row) {
            $returnedByItem[(int) $row['item_id']] = (float) $row['qty'];
        }

        $itemModel  = model(ItemModel::class);
        $stockModel = model(StockAdjustmentModel::class);

        foreach ($sale['lines'] as $line) {
            $item = $itemModel->find((int) $line['item_id']);
            if (! $item || (int) $item['manage_stock'] !== 1) {
                continue;
            }

            $qtyToRestore = (float) $line['quantity'] - ($returnedByItem[(int) $line['item_id']] ?? 0);
            if ($qtyToRestore <= 0) {
                continue;
            }

            $stockModel->record(
                $sale['branch_id'],
                (int) $line['item_id'],
                $qtyToRestore,
                'in',
                'sale_return',
                $userId,
                'Cancelled sale ' . $sale['invoice_no']
            );
        }

        $this->update($saleId, ['status' => 'cancelled', 'pay_status' => 'cancelled']);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Daily sales totals for the last N days — used for the Dashboard bar
     * chart. Only counts completed sales (held/cancelled don't count as
     * real revenue).
     */
    public function getDailyTotals(int $branchId, int $days = 7): array
    {
        $rows = $this->select('sale_date, SUM(grand_total) as total')
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->where('sale_date >=', date('Y-m-d', strtotime("-{$days} days")))
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'ASC')
            ->findAll();

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['sale_date']] = (float) $r['total'];
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $date, 'total' => $byDate[$date] ?? 0.0];
        }

        return $result;
    }

    /**
     * Top 5 fast-moving items by quantity sold, all-time for this branch.
     */
    public function getTopMovers(int $branchId, int $limit = 5): array
    {
        return $this->db->table('sale_items')
            ->select('items.name as item_name, SUM(sale_items.quantity) as total_qty')
            ->join('sales', 'sales.id = sale_items.sale_id')
            ->join('items', 'items.id = sale_items.item_id')
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', 'completed')
            ->groupBy('sale_items.item_id')
            ->orderBy('total_qty', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /**
     * Pending sales — completed sales where amount_paid is less than
     * grand_total (matches the original's "Accounts Receivable" concept).
     */
    public function getPendingSales(int $branchId): array
    {
        return $this->select('sales.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', 'completed')
            ->where('sales.amount_paid <', 'sales.grand_total', false)
            ->orderBy('sales.id', 'DESC')
            ->findAll();
    }

    /**
     * Today's Sales Summary card data.
     */
    public function getTodaySummary(int $branchId): array
    {
        $today = date('Y-m-d');

        $completed = $this->where('branch_id', $branchId)
            ->where('sale_date', $today)
            ->where('status', 'completed')
            ->findAll();

        $totalSales   = array_sum(array_column($completed, 'grand_total'));
        $paidSales    = array_sum(array_map(static fn ($s) => min((float) $s['amount_paid'], (float) $s['grand_total']), $completed));
        $pendingSales = $totalSales - $paidSales;

        $cancelledTotal = (float) ($this->where('branch_id', $branchId)
            ->where('sale_date', $today)
            ->where('status', 'cancelled')
            ->selectSum('grand_total')
            ->first()['grand_total'] ?? 0);

        return [
            'total_sales'     => $totalSales,
            'paid_sales'      => $paidSales,
            'pending_sales'   => $pendingSales,
            'cancelled_sales' => $cancelledTotal,
        ];
    }
}
