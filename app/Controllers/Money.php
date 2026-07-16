<?php

namespace App\Controllers;

use App\Models\MoneyTransactionModel;
use App\Models\ChartOfAccountModel;

class Money extends BaseController
{
    public function index()
    {
        $this->requirePermission('accounting', 'view');

        $data = [
            'title'        => 'Manage Money',
            'transactions' => model(MoneyTransactionModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('accounting/money', $data)
            . view('layout/footer');
    }

    public function makePayment()
    {
        $this->requirePermission('accounting', 'add');
        return $this->form('out');
    }

    public function receivePayment()
    {
        $this->requirePermission('accounting', 'add');
        return $this->form('in');
    }

    private function form(string $direction)
    {
        $accountModel = model(ChartOfAccountModel::class);

        if ($this->request->getMethod() === 'POST') {
            $amount = (float) $this->request->getPost('amount');

            model(MoneyTransactionModel::class)->post(
                $this->branchId,
                (int) $this->currentUser['id'],
                $this->request->getPost('payment_date') ?: date('Y-m-d'),
                $this->request->getPost('account_name'),
                $direction === 'in' ? $amount : 0,
                $direction === 'out' ? $amount : 0,
                $this->request->getPost('description'),
                $this->request->getPost('pay_mode') ?: 'cash',
                'manual'
            );

            $this->session->setFlashdata('success', 'Transaction recorded.');
            return redirect()->to('/accounting/money');
        }

        $data = [
            'title'     => $direction === 'in' ? 'Receive Payment' : 'Make Payment',
            'direction' => $direction,
            'accounts'  => $accountModel->getActive($this->branchId),
        ];

        return view('layout/header', $data)
            . view('accounting/money_form', $data)
            . view('layout/footer');
    }
}
