<?php

namespace App\Controllers;

use App\Models\SubAccountTypeModel;
use App\Models\AccountTypeModel;

class SubAccountTypes extends BaseController
{
    public function index()
    {
        $this->requirePermission('accounting', 'view');

        $data = [
            'title'    => 'Sub Account Type List',
            'subTypes' => model(SubAccountTypeModel::class)->getAll(),
        ];

        return view('layout/header', $data)
            . view('accounting/sub_account_types', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('accounting', 'add');

        if ($this->request->getMethod() === 'POST') {
            model(SubAccountTypeModel::class)->create([
                'name'            => $this->request->getPost('name'),
                'account_type_id' => $this->request->getPost('account_type_id'),
                'description'     => $this->request->getPost('description'),
            ]);
            return redirect()->to('/accounting/sub-account-types');
        }

        $data = ['title' => 'Add Sub Account Type', 'accountTypes' => model(AccountTypeModel::class)->getAll()];

        return view('layout/header', $data)
            . view('accounting/sub_account_type_add', $data)
            . view('layout/footer');
    }
}
