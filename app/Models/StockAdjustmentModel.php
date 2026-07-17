<?php

namespace App\Models;

use CodeIgniter\Model;

class StockAdjustmentModel extends Model
{
    protected $table         = 'stock_adjustments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'adjustment_date', 'item_id', 'direction', 'quantity', 'unit_cost', 'reason',
        'migration_control_account_id', 'note', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Single entry point for every stock movement in the system.
     * Writes the audit row AND updates items.current_stock atomically.
     *
     * Call this from Purchase, POS, Issued Products, Damaged Products, and
     * the manual Stock Manager screen — never touch ItemModel::adjustStock()
     * directly from a controller, so every movement always has a paper trail.
     */
    public function record(
        int $branchId,
        int $itemId,
        float $qty,
        string $direction,
        string $reason,
        int $userId,
        ?string $note = null,
        ?string $adjustmentDate = null,
        ?int $migrationControlAccountId = null,
        ?float $unitCost = null
    ): bool {
        $this->insert([
            'branch_id'                    => $branchId,
            'adjustment_date'              => $adjustmentDate ?: date('Y-m-d'),
            'item_id'                      => $itemId,
            'direction'                    => $direction,
            'quantity'                     => abs($qty),
            'unit_cost'                    => $unitCost,
            'reason'                       => $reason,
            'migration_control_account_id' => $migrationControlAccountId,
            'note'                         => $note,
            'created_by'                   => $userId,
            'created_at'                   => date('Y-m-d H:i:s'),
        ]);

        return model(ItemModel::class)->adjustStock($itemId, $qty, $direction);
    }

    public function historyForItem(int $itemId): array
    {
        return $this->select('stock_adjustments.*, users.full_name as user_name')
            ->join('users', 'users.id = stock_adjustments.created_by', 'left')
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function recentForBranch(int $branchId, int $limit = 50): array
    {
        return $this->select('stock_adjustments.*, items.name as item_name, users.full_name as user_name,
                migration_control_accounts.name as account_name')
            ->join('items', 'items.id = stock_adjustments.item_id')
            ->join('users', 'users.id = stock_adjustments.created_by', 'left')
            ->join('migration_control_accounts', 'migration_control_accounts.id = stock_adjustments.migration_control_account_id', 'left')
            ->where('stock_adjustments.branch_id', $branchId)
            ->orderBy('stock_adjustments.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Filtered by reason — powers Issued Products and Damaged Products,
     * which are really just this same audit trail viewed through a filter.
     */
    public function getByReason(int $branchId, string $reason): array
    {
        return $this->select('stock_adjustments.*, items.name as item_name, items.item_code,
                units.short_name as unit_short, users.full_name as user_name')
            ->join('items', 'items.id = stock_adjustments.item_id')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->join('users', 'users.id = stock_adjustments.created_by', 'left')
            ->where('stock_adjustments.branch_id', $branchId)
            ->where('stock_adjustments.reason', $reason)
            ->orderBy('stock_adjustments.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Deletes an adjustment row AND reverses its effect on current_stock —
     * otherwise deleting a record that already decremented stock would
     * leave a permanent, invisible stock discrepancy.
     */
    public function deleteAndReverse(int $adjustmentId): bool
    {
        $row = $this->find($adjustmentId);
        if (! $row) {
            return false;
        }

        // Reverse: an 'out' becomes an 'in' and vice versa.
        $reverseDirection = $row['direction'] === 'out' ? 'in' : 'out';
        model(ItemModel::class)->adjustStock((int) $row['item_id'], (float) $row['quantity'], $reverseDirection);

        return (bool) $this->delete($adjustmentId);
    }
}
