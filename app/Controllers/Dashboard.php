<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\SaleModel;
use App\Models\PurchaseModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $itemModel     = model(ItemModel::class);
        $saleModel     = model(SaleModel::class);
        $purchaseModel = model(PurchaseModel::class);

        $data = [
            'title'         => 'Dashboard',
            'lowStock'      => $itemModel->lowStock($this->branchId),
            'totalItems'    => count($itemModel->getForBranch($this->branchId)),
            'currentUser'   => $this->currentUser,
            'salesDaily'    => $saleModel->getDailyTotals($this->branchId, 7),
            'purchaseDaily' => $purchaseModel->getDailyTotals($this->branchId, 7),
            'topMovers'     => $saleModel->getTopMovers($this->branchId, 5),
            'pendingSales'  => $saleModel->getPendingSales($this->branchId),
            'todaySummary'  => $saleModel->getTodaySummary($this->branchId),
        ];

        return view('layout/header', $data)
            . view('dashboard/index', $data)
            . view('layout/footer');
    }
}
