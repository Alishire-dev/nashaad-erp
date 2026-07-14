<?php

namespace App\Models;

use CodeIgniter\Model;

class StockAdjustmentModel extends Model
{
    protected $table         = 'stock_adjustments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'item_id', 'direction', 'quantity', 'reason', 'note', 'created_by',
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
        ?string $note = null
    ): bool {
        $this->insert([
            'branch_id'  => $branchId,
            'item_id'    => $itemId,
            'direction'  => $direction,
            'quantity'   => abs($qty),
            'reason'     => $reason,
            'note'       => $note,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
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
        return $this->select('stock_adjustments.*, items.name as item_name, users.full_name as user_name')
            ->join('items', 'items.id = stock_adjustments.item_id')
            ->join('users', 'users.id = stock_adjustments.created_by', 'left')
            ->where('stock_adjustments.branch_id', $branchId)
            ->orderBy('stock_adjustments.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
