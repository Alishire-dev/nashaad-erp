<?php

namespace App\Controllers;

use App\Models\SaleModel;

class Sales extends BaseController
{
    public function index()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title' => 'Sales List',
            'sales' => model(SaleModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/list', $data)
            . view('layout/footer');
    }

    public function view($id)
    {
        $this->requirePermission('sales', 'view');

        $sale = model(SaleModel::class)->getWithLines((int) $id);
        if (! $sale) {
            return redirect()->to('/sales/list');
        }

        return view('layout/header', ['title' => 'Sale ' . $sale['invoice_no']])
            . view('sales/view', ['sale' => $sale])
            . view('layout/footer');
    }
}
