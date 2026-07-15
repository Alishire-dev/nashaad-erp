<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemConversionModel extends Model
{
    protected $table         = 'item_conversions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'parent_item_id', 'child_item_id', 'conversion_rate', 'description', 'created_by'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Creates the child item in the catalog (inheriting the parent's category/tax
     * settings) and records the conversion recipe linking it back to its parent.
     */
    public function createConversion(array $parentItem, array $data, int $userId): int|string|false
    {
        $itemModel = model(ItemModel::class);

        $childId = $itemModel->createForBranch([
            'branch_id'       => $parentItem['branch_id'],
            'category_id'     => $parentItem['category_id'],
            'brand_id'        => $parentItem['brand_id'],
            'unit_id'         => $data['unit_id'],
            'tax_category_id' => $parentItem['tax_category_id'],
            'tax_type'        => $parentItem['tax_type'],
            'name'            => $data['name'],
            'purpose'         => 'for_sale',
            'manage_stock'    => 1,
            'purchase_price'  => 0,
            'sales_price'     => $data['sales_price'],
            'current_stock'   => 0,
        ]);

        if (! $childId) {
            return false;
        }

        return $this->insert([
            'branch_id'       => $parentItem['branch_id'],
            'parent_item_id'  => $parentItem['id'],
            'child_item_id'   => $childId,
            'conversion_rate' => $data['conversion_rate'],
            'description'     => $data['description'] ?? null,
            'created_by'      => $userId,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
