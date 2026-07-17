<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table         = 'items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'item_code', 'category_id', 'brand_id', 'unit_id', 'tax_category_id', 'tax_type',
        'name', 'sku', 'barcode', 'image', 'purpose', 'order_item', 'manage_stock', 'allow_negative_sale',
        'alert_qty', 'purchase_price', 'sales_price', 'wholesale_price', 'minimum_price',
        'profit_margin', 'price_change_all_branches', 'sales_commission', 'expiry_date', 'current_stock',
        'description', 'status',
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
        return $this->select('items.*, categories.name as category_name, units.short_name as unit_short,
                tax_categories.rate as tax_rate, brands.name as brand_name')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->join('tax_categories', 'tax_categories.id = items.tax_category_id', 'left')
            ->join('brands', 'brands.id = items.brand_id', 'left')
            ->where('items.branch_id', $branchId)
            ->orderBy('items.name', 'ASC')
            ->findAll();
    }

    /**
     * Creates a new item, auto-generating a sequential ITM0001-style code
     * per branch — separate from the user-editable SKU field.
     */
    public function createForBranch(array $data): int|string|false
    {
        $count = $this->where('branch_id', $data['branch_id'])->countAllResults();
        $data['item_code'] = 'ITM' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);

        return $this->insert($data);
    }

    /**
     * Full detail for the Item Profile page — one item with category/unit/brand/tax names.
     */
    public function getProfileData(int $itemId): ?array
    {
        return $this->select('items.*, categories.name as category_name, units.name as unit_name,
                brands.name as brand_name, tax_categories.name as tax_category_name')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->join('units', 'units.id = items.unit_id', 'left')
            ->join('brands', 'brands.id = items.brand_id', 'left')
            ->join('tax_categories', 'tax_categories.id = items.tax_category_id', 'left')
            ->find($itemId);
    }

    /**
     * "Batches" for the Item Profile page — one row per purchase this item has
     * appeared in. NOTE: Stocked Qty is real (from purchase_items), but this
     * system tracks stock as one aggregate current_stock per item, not
     * per-batch remaining balances (that would need a FIFO-consumption
     * redesign of how sales/purchases touch stock). Balance Qty here is
     * therefore intentionally left as "—" rather than a fabricated number —
     * see README for what true batch tracking would require.
     */
    public function getPurchaseBatches(int $itemId): array
    {
        return $this->db->table('purchase_items')
            ->select('purchase_items.*, purchases.purchase_date, purchases.branch_id, branches.name as branch_name')
            ->join('purchases', 'purchases.id = purchase_items.purchase_id')
            ->join('branches', 'branches.id = purchases.branch_id', 'left')
            ->where('purchase_items.item_id', $itemId)
            ->orderBy('purchases.purchase_date', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Child items created via Item Conversion, for the Item Profile "Child Item" table.
     */
    public function getConversionChildren(int $itemId): array
    {
        return $this->db->table('item_conversions')
            ->select('item_conversions.*, items.name as child_name, items.purchase_price as child_purchase_price,
                    items.sales_price as child_sales_price, items.current_stock as child_stock,
                    items.alert_qty as child_alert_qty, items.status as child_status, branches.name as branch_name')
            ->join('items', 'items.id = item_conversions.child_item_id')
            ->join('branches', 'branches.id = item_conversions.branch_id', 'left')
            ->where('item_conversions.parent_item_id', $itemId)
            ->get()->getResultArray();
    }

    public function lowStock(int $branchId): array
    {
        return $this->select('items.*, categories.name as category_name, brands.name as brand_name')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->join('brands', 'brands.id = items.brand_id', 'left')
            ->where('items.branch_id', $branchId)
            ->where('items.manage_stock', 1)
            ->where('items.current_stock <= items.alert_qty', null, false)
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
