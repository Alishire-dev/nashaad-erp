<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemPriceLogModel extends Model
{
    protected $table         = 'item_price_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['item_id', 'old_price', 'new_price', 'changed_by', 'changed_at'];

    protected $useTimestamps = false;
    protected $createdField  = 'changed_at';

    public function historyForItem(int $itemId): array
    {
        return $this->select('item_price_log.*, users.full_name as user_name')
            ->join('users', 'users.id = item_price_log.changed_by', 'left')
            ->where('item_id', $itemId)
            ->orderBy('changed_at', 'DESC')
            ->findAll();
    }

    public function recentForBranch(int $branchId, int $limit = 100): array
    {
        return $this->select('item_price_log.*, items.name as item_name, users.full_name as user_name')
            ->join('items', 'items.id = item_price_log.item_id')
            ->join('users', 'users.id = item_price_log.changed_by', 'left')
            ->where('items.branch_id', $branchId)
            ->orderBy('item_price_log.changed_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
