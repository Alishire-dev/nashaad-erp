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
        ];

        return view('layout/header', $data)
            . view('items/issued', $data)
            . view('layout/footer');
    }

    public function addForm()
    {
        $this->requirePermission('items', 'add');

        $data = [
            'title' => 'Add Issued Products',
            'items' => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/issued_add', $data)
            . view('layout/footer');
    }

    /**
     * Accepts multiple lines in one submission (item_id, quantity, unit_price,
     * note per row) — matches the original's multi-row "+" cart form, not a
     * single-item form.
     */
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
                'issued',
                (int) $this->currentUser['id'],
                $line['note'] ?? null,
                $line['date'] ?? null,
                null,
                isset($line['unit_price']) ? (float) $line['unit_price'] : null
            );
        }

        $this->session->setFlashdata('success', 'Items issued and stock updated.');
        return redirect()->to('/issued-products');
    }

    public function delete($id)
    {
        $this->requirePermission('items', 'delete');
        model(StockAdjustmentModel::class)->deleteAndReverse((int) $id);
        $this->session->setFlashdata('success', 'Issued record deleted and stock restored.');
        return redirect()->to('/issued-products');
    }
}
