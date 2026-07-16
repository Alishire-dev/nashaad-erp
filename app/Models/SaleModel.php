<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table         = 'sales';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'customer_id', 'invoice_no', 'sale_date', 'status',
        'subtotal', 'discount_pct', 'discount_amt', 'tax_amount', 'grand_total',
        'amount_paid', 'pay_mode', 'created_by',
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
        $db = db_connect();
        $db->transStart();

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

        $header['subtotal']     = round($subtotal, 2);
        $header['discount_amt'] = round($discAmt, 2);
        $header['grand_total']  = round($grandTotal, 2);
        $header['invoice_no']   = $this->nextInvoiceNo($header['branch_id']);
        $header['created_by']   = $userId;
        $header['created_at']   = date('Y-m-d H:i:s');
        $header['status']       = 'completed';

        $saleId = $this->insert($header);

        if (! $saleId) {
            $db->transRollback();
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

        $db->transComplete();

        return $db->transStatus() ? $saleId : false;
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
        return $this->select('sales.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('sales.branch_id', $branchId)
            ->where('sales.status', 'completed')
            ->orderBy('sales.id', 'DESC')
            ->findAll();
    }

    public function getWithLines(int $saleId): ?array
    {
        $sale = $this->select('sales.*, customers.name as customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
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
}
