<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class Customers extends BaseController
{
    public function index()
    {
        $this->requirePermission('customers', 'view');

        $data = [
            'title'     => 'Customers List',
            'customers' => model(CustomerModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('customers/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('customers', 'add');

        $customerModel = model(CustomerModel::class);

        if ($this->request->getMethod() === 'POST') {
            $customerModel->createForBranch([
                'branch_id' => $this->branchId,
                'name'      => $this->request->getPost('name'),
                'phone'     => $this->request->getPost('phone'),
                'email'     => $this->request->getPost('email'),
                'address'   => $this->request->getPost('address'),
            ]);

            return redirect()->to('/customers');
        }

        return view('layout/header', ['title' => 'Add Customer'])
            . view('customers/add')
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the POS screen's "+ new customer" popup.
     */
    public function quickAdd()
    {
        $this->requirePermission('customers', 'add');

        $id = model(CustomerModel::class)->createForBranch([
            'branch_id' => $this->branchId,
            'name'      => $this->request->getPost('name'),
            'phone'     => $this->request->getPost('phone'),
        ]);

        return $this->response->setJSON(['id' => $id, 'name' => $this->request->getPost('name')]);
    }
}
