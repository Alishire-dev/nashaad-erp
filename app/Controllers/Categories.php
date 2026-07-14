<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class Categories extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title'      => 'Categories List',
            'categories' => model(CategoryModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('categories/list', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('items', 'add');

        $categoryModel = model(CategoryModel::class);

        if ($this->request->getMethod() === 'POST') {
            $categoryModel->createForBranch([
                'branch_id'   => $this->branchId,
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'parent_id'   => $this->request->getPost('parent_id') ?: null,
                'show_on_pos' => $this->request->getPost('show_on_pos') === 'no' ? 0 : 1,
            ]);

            return redirect()->to('/category/view');
        }

        $branch = db_connect()->table('branches')->where('id', $this->branchId)->get()->getRowArray();

        $data = [
            'title'         => 'Add Category',
            'branchName'    => $branch['name'] ?? 'Main Branch',
            'allCategories' => $categoryModel->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('categories/add', $data)
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the Items form's inline "+" popup.
     * Defaults parent_id=null, show_on_pos=yes — full detail can be edited later.
     */
    public function quickAdd()
    {
        $this->requirePermission('items', 'add');

        $categoryModel = model(CategoryModel::class);
        $id = $categoryModel->createForBranch([
            'branch_id' => $this->branchId,
            'name'      => $this->request->getPost('name'),
        ]);

        return $this->response->setJSON(['id' => $id, 'name' => $this->request->getPost('name')]);
    }
}
