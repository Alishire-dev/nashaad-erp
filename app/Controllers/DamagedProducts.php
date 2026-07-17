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
        ];

        return view('layout/header', $data)
            . view('items/damaged', $data)
            . view('layout/footer');
    }

    public function addForm()
    {
        $this->requirePermission('items', 'add');

        $data = [
            'title' => 'Add Damaged Products',
            'items' => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/damaged_add', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $lines = json_decode((string) $this->request->getPost('lines_json'), true) ?: [];
        $stockModel = model(StockAdjustmentModel::class);

        foreach ($lines as $line) {
            $stockModel->record(
                $this->branchId,
                (int) $line['item_id'],
                (float) $line['quantity'],
                'out',
                'damaged',
                (int) $this->currentUser['id'],
                $line['note'] ?? null,
                $line['date'] ?? null,
                null,
                isset($line['unit_price']) ? (float) $line['unit_price'] : null
            );
        }

        $this->session->setFlashdata('success', 'Damage recorded and stock updated.');
        return redirect()->to('/damaged-products');
    }

    public function delete($id)
    {
        $this->requirePermission('items', 'delete');
        model(StockAdjustmentModel::class)->deleteAndReverse((int) $id);
        $this->session->setFlashdata('success', 'Damage record deleted and stock restored.');
        return redirect()->to('/damaged-products');
    }
}
