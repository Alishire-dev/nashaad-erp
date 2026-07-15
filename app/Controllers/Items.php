<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\UnitModel;
use App\Models\BrandModel;
use App\Models\TaxCategoryModel;

class Items extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $itemModel = model(ItemModel::class);
        $data = [
            'title' => 'Items List',
            'items' => $itemModel->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/list', $data)
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

    /**
     * Shared handler for both New Item and Edit Item — the original system
     * treats these as one "Add/Update" screen, so this does too.
     */
    private function form(?int $itemId)
    {
        $itemModel = model(ItemModel::class);
        $existing  = $itemId ? $itemModel->find($itemId) : null;

        if ($itemId && ! $existing) {
            return redirect()->to('/items/list');
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'branch_id'           => $this->branchId,
                'category_id'         => $this->request->getPost('category_id') ?: null,
                'brand_id'            => $this->request->getPost('brand_id') ?: null,
                'unit_id'             => $this->request->getPost('unit_id'),
                'tax_category_id'     => $this->request->getPost('tax_category_id') ?: 1,
                'tax_type'            => $this->request->getPost('tax_type') ?: 'inclusive',
                'name'                => $this->request->getPost('name'),
                'sku'                 => $this->request->getPost('sku') ?: null,
                'expiry_date'         => $this->request->getPost('expiry_date') ?: null,
                'purpose'             => $this->request->getPost('purpose') ?: 'for_sale',
                'order_item'          => $this->request->getPost('order_item') === 'no' ? 0 : 1,
                'manage_stock'        => $this->request->getPost('manage_stock') === 'yes' ? 1 : 0,
                'allow_negative_sale' => $this->request->getPost('allow_negative_sale') === 'yes' ? 1 : 0,
                'alert_qty'           => $this->request->getPost('alert_qty') ?: 0,
                'purchase_price'      => $this->request->getPost('purchase_price') ?: 0,
                'sales_price'         => $this->request->getPost('sales_price') ?: 0,
                'wholesale_price'     => $this->request->getPost('wholesale_price') ?: 0,
                'minimum_price'       => $this->request->getPost('minimum_price') ?: 0,
                'profit_margin'       => $this->request->getPost('profit_margin') ?: 0,
                'price_change_all_branches' => $this->request->getPost('price_change_all_branches') === 'yes' ? 1 : 0,
                'sales_commission'    => $this->request->getPost('sales_commission') ?: 0,
                'description'         => $this->request->getPost('description'),
            ];

            // Opening stock only makes sense on create; edits go through Stock Manager
            // instead so stock changes always leave an audit trail.
            if (! $itemId) {
                $data['current_stock'] = $this->request->getPost('opening_stock') ?: 0;
            }

            $uploadedImage = $this->handleImageUpload($existing['image'] ?? null);
            if ($uploadedImage !== false) {
                if ($uploadedImage !== null) {
                    $data['image'] = $uploadedImage;
                }
            } else {
                return $this->renderForm($itemId, $existing);
            }

            $isValid = $itemId
                ? $itemModel->update($itemId, $data)
                : $itemModel->createForBranch($data);

            if (! $isValid) {
                return $this->renderForm($itemId, $existing, $itemModel->errors());
            }

            $this->session->setFlashdata('success', $itemId ? 'Item updated successfully.' : 'Item created successfully.');
            return redirect()->to('/items/list');
        }

        return $this->renderForm($itemId, $existing);
    }

    /**
     * Item Profile — basic info + purchase batch history + conversion children.
     */
    public function profile($id)
    {
        $this->requirePermission('items', 'view');

        $itemModel = model(ItemModel::class);
        $item = $itemModel->getProfileData((int) $id);

        if (! $item) {
            return redirect()->to('/items/list');
        }

        $data = [
            'title'    => 'Item Profile',
            'item'     => $item,
            'batches'  => $itemModel->getPurchaseBatches((int) $id),
            'children' => $itemModel->getConversionChildren((int) $id),
            'units'    => model(UnitModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/profile', $data)
            . view('layout/footer');
    }

    /**
     * Item Conversion — "Create Conversion" tab creates a real child item + recipe;
     * "Conversion List" tab shows existing ones. Both rendered inline on the
     * Items List page via a modal, not a separate route — this action just
     * handles the Create Conversion form POST and redirects back.
     */
    public function conversionCreate($id)
    {
        $this->requirePermission('items', 'add');

        $itemModel = model(ItemModel::class);
        $parent = $itemModel->find((int) $id);

        if (! $parent) {
            return redirect()->to('/items/list');
        }

        model(\App\Models\ItemConversionModel::class)->createConversion($parent, [
            'name'            => $this->request->getPost('name'),
            'unit_id'         => $this->request->getPost('unit_id'),
            'conversion_rate' => $this->request->getPost('conversion_rate'),
            'sales_price'     => $this->request->getPost('sales_price'),
            'description'     => $this->request->getPost('description'),
        ], (int) $this->currentUser['id']);

        $this->session->setFlashdata('success', 'Conversion child item created.');
        return redirect()->to('/items/list');
    }

    /**
     * Soft delete — sets status=inactive rather than removing the row.
     * Items already referenced by purchases/sales/stock movements can't be
     * hard-deleted without breaking those records' history, so "Delete"
     * here means "remove from active use", not "erase".
     */
    public function delete($id)
    {
        $this->requirePermission('items', 'delete');

        model(ItemModel::class)->update((int) $id, ['status' => 'inactive']);

        $this->session->setFlashdata('success', 'Item deleted (marked inactive).');
        return redirect()->to('/items/list');
    }

    private function renderForm(?int $itemId, ?array $existing, ?array $validation = null)
    {
        $data = [
            'title'         => $itemId ? 'Edit Item' : 'New Item',
            'item'          => $existing,
            'categories'    => model(CategoryModel::class)->getForBranch($this->branchId),
            'units'         => model(UnitModel::class)->getForBranch($this->branchId),
            'brands'        => model(BrandModel::class)->getForBranch($this->branchId),
            'taxCategories' => model(TaxCategoryModel::class)->getActive(),
            'validation'    => $validation,
        ];

        return view('layout/header', $data)
            . view('items/add', $data)
            . view('layout/footer');
    }

    /**
     * Handles the optional image upload.
     * Returns: new filename (string) if a file was uploaded, null if the
     * field was empty (keep whatever was there before), or false on
     * validation failure.
     */
    private function handleImageUpload(?string $previousImage)
    {
        $file = $this->request->getFile('image');

        if (! $file || ! $file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null; // no new file selected — leave existing image alone
        }

        if (! in_array(strtolower($file->getClientExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->session->setFlashdata('error', 'Image must be jpg, jpeg, png, or webp.');
            return false;
        }

        $uploadPath = FCPATH . 'uploads/items';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        // Clean up the old image so uploads/ doesn't accumulate orphaned files
        if ($previousImage && is_file($uploadPath . '/' . $previousImage)) {
            @unlink($uploadPath . '/' . $previousImage);
        }

        return $newName;
    }
}
