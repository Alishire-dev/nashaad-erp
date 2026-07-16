<?php

namespace App\Models;

use CodeIgniter\Model;

class MoneyTransactionModel extends Model
{
    protected $table         = 'money_transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'trans_by', 'payment_date', 'pay_mode', 'description',
        'chart_of_account_id', 'voucher_no', 'amount_in', 'amount_out',
        'reference_type', 'reference_id',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Single entry point for posting a money transaction — Purchase and
     * Sale checkout both call this rather than inserting rows directly,
     * same "one model owns the audit trail" rule as StockAdjustmentModel.
     * Looks the GL account up by name (from the seeded chart of accounts)
     * rather than requiring callers to know IDs.
     */
    public function post(
        int $branchId,
        int $userId,
        string $date,
        string $accountName,
        float $amountIn,
        float $amountOut,
        string $description,
        ?string $payMode = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $voucherNo = null
    ): bool {
        $account = model(ChartOfAccountModel::class)->findByName($branchId, $accountName);

        if (! $account) {
            // Don't silently fail a whole checkout over a missing GL account —
            // log it and skip posting rather than blocking the sale/purchase.
            log_message('warning', "MoneyTransactionModel::post — chart of account '{$accountName}' not found for branch {$branchId}, skipping ledger post.");
            return false;
        }

        return (bool) $this->insert([
            'branch_id'           => $branchId,
            'trans_by'            => $userId,
            'payment_date'        => $date,
            'pay_mode'            => $payMode,
            'description'         => $description,
            'chart_of_account_id' => $account['id'],
            'voucher_no'          => $voucherNo,
            'amount_in'           => $amountIn,
            'amount_out'          => $amountOut,
            'reference_type'      => $referenceType,
            'reference_id'        => $referenceId,
            'created_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    public function getForBranch(int $branchId, int $limit = 200): array
    {
        return $this->select('money_transactions.*, chart_of_accounts.account_name, users.full_name as trans_by_name')
            ->join('chart_of_accounts', 'chart_of_accounts.id = money_transactions.chart_of_account_id', 'left')
            ->join('users', 'users.id = money_transactions.trans_by', 'left')
            ->where('money_transactions.branch_id', $branchId)
            ->orderBy('money_transactions.payment_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
