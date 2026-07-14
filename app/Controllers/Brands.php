<?php

namespace App\Controllers;

use App\Models\BrandModel;

class Brands extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'  => 'Brands List',
            'brands' => model(BrandModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('brands/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $brandModel = model(BrandModel::class);

        if ($this->request->getMethod() === 'POST') {
            $brandModel->createForBranch([
                'branch_id' => $this->branchId,
                'name'      => $this->request->getPost('name'),
            ]);

            return redirect()->to('/brands');
        }

        return view('layout/header', ['title' => 'Add Brand'])
            . view('brands/add')
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the Items form's inline "+" popup.
     */
    public function quickAdd()
    {
        $this->requirePermission('items', 'add');

        $id = model(BrandModel::class)->createForBranch([
            'branch_id' => $this->branchId,
            'name'      => $this->request->getPost('name'),
        ]);

        return $this->response->setJSON(['id' => $id, 'name' => $this->request->getPost('name')]);
    }
}
