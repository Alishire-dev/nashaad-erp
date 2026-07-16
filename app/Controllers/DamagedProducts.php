<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\StockAdjustmentModel;

class DamagedProducts extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'   => 'Damaged Products',
            'damaged' => model(StockAdjustmentModel::class)->getByReason($this->branchId, 'damaged'),
            'items'   => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/damaged', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        if ($this->request->getMethod() === 'POST') {
            model(StockAdjustmentModel::class)->record(
                $this->branchId,
                (int) $this->request->getPost('item_id'),
                (float) $this->request->getPost('quantity'),
                'out',
                'damaged',
                (int) $this->currentUser['id'],
                $this->request->getPost('note')
            );

            $this->session->setFlashdata('success', 'Damage recorded and stock updated.');
        }

        return redirect()->to('/damaged-products');
    }
}
