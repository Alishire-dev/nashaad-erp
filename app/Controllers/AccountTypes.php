<?php

namespace App\Controllers;

use App\Models\AccountTypeModel;

class AccountTypes extends BaseController
{
    public function index()
    {
        $this->requirePermission('accounting', 'view');

        $data = [
            'title'        => 'Accounts Type List',
            'accountTypes' => model(AccountTypeModel::class)->getAll(),
        ];

        return view('layout/header', $data)
            . view('accounting/account_types', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('accounting', 'add');

        if ($this->request->getMethod() === 'POST') {
            model(AccountTypeModel::class)->insert([
                'name'   => $this->request->getPost('name'),
                'status' => 'active',
            ]);
            return redirect()->to('/accounting/account-types');
        }

        return view('layout/header', ['title' => 'Add Account Type'])
            . view('accounting/account_type_add')
            . view('layout/footer');
    }
}
