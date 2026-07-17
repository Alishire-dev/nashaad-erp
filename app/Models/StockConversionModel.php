<?php

namespace App\Models;

use CodeIgniter\Model;

class StockConversionModel extends Model
{
    protected $table         = 'stock_conversions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'parent_item_id', 'qty_converted', 'child_item_id',
        'qty_produced', 'description', 'created_by',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->select('stock_conversions.*, parent.name as parent_name,
                child.name as child_name, users.full_name as user_name')
            ->join('items as parent', 'parent.id = stock_conversions.parent_item_id')
            ->join('items as child', 'child.id = stock_conversions.child_item_id')
            ->join('users', 'users.id = stock_conversions.created_by', 'left')
            ->where('stock_conversions.branch_id', $branchId)
            ->orderBy('stock_conversions.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Executes a real conversion: decrements the parent item's stock,
     * increments the child item's stock, both through
     * StockAdjustmentModel (never ItemModel::adjustStock directly — same
     * rule as everywhere else), then logs the event here for the report.
     * This is the EXECUTION step — distinct from ItemConversionModel,
     * which only defines the parent/child RECIPE relationship.
     */
    public function convert(
        int $branchId,
        int $parentItemId,
        float $qtyConverted,
        int $childItemId,
        float $qtyProduced,
        ?string $description,
        int $userId
    ): int|false {
        $this->db->transStart();

        $stockModel = model(StockAdjustmentModel::class);

        $stockModel->record($branchId, $parentItemId, $qtyConverted, 'out', 'conversion', $userId, $description);
        $stockModel->record($branchId, $childItemId, $qtyProduced, 'in', 'conversion', $userId, $description);

        $id = $this->insert([
            'branch_id'      => $branchId,
            'parent_item_id' => $parentItemId,
            'qty_converted'  => $qtyConverted,
            'child_item_id'  => $childItemId,
            'qty_produced'   => $qtyProduced,
            'description'    => $description,
            'created_by'     => $userId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        return $this->db->transStatus() ? $id : false;
    }
}
