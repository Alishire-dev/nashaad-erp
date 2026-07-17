<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseReturnModel;
use App\Models\PurchasePaymentModel;
use App\Models\SupplierModel;
use App\Models\ItemModel;

class Purchase extends BaseController
{
    public function index()
    {
        $this->requirePermission('purchase', 'view');

        $data = [
            'title'     => 'Purchase List',
            'purchases' => model(PurchaseModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('purchase/list', $data)
            . view('layout/footer');
    }

    public function view($id)
    {
        $this->requirePermission('purchase', 'view');

        $purchase = model(PurchaseModel::class)->getWithLines((int) $id);
        if (! $purchase) {
            return redirect()->to('/purchase/list');
        }

        return view('layout/header', ['title' => 'Purchase #' . $id])
            . view('purchase/view', ['purchase' => $purchase])
            . view('layout/footer');
    }

    public function viewPayments($id)
    {
        $this->requirePermission('purchase', 'view');

        $purchase = model(PurchaseModel::class)->getWithLines((int) $id);
        if (! $purchase) {
            return redirect()->to('/purchase/list');
        }

        $data = [
            'title'    => 'Payments',
            'purchase' => $purchase,
            'payments' => model(PurchasePaymentModel::class)->getForPurchase((int) $id),
        ];

        return view('layout/header', $data)
            . view('purchase/payments', $data)
            . view('layout/footer');
    }

    public function addPayment($id)
    {
        $this->requirePermission('purchase', 'add');

        model(PurchasePaymentModel::class)->addPayment(
            (int) $id,
            $this->request->getPost('payment_date') ?: date('Y-m-d'),
            (float) $this->request->getPost('amount'),
            $this->request->getPost('payment_type') ?: 'cash',
            $this->request->getPost('voucher_no'),
            $this->request->getPost('payment_note'),
            (int) $this->currentUser['id']
        );

        $this->session->setFlashdata('success', 'Payment recorded.');
        return redirect()->to('/purchase/payments/' . $id);
    }

    public function deletePayment($paymentId)
    {
        $this->requirePermission('purchase', 'delete');

        $payment = model(PurchasePaymentModel::class)->find((int) $paymentId);
        $purchaseId = $payment['purchase_id'] ?? null;

        model(PurchasePaymentModel::class)->deletePayment((int) $paymentId);

        $this->session->setFlashdata('success', 'Payment deleted.');
        return redirect()->to('/purchase/payments/' . $purchaseId);
    }

    public function add()
    {
        $this->requirePermission('purchase', 'add');

        if ($this->request->getMethod() === 'POST') {
            $linesJson = $this->request->getPost('lines_json');
            $lines     = json_decode((string) $linesJson, true) ?: [];

            if (empty($lines) || empty($this->request->getPost('supplier_id'))) {
                return $this->renderForm('Please select a supplier and add at least one item.');
            }

            $header = [
                'branch_id'     => $this->branchId,
                'supplier_id'   => (int) $this->request->getPost('supplier_id'),
                'reference_no'  => $this->request->getPost('reference_no'),
                'purchase_date' => $this->request->getPost('purchase_date') ?: date('Y-m-d'),
                'due_date'      => $this->request->getPost('due_date') ?: null,
                'status'        => 'received',
                'note'          => $this->request->getPost('note'),
            ];

            $purchaseId = model(PurchaseModel::class)->createWithLines($header, $lines, (int) $this->currentUser['id']);

            if (! $purchaseId) {
                return $this->renderForm('Could not save the purchase. Please try again.');
            }

            $this->session->setFlashdata('success', 'Purchase #' . $purchaseId . ' saved and stock updated.');
            return redirect()->to('/purchase/list');
        }

        return $this->renderForm();
    }

    private function renderForm(?string $error = null)
    {
        $data = [
            'title'     => 'New Purchase',
            'suppliers' => model(SupplierModel::class)->getForBranch($this->branchId),
            'items'     => model(ItemModel::class)->getForBranch($this->branchId),
            'error'     => $error,
        ];

        return view('layout/header', $data)
            . view('purchase/add', $data)
            . view('layout/footer');
    }

    // ---------------- Purchase Return ----------------

    public function returns()
    {
        $this->requirePermission('purchase', 'view');

        $data = [
            'title'   => 'Purchase Returns',
            'returns' => model(PurchaseReturnModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('purchase/returns_list', $data)
            . view('layout/footer');
    }

    public function returnAdd()
    {
        $this->requirePermission('purchase', 'add');

        $purchaseModel = model(PurchaseModel::class);

        if ($this->request->getMethod() === 'POST') {
            $purchaseId = (int) $this->request->getPost('purchase_id');
            $linesJson  = $this->request->getPost('lines_json');
            $lines      = json_decode((string) $linesJson, true) ?: [];

            if (! $purchaseId || empty($lines)) {
                $data = [
                    'title'     => 'New Purchase Return',
                    'purchases' => $purchaseModel->getForBranch($this->branchId),
                    'error'     => 'Select a purchase and at least one item to return.',
                ];
                return view('layout/header', $data) . view('purchase/return_add', $data) . view('layout/footer');
            }

            $header = [
                'branch_id'   => $this->branchId,
                'purchase_id' => $purchaseId,
                'return_date' => date('Y-m-d'),
                'reason'      => $this->request->getPost('reason'),
            ];

            model(PurchaseReturnModel::class)->createWithLines($header, $lines, (int) $this->currentUser['id']);

            $this->session->setFlashdata('success', 'Purchase return recorded and stock adjusted.');
            return redirect()->to('/purchase/returns');
        }

        $data = [
            'title'     => 'New Purchase Return',
            'purchases' => $purchaseModel->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('purchase/return_add', $data)
            . view('layout/footer');
    }

    /**
     * AJAX: fetch a purchase's line items to populate the return form.
     */
    public function linesJson($purchaseId)
    {
        $purchase = model(PurchaseModel::class)->getWithLines((int) $purchaseId);
        return $this->response->setJSON($purchase['lines'] ?? []);
    }
}
