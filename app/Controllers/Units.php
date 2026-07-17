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
        return $this->form(null);
    }

    public function edit($id)
    {
        $this->requirePermission('items', 'edit');
        return $this->form((int) $id);
    }

    private function form(?int $unitId)
    {
        $unitModel = model(UnitModel::class);
        $existing  = $unitId ? $unitModel->find($unitId) : null;

        if ($unitId && ! $existing) {
            return redirect()->to('/units');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'branch_id'         => $this->branchId,
                'name'              => $this->request->getPost('name'),
                'short_name'        => $this->request->getPost('short_name'),
                'conversion_factor' => $this->request->getPost('conversion_factor') ?: null,
                'base_unit_id'      => $this->request->getPost('base_unit_id') ?: null,
                'description'       => $this->request->getPost('description'),
            ];

            if ($unitId) {
                $unitModel->update($unitId, $data);
            } else {
                $unitModel->createForBranch($data);
            }

            return redirect()->to('/units');
        }

        $data = [
            'title'      => $unitId ? 'Edit Unit' : 'Add Unit',
            'unit'       => $existing,
            'allUnits'   => array_filter($unitModel->getForBranch($this->branchId), static fn ($u) => $u['id'] !== $unitId),
        ];

        return view('layout/header', $data)
            . view('units/add', $data)
            . view('layout/footer');
    }

    public function delete($id)
    {
        $this->requirePermission('items', 'delete');
        model(UnitModel::class)->update((int) $id, ['status' => 'inactive']);
        $this->session->setFlashdata('success', 'Unit deleted.');
        return redirect()->to('/units');
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
