<?php

namespace App\Controllers;

use App\Models\UnitModel;

class Units extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title' => 'Units List',
            'units' => model(UnitModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('units/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $unitModel = model(UnitModel::class);

        if ($this->request->getMethod() === 'POST') {
            $unitModel->createForBranch([
                'branch_id'   => $this->branchId,
                'name'        => $this->request->getPost('name'),
                'short_name'  => $this->request->getPost('short_name'),
                'description' => $this->request->getPost('description'),
            ]);

            return redirect()->to('/units');
        }

        return view('layout/header', ['title' => 'Add Unit'])
            . view('units/add')
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the Items form's inline "+" popup.
     */
    public function quickAdd()
    {
        $this->requirePermission('items', 'add');

        $id = model(UnitModel::class)->createForBranch([
            'branch_id'  => $this->branchId,
            'name'       => $this->request->getPost('name'),
            'short_name' => $this->request->getPost('short_name') ?: $this->request->getPost('name'),
        ]);

        return $this->response->setJSON(['id' => $id, 'name' => $this->request->getPost('name')]);
    }
}
