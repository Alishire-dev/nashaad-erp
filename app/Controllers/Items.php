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
            'title'         => 'Items List',
            'items'         => $itemModel->getForBranch($this->branchId),
            'quickLinksView'=> 'items/_quick_links',
        ];

        return view('layout/header', $data)
            . view('items/list', $data)
            . view('layout/footer');
    }

    /**
     * Archived Items — Items List filtered to status=inactive (soft-deleted
     * items). Same columns/DataTable pattern as the main list.
     */
    public function archived()
    {
        $this->requirePermission('items', 'view');

        $itemModel = model(ItemModel::class);
        $data = [
            'title'          => 'Archived Items',
            'items'          => array_filter($itemModel->getForBranch($this->branchId), static fn ($i) => $i['status'] === 'inactive'),
            'quickLinksView' => 'items/_quick_links',
            'isArchivedView' => true,
        ];

        return view('layout/header', $data)
            . view('items/archived', $data)
            . view('layout/footer');
    }

    /**
     * Restores an archived (inactive) item back to active.
     */
    public function restore($id)
    {
        $this->requirePermission('items', 'edit');
        model(ItemModel::class)->update((int) $id, ['status' => 'active']);
        $this->session->setFlashdata('success', 'Item restored.');
        return redirect()->to('/items/archived');
    }

    /**
     * Download Upload Template — a blank CSV with the exact headers
     * bulkUpload() expects, so a real spreadsheet round-trip is possible.
     */
    public function downloadTemplate()
    {
        $this->requirePermission('items', 'view');

        $headers = ['item_code', 'name', 'category', 'unit', 'alert_qty', 'purchase_price', 'sales_price', 'wholesale_price', 'minimum_price'];
        $sample  = ['', 'Sample Item Name', 'Existing Category Name', 'Existing Unit Name', '0', '0.00', '0.00', '0.00', '0.00'];

        $csv = implode(',', $headers) . "\n" . implode(',', $sample) . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="items_upload_template.csv"')
            ->setBody($csv);
    }

    /**
     * Bulk Items Upload — CSV round-trip of the template above. Matches
     * existing items by item_code (updates them); blank item_code creates
     * a new item. Category/Unit are matched by name, created if missing —
     * same pattern as ImportOriginalItemsSeeder, just reachable from the UI
     * instead of the command line.
     */
    public function bulkUploadForm()
    {
        $this->requirePermission('items', 'add');

        $data = [
            'title'             => 'Bulk Items Upload',
            'quickLinksView'    => 'items/_quick_links',
            'migrationAccounts' => model(\App\Models\MigrationControlAccountModel::class)->getActive(),
        ];

        return view('layout/header', $data)
            . view('items/bulk_upload', $data)
            . view('layout/footer');
    }

    public function bulkUpload()
    {
        $this->requirePermission('items', 'add');

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            $this->session->setFlashdata('error', 'Please choose a valid CSV file.');
            return redirect()->to('/items/bulk-upload');
        }

        $itemModel     = model(ItemModel::class);
        $categoryModel = model(CategoryModel::class);
        $unitModel     = model(UnitModel::class);

        $rows = array_map('str_getcsv', file($file->getTempName()));
        $header = array_map('trim', array_shift($rows));

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                $skipped++;
                continue;
            }
            $r = array_combine($header, $row);
            $name = trim($r['name'] ?? '');
            if ($name === '') {
                $skipped++;
                continue;
            }

            $categoryId = null;
            if (! empty($r['category'])) {
                $existing = array_filter($categoryModel->getForBranch($this->branchId), static fn ($c) => strcasecmp($c['name'], trim($r['category'])) === 0);
                $categoryId = $existing ? array_values($existing)[0]['id'] : $categoryModel->createForBranch(['branch_id' => $this->branchId, 'name' => trim($r['category'])]);
            }

            $unitId = null;
            if (! empty($r['unit'])) {
                $existingU = array_filter($unitModel->getForBranch($this->branchId), static fn ($u) => strcasecmp($u['name'], trim($r['unit'])) === 0);
                $unitId = $existingU ? array_values($existingU)[0]['id'] : $unitModel->createForBranch(['branch_id' => $this->branchId, 'name' => trim($r['unit']), 'short_name' => trim($r['unit'])]);
            }

            $itemCode = trim($r['item_code'] ?? '');
            $data = [
                'branch_id'       => $this->branchId,
                'category_id'     => $categoryId,
                'unit_id'         => $unitId,
                'alert_qty'       => (float) ($r['alert_qty'] ?? 0),
                'purchase_price'  => (float) ($r['purchase_price'] ?? 0),
                'sales_price'     => (float) ($r['sales_price'] ?? 0),
                'wholesale_price' => (float) ($r['wholesale_price'] ?? 0),
                'minimum_price'   => (float) ($r['minimum_price'] ?? 0),
                'name'            => $name,
            ];

            $existingItem = $itemCode !== '' ? $itemModel->where('item_code', $itemCode)->first() : null;

            if ($existingItem) {
                $itemModel->update($existingItem['id'], $data);
                $updated++;
            } else {
                if ($itemCode !== '') {
                    $data['item_code'] = $itemCode;
                    $itemModel->insert($data);
                } else {
                    $itemModel->createForBranch($data);
                }
                $created++;
            }
        }

        $this->session->setFlashdata('success', "Bulk upload complete: {$created} created, {$updated} updated, {$skipped} skipped.");
        return redirect()->to('/items/list');
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
