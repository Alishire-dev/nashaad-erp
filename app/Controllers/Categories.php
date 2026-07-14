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
            ]);

            return redirect()->to('/category/view');
        }

        return view('layout/header', ['title' => 'Add Category'])
            . view('categories/add')
            . view('layout/footer');
    }

    /**
     * Lightweight JSON endpoint for the Items form's inline "+" popup.
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
