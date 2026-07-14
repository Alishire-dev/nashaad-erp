<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class Categories extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $branch = db_connect()->table('branches')->where('id', $this->branchId)->get()->getRowArray();

        $data = [
            'title'      => 'Categories List',
            'categories' => model(CategoryModel::class)->getForBranch($this->branchId),
            'branchName' => $branch['name'] ?? 'Main Branch',
        ];

        return view('layout/header', $data)
            . view('categories/list', $data)
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

    private function form(?int $categoryId)
    {
        $categoryModel = model(CategoryModel::class);
        $existing      = $categoryId ? $categoryModel->find($categoryId) : null;

        if ($categoryId && ! $existing) {
            return redirect()->to('/category/view');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'branch_id'   => $this->branchId,
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'parent_id'   => $this->request->getPost('parent_id') ?: null,
                'show_on_pos' => $this->request->getPost('show_on_pos') === 'no' ? 0 : 1,
            ];

            if ($categoryId) {
                $categoryModel->update($categoryId, $data);
            } else {
                $categoryModel->createForBranch($data);
            }

            return redirect()->to('/category/view');
        }

        return $this->renderForm($categoryId, $existing);
    }

    private function renderForm(?int $categoryId, ?array $existing)
    {
        $branch = db_connect()->table('branches')->where('id', $this->branchId)->get()->getRowArray();

        $data = [
            'title'         => $categoryId ? 'Edit Category' : 'Add Category',
            'category'      => $existing,
            'branchName'    => $branch['name'] ?? 'Main Branch',
            'allCategories' => array_filter(
                model(CategoryModel::class)->getForBranch($this->branchId),
                static fn ($c) => $c['id'] !== $categoryId // a category can't be its own parent
            ),
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
