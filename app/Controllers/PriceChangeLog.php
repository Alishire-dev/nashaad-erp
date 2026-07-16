<?php

namespace App\Controllers;

use App\Models\ItemPriceLogModel;

class PriceChangeLog extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title' => 'Price Change Log',
            'logs'  => model(ItemPriceLogModel::class)->recentForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/price_change_log', $data)
            . view('layout/footer');
    }
}
