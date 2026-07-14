<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class Suppliers extends BaseController
{
    public function index()
    {
        $this->requirePermission('suppliers', 'view');

        $data = [
            'title'     => 'Suppliers List',
            'suppliers' => model(SupplierModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('suppliers/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('suppliers', 'add');

        $supplierModel = model(SupplierModel::class);

        if ($this->request->getMethod() === 'POST') {
            $supplierModel->createForBranch([
                'branch_id' => $this->branchId,
                'name'      => $this->request->getPost('name'),
                'phone'     => $this->request->getPost('phone'),
                'email'     => $this->request->getPost('email'),
                'address'   => $this->request->getPost('address'),
            ]);

            return redirect()->to('/suppliers');
        }

        return view('layout/header', ['title' => 'Add Supplier'])
            . view('suppliers/add')
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the Purchase screen's "+ new supplier" popup.
     */
    public function quickAdd()
    {
        $this->requirePermission('suppliers', 'add');

        $id = model(SupplierModel::class)->createForBranch([
            'branch_id' => $this->branchId,
            'name'      => $this->request->getPost('name'),
        ]);

        return $this->response->setJSON(['id' => $id, 'name' => $this->request->getPost('name')]);
    }
}
