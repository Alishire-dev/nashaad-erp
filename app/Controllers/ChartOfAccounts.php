<?php

namespace App\Controllers;

use App\Models\ChartOfAccountModel;
use App\Models\SubAccountTypeModel;

class ChartOfAccounts extends BaseController
{
    public function index()
    {
        $this->requirePermission('accounting', 'view');

        $data = [
            'title'    => 'Charts of Account List',
            'accounts' => model(ChartOfAccountModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('accounting/chart_of_accounts', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('accounting', 'add');

        if ($this->request->getMethod() === 'POST') {
            model(ChartOfAccountModel::class)->createForBranch([
                'branch_id'           => $this->branchId,
                'account_name'        => $this->request->getPost('account_name'),
                'sub_account_type_id' => $this->request->getPost('sub_account_type_id'),
                'description'         => $this->request->getPost('description'),
            ]);
            return redirect()->to('/accounting/chart-of-accounts');
        }

        $data = ['title' => 'Add Chart of Account', 'subTypes' => model(SubAccountTypeModel::class)->getAll()];

        return view('layout/header', $data)
            . view('accounting/chart_of_account_add', $data)
            . view('layout/footer');
    }
}
