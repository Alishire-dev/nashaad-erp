<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\StockAdjustmentModel;

class StockManager extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'  => 'Stock Manager',
            'recent' => model(StockAdjustmentModel::class)->recentForBranch($this->branchId),
            'items'  => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('stock/manager', $data)
            . view('layout/footer');
    }

    public function adjust()
    {
        $this->requirePermission('items', 'edit');

        if ($this->request->getMethod() === 'POST') {
            $itemId    = (int) $this->request->getPost('item_id');
            $qty       = (float) $this->request->getPost('quantity');
            $direction = $this->request->getPost('direction') === 'out' ? 'out' : 'in';
            $note      = $this->request->getPost('note');

            model(StockAdjustmentModel::class)->record(
                $this->branchId,
                $itemId,
                $qty,
                $direction,
                'manual_correction',
                (int) $this->currentUser['id'],
                $note
            );

            $this->session->setFlashdata('success', 'Stock adjusted successfully.');
        }

        return redirect()->to('/stock/manager');
    }

    /**
     * Dedicated Stock Alert page (dashboard shows the summary; this is the full list).
     */
    public function alerts()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'    => 'Stock Alert',
            'lowStock' => model(ItemModel::class)->lowStock($this->branchId),
        ];

        return view('layout/header', $data)
            . view('stock/alerts', $data)
            . view('layout/footer');
    }
}
