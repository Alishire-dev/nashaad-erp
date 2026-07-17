<?php

namespace App\Controllers;

use App\Models\PurchaseReturnModel;

class DebitNotes extends BaseController
{
    public function index()
    {
        $this->requirePermission('purchase', 'view');

        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');

        $data = [
            'title' => 'Debit Notes Report',
            'from'  => $from,
            'to'    => $to,
            'notes' => model(PurchaseReturnModel::class)->getForBranchInRange($this->branchId, $from, $to),
        ];

        return view('layout/header', $data)
            . view('purchase/debit_notes', $data)
            . view('layout/footer');
    }
}
