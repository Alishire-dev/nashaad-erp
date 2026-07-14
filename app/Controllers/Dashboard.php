<?php

namespace App\Controllers;

use App\Models\ItemModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $itemModel = model(ItemModel::class);

        $data = [
            'title'       => 'Dashboard',
            'lowStock'    => $itemModel->lowStock($this->branchId),
            'totalItems'  => count($itemModel->getForBranch($this->branchId)),
            'currentUser' => $this->currentUser,
        ];

        return view('layout/header', $data)
            . view('dashboard/index', $data)
            . view('layout/footer');
    }
}
