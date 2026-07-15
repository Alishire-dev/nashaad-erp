<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\StockAdjustmentModel;
use App\Models\MigrationControlAccountModel;
use App\Models\ItemPriceLogModel;

class StockManager extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'             => 'Stock Manager',
            'recent'            => model(StockAdjustmentModel::class)->recentForBranch($this->branchId),
            'items'             => model(ItemModel::class)->getForBranch($this->branchId),
            'migrationAccounts' => model(MigrationControlAccountModel::class)->getActive(),
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
            $qty       = (float) $this->request->getPost('adjust_qty');
            $direction = $this->request->getPost('status') === 'decrease' ? 'out' : 'in';
            $note      = $this->request->getPost('narrative');
            $date      = $this->request->getPost('adjustment_date') ?: date('Y-m-d');
            $accountId = $this->request->getPost('migration_control_account_id') ?: null;

            model(StockAdjustmentModel::class)->record(
                $this->branchId,
                $itemId,
                $qty,
                $direction,
                'manual_correction',
                (int) $this->currentUser['id'],
                $note,
                $date,
                $accountId ? (int) $accountId : null
            );

            $this->session->setFlashdata('success', 'Stock adjusted successfully.');
        }

        return redirect()->to('/stock/manager');
    }

    /**
     * "Update Price" action — updates the four price fields directly from
     * Stock Manager (separate from the full Edit Item Details form), and
     * logs the sales-price change to item_price_log for the (still pending)
     * Price Change Log screen.
     */
    public function updatePrice($id)
    {
        $this->requirePermission('items', 'edit');

        $itemModel = model(ItemModel::class);
        $item = $itemModel->find((int) $id);

        if (! $item) {
            return redirect()->to('/stock/manager');
        }

        if ($this->request->getMethod() === 'POST') {
            $newSalesPrice = (float) $this->request->getPost('sales_price');

            if ($newSalesPrice != (float) $item['sales_price']) {
                model(ItemPriceLogModel::class)->insert([
                    'item_id'    => $item['id'],
                    'old_price'  => $item['sales_price'],
                    'new_price'  => $newSalesPrice,
                    'changed_by' => $this->currentUser['id'],
                    'changed_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $itemModel->update($item['id'], [
                'purchase_price'  => $this->request->getPost('purchase_price') ?: 0,
                'sales_price'     => $newSalesPrice,
                'wholesale_price' => $this->request->getPost('wholesale_price') ?: 0,
                'minimum_price'   => $this->request->getPost('promotion_price') ?: 0,
            ]);

            $this->session->setFlashdata('success', 'Price updated successfully.');
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
