<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\UnitModel;
use App\Models\BrandModel;

class Items extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $itemModel = model(ItemModel::class);
        $data = [
            'title' => 'Items List',
            'items' => $itemModel->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $itemModel = model(ItemModel::class);

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'branch_id'           => $this->branchId,
                'category_id'         => $this->request->getPost('category_id') ?: null,
                'brand_id'            => $this->request->getPost('brand_id') ?: null,
                'unit_id'             => $this->request->getPost('unit_id'),
                'tax_category_id'     => $this->request->getPost('tax_category_id') ?: 1,
                'tax_type'            => $this->request->getPost('tax_type') ?: 'inclusive',
                'name'                => $this->request->getPost('name'),
                'sku'                 => $this->request->getPost('sku'),
                'purpose'             => $this->request->getPost('purpose') ?: 'for_sale',
                'manage_stock'        => $this->request->getPost('manage_stock') === 'yes' ? 1 : 0,
                'allow_negative_sale' => $this->request->getPost('allow_negative_sale') === 'yes' ? 1 : 0,
                'alert_qty'           => $this->request->getPost('alert_qty') ?: 0,
                'purchase_price'      => $this->request->getPost('purchase_price') ?: 0,
                'sales_price'         => $this->request->getPost('sales_price') ?: 0,
                'wholesale_price'     => $this->request->getPost('wholesale_price') ?: 0,
                'minimum_price'       => $this->request->getPost('minimum_price') ?: 0,
                'profit_margin'       => $this->request->getPost('profit_margin') ?: 0,
                'current_stock'       => $this->request->getPost('opening_stock') ?: 0,
                'description'         => $this->request->getPost('description'),
            ];

            if (! $itemModel->insert($data)) {
                $data['title']      = 'New Item';
                $data['validation'] = $itemModel->errors();
                $data['categories'] = model(CategoryModel::class)->getForBranch($this->branchId);
                $data['units']      = model(UnitModel::class)->getForBranch($this->branchId);
                $data['brands']     = model(BrandModel::class)->getForBranch($this->branchId);

                return view('layout/header', $data)
                    . view('items/add', $data)
                    . view('layout/footer');
            }

            $this->session->setFlashdata('success', 'Item created successfully.');
            return redirect()->to('/items/list');
        }

        $data = [
            'title'      => 'New Item',
            'categories' => model(CategoryModel::class)->getForBranch($this->branchId),
            'units'      => model(UnitModel::class)->getForBranch($this->branchId),
            'brands'     => model(BrandModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/add', $data)
            . view('layout/footer');
    }
}
