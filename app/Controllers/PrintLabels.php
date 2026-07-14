<?php

namespace App\Controllers;

use App\Models\ItemModel;

class PrintLabels extends BaseController
{
    public function index()
    {
        $this->requirePermission('items', 'view');

        $data = [
            'title' => 'Print Labels',
            'items' => model(ItemModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('items/print_labels', $data)
            . view('layout/footer');
    }

    /**
     * Renders a clean, no-sidebar label sheet for the selected item IDs.
     * Separate from the picker page so it can be sent straight to Ctrl+P.
     */
    public function sheet()
    {
        $this->requirePermission('items', 'view');

        $ids = $this->request->getGet('ids');
        $ids = $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];

        $itemModel = model(ItemModel::class);
        $allItems  = $itemModel->getForBranch($this->branchId);
        $selected  = array_filter($allItems, static fn ($i) => in_array((int) $i['id'], $ids, true));

        return view('items/label_sheet', ['items' => $selected]);
    }
}
