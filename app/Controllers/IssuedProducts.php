<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\StockAdjustmentModel;

class IssuedProducts extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'  => 'Issued Products',
            'issued' => model(StockAdjustmentModel::class)->getByReason($this->branchId, 'issued'),
            'items'  => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/issued', $data)
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
                'issued',
                (int) $this->currentUser['id'],
                $this->request->getPost('note')
            );

            $this->session->setFlashdata('success', 'Item issued and stock updated.');
        }

        return redirect()->to('/issued-products');
    }
}
