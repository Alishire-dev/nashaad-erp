<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\CustomerModel;
use App\Models\SaleModel;

class Pos extends BaseController
{
    public function index()
    {
        $this->requirePermission('pos', 'view');

        $itemModel     = model(ItemModel::class);
        $categoryModel = model(CategoryModel::class);
        $customerModel = model(CustomerModel::class);
        $saleModel     = model(SaleModel::class);

        $categories = array_filter($categoryModel->getForBranch($this->branchId), static fn ($c) => (int) ($c['show_on_pos'] ?? 1) === 1);

        $data = [
            'title'      => 'POS',
            'categories' => $categories,
            'items'      => array_filter($itemModel->getForBranch($this->branchId), static fn ($i) => $i['status'] === 'active'),
            'customers'  => $customerModel->getForBranch($this->branchId),
            'walkIn'     => $customerModel->walkIn($this->branchId),
            'heldCount'  => count($saleModel->getHeldSales($this->branchId)),
        ];

        return view('layout/header', $data)
            . view('pos/index', $data)
            . view('layout/footer');
    }

    /**
     * Finalizes the sale. Cart arrives as a JSON string (same pattern as
     * Purchase's lines_json) rather than dozens of indexed POST fields.
     */
    public function checkout()
    {
        $this->requirePermission('pos', 'add');

        $lines = json_decode((string) $this->request->getPost('lines_json'), true) ?: [];

        if (empty($lines)) {
            $this->session->setFlashdata('error', 'Cart is empty.');
            return redirect()->to('/pos');
        }

        $header = [
            'branch_id'    => $this->branchId,
            'customer_id'  => (int) $this->request->getPost('customer_id'),
            'sale_date'    => date('Y-m-d'),
            'discount_pct' => (float) ($this->request->getPost('discount_pct') ?: 0),
            'amount_paid'  => (float) ($this->request->getPost('amount_paid') ?: 0),
            'pay_mode'     => $this->request->getPost('pay_mode') ?: 'cash',
        ];

        $saleId = model(SaleModel::class)->checkout($header, $lines, (int) $this->currentUser['id']);

        if (! $saleId) {
            $this->session->setFlashdata('error', 'Could not complete the sale. Please try again.');
            return redirect()->to('/pos');
        }

        $this->session->setFlashdata('success', 'Sale completed successfully.');
        return redirect()->to('/sales/view/' . $saleId);
    }

    public function hold()
    {
        $this->requirePermission('pos', 'add');

        $lines = json_decode((string) $this->request->getPost('lines_json'), true) ?: [];
        if (empty($lines)) {
            return redirect()->to('/pos');
        }

        $header = [
            'branch_id'   => $this->branchId,
            'customer_id' => (int) $this->request->getPost('customer_id'),
            'sale_date'   => date('Y-m-d'),
        ];

        model(SaleModel::class)->holdSale($header, $lines, (int) $this->currentUser['id']);

        $this->session->setFlashdata('success', 'Cart held.');
        return redirect()->to('/pos');
    }

    public function heldList()
    {
        $this->requirePermission('pos', 'view');

        $held = model(SaleModel::class)->getHeldSales($this->branchId);
        return $this->response->setJSON($held);
    }

    /**
     * Returns a held sale's lines as JSON so the POS screen's JS can
     * repopulate the cart, then removes the held record — it gets
     * recreated properly once the cashier actually checks out.
     */
    public function recall($id)
    {
        $this->requirePermission('pos', 'view');

        $saleModel = model(SaleModel::class);
        $sale = $saleModel->getHeldSaleWithLines((int) $id);

        if (! $sale) {
            return $this->response->setJSON(['error' => 'Not found']);
        }

        $saleModel->deleteHeldSale((int) $id);

        return $this->response->setJSON($sale);
    }
}
