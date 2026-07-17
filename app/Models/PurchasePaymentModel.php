<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchasePaymentModel extends Model
{
    protected $table         = 'purchase_payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'purchase_id', 'payment_date', 'amount', 'payment_type',
        'voucher_no', 'payment_note', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForPurchase(int $purchaseId): array
    {
        return $this->select('purchase_payments.*, users.full_name as created_by_name')
            ->join('users', 'users.id = purchase_payments.created_by', 'left')
            ->where('purchase_id', $purchaseId)
            ->orderBy('payment_date', 'ASC')
            ->findAll();
    }

    /**
     * Records a payment AND keeps purchases.amount_paid/pay_status in sync
     * — the single entry point, same rule as StockAdjustmentModel::record()
     * and MoneyTransactionModel::post(). Never insert into
     * purchase_payments directly from a controller.
     */
    public function addPayment(
        int $purchaseId,
        string $date,
        float $amount,
        string $paymentType,
        ?string $voucherNo,
        ?string $note,
        int $userId
    ): int|false {
        $this->db->transStart();

        $id = $this->insert([
            'purchase_id'  => $purchaseId,
            'payment_date' => $date,
            'amount'       => $amount,
            'payment_type' => $paymentType,
            'voucher_no'   => $voucherNo,
            'payment_note' => $note,
            'created_by'   => $userId,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->recalculatePurchase($purchaseId);

        $this->db->transComplete();

        return $this->db->transStatus() ? $id : false;
    }

    public function deletePayment(int $paymentId): bool
    {
        $row = $this->find($paymentId);
        if (! $row) {
            return false;
        }

        $this->db->transStart();
        $this->delete($paymentId);
        $this->recalculatePurchase((int) $row['purchase_id']);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Recomputes purchases.amount_paid from the real payment rows (source
     * of truth), and derives pay_status from it — so the two can never
     * silently drift apart.
     */
    private function recalculatePurchase(int $purchaseId): void
    {
        $total = (float) ($this->where('purchase_id', $purchaseId)->selectSum('amount')->first()['amount'] ?? 0);

        $purchaseModel = model(PurchaseModel::class);
        $purchase = $purchaseModel->find($purchaseId);
        if (! $purchase) {
            return;
        }

        $grandTotal = (float) $purchase['grand_total'];
        $payStatus  = 'unpaid';
        if ($purchase['pay_status'] === 'cancelled' || $purchase['pay_status'] === 'requisition') {
            $payStatus = $purchase['pay_status']; // don't override these special states
        } elseif ($total <= 0) {
            $payStatus = 'unpaid';
        } elseif ($total >= $grandTotal) {
            $payStatus = 'paid';
        } else {
            $payStatus = 'partial';
        }

        $purchaseModel->update($purchaseId, ['amount_paid' => $total, 'pay_status' => $payStatus]);
    }
}
