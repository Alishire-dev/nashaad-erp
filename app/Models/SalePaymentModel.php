<?php

namespace App\Models;

use CodeIgniter\Model;

class SalePaymentModel extends Model
{
    protected $table         = 'sale_payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['sale_id', 'payment_date', 'amount', 'payment_type', 'payment_note', 'created_by'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForSale(int $saleId): array
    {
        return $this->select('sale_payments.*, users.full_name as created_by_name')
            ->join('users', 'users.id = sale_payments.created_by', 'left')
            ->where('sale_id', $saleId)
            ->orderBy('payment_date', 'ASC')
            ->findAll();
    }

    /**
     * Same "single entry point keeps everything in sync" rule as
     * PurchasePaymentModel::addPayment().
     */
    public function addPayment(int $saleId, string $date, float $amount, string $paymentType, ?string $note, int $userId): int|false
    {
        $this->db->transStart();

        $id = $this->insert([
            'sale_id'      => $saleId,
            'payment_date' => $date,
            'amount'       => $amount,
            'payment_type' => $paymentType,
            'payment_note' => $note,
            'created_by'   => $userId,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->recalculateSale($saleId);
        $this->db->transComplete();

        return $this->db->transStatus() ? $id : false;
    }

    /**
     * Bulk version for the repeatable-payment-row UI — one submission can
     * record several rows (e.g. part cash, part M-Pesa) in a single trip,
     * recalculating the sale's amount_paid/pay_status once at the end
     * rather than once per row.
     *
     * @param array $rows each: ['date','amount','payment_type','note']
     */
    public function addPayments(int $saleId, array $rows, int $userId): bool
    {
        $this->db->transStart();

        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            if ($amount <= 0) {
                continue; // skip blank/zero rows rather than rejecting the whole batch
            }
            $this->insert([
                'sale_id'      => $saleId,
                'payment_date' => $row['date'] ?? date('Y-m-d'),
                'amount'       => $amount,
                'payment_type' => $row['payment_type'] ?? 'cash',
                'payment_note' => $row['note'] ?? null,
                'created_by'   => $userId,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $this->recalculateSale($saleId);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deletePayment(int $paymentId): bool
    {
        $row = $this->find($paymentId);
        if (! $row) {
            return false;
        }

        $this->db->transStart();
        $this->delete($paymentId);
        $this->recalculateSale((int) $row['sale_id']);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    private function recalculateSale(int $saleId): void
    {
        $total = (float) ($this->where('sale_id', $saleId)->selectSum('amount')->first()['amount'] ?? 0);

        $saleModel = model(SaleModel::class);
        $sale = $saleModel->find($saleId);
        if (! $sale) {
            return;
        }

        $grandTotal = (float) $sale['grand_total'];
        if ($sale['pay_status'] === 'cancelled') {
            $payStatus = 'cancelled'; // don't override
        } elseif ($total <= 0) {
            $payStatus = 'unpaid';
        } elseif ($total >= $grandTotal) {
            $payStatus = 'paid';
        } else {
            $payStatus = 'partial';
        }

        $saleModel->update($saleId, ['amount_paid' => $total, 'pay_status' => $payStatus]);
    }
}
