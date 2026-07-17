<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\StockConversionModel;

class StockConversion extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'       => 'Stock Conversion Report',
            'conversions' => model(StockConversionModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/stock_conversion', $data)
            . view('layout/footer');
    }

    public function addForm()
    {
        $this->requirePermission('items', 'add');

        $data = [
            'title' => 'Convert Stock',
            'items' => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/stock_conversion_add', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $ok = model(StockConversionModel::class)->convert(
            $this->branchId,
            (int) $this->request->getPost('parent_item_id'),
            (float) $this->request->getPost('qty_converted'),
            (int) $this->request->getPost('child_item_id'),
            (float) $this->request->getPost('qty_produced'),
            $this->request->getPost('description'),
            (int) $this->currentUser['id']
        );

        $this->session->setFlashdata($ok ? 'success' : 'error', $ok ? 'Stock converted successfully.' : 'Conversion failed.');
        return redirect()->to('/stock-conversion');
    }
}
