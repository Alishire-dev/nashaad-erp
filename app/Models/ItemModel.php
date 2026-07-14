<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table         = 'items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'category_id', 'brand_id', 'unit_id', 'tax_category_id', 'tax_type',
        'name', 'sku', 'barcode', 'image', 'purpose', 'manage_stock', 'allow_negative_sale',
        'alert_qty', 'purchase_price', 'sales_price', 'wholesale_price', 'minimum_price',
        'profit_margin', 'sales_commission', 'expiry_date', 'current_stock', 'description', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'            => 'required|min_length[2]|max_length[150]',
        'unit_id'         => 'required|is_natural_no_zero',
        'purchase_price'  => 'required|decimal',
        'sales_price'     => 'required|decimal',
    ];

    public function getForBranch(int $branchId): array
    {
        return $this->select('items.*, categories.name as category_name, units.short_name as unit_short')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->where('items.branch_id', $branchId)
            ->orderBy('items.name', 'ASC')
            ->findAll();
    }

    public function lowStock(int $branchId): array
    {
        return $this->where('branch_id', $branchId)
            ->where('manage_stock', 1)
            ->where('current_stock <= alert_qty', null, false)
            ->findAll();
    }

    /**
     * Adjust stock atomically (used by Purchase + POS + Issued/Damaged).
     * $direction: 'in' (purchase/return) or 'out' (sale/issue/damage)
     */
    public function adjustStock(int $itemId, float $qty, string $direction = 'in'): bool
    {
        $qty = abs($qty);
        $sign = $direction === 'in' ? '+' : '-';

        $this->db->table($this->table)
            ->where('id', $itemId)
            ->set('current_stock', "current_stock {$sign} {$qty}", false)
            ->update();

        return true;
    }
}
