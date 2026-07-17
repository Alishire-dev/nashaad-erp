<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\SalePaymentModel;
use App\Models\SaleReturnModel;
use App\Models\UserModel;

class Sales extends BaseController
{
    public function index()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title' => 'Sales List',
            'sales' => model(SaleModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/list', $data)
            . view('layout/footer');
    }

    public function view($id)
    {
        $this->requirePermission('sales', 'view');

        $sale = model(SaleModel::class)->getWithLines((int) $id);
        if (! $sale) {
            return redirect()->to('/sales/list');
        }

        $data = [
            'title'         => 'Sale ' . $sale['invoice_no'],
            'sale'          => $sale,
            'salesPersons'  => model(UserModel::class)->where('status', 'active')->findAll(),
            'paymentsPreview' => model(SalePaymentModel::class)->getForSale((int) $id),
        ];

        return view('layout/header', $data)
            . view('sales/view', $data)
            . view('layout/footer');
    }

    public function updateDetails($id)
    {
        $this->requirePermission('sales', 'edit');

        model(SaleModel::class)->updateDetails((int) $id, [
            'sales_person_id' => $this->request->getPost('sales_person_id') ?: null,
            'sale_date'       => $this->request->getPost('sale_date'),
            'due_date'        => $this->request->getPost('due_date') ?: null,
            'lpo_number'      => $this->request->getPost('lpo_number'),
            'note'            => $this->request->getPost('note'),
        ]);

        $this->session->setFlashdata('success', 'Sale details updated.');
        return redirect()->to('/sales/view/' . $id);
    }

    public function applyDiscount($id)
    {
        $this->requirePermission('sales', 'edit');

        model(SaleModel::class)->applyDiscount(
            (int) $id,
            $this->request->getPost('discount_type') ?: 'percentage',
            (float) $this->request->getPost('discount_value')
        );

        $this->session->setFlashdata('success', 'Discount applied.');
        return redirect()->to('/sales/view/' . $id);
    }

    public function cancel($id)
    {
        $this->requirePermission('sales', 'delete');

        $ok = model(SaleModel::class)->cancelSale((int) $id, (int) $this->currentUser['id']);
        $this->session->setFlashdata($ok ? 'success' : 'error', $ok ? 'Sale cancelled and stock restored.' : 'Could not cancel this sale.');
        return redirect()->to('/sales/view/' . $id);
    }

    public function cancelled()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title' => 'Cancelled Sales',
            'sales' => model(SaleModel::class)->getCancelledSales($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/cancelled', $data)
            . view('layout/footer');
    }

    // ---------------- Payments ----------------

    public function viewPayments($id)
    {
        $this->requirePermission('sales', 'view');

        $sale = model(SaleModel::class)->getWithLines((int) $id);
        if (! $sale) {
            return redirect()->to('/sales/list');
        }

        $data = [
            'title'    => 'Payments',
            'sale'     => $sale,
            'payments' => model(SalePaymentModel::class)->getForSale((int) $id),
        ];

        return view('layout/header', $data)
            . view('sales/payments', $data)
            . view('layout/footer');
    }

    public function addPayment($id)
    {
        $this->requirePermission('sales', 'add');

        model(SalePaymentModel::class)->addPayment(
            (int) $id,
            $this->request->getPost('payment_date') ?: date('Y-m-d'),
            (float) $this->request->getPost('amount'),
            $this->request->getPost('payment_type') ?: 'cash',
            $this->request->getPost('payment_note'),
            (int) $this->currentUser['id']
        );

        $this->session->setFlashdata('success', 'Payment recorded.');
        return redirect()->to('/sales/payments/' . $id);
    }

    public function deletePayment($paymentId)
    {
        $this->requirePermission('sales', 'delete');

        $payment = model(SalePaymentModel::class)->find((int) $paymentId);
        $saleId  = $payment['sale_id'] ?? null;

        model(SalePaymentModel::class)->deletePayment((int) $paymentId);

        $this->session->setFlashdata('success', 'Payment deleted.');
        return redirect()->to('/sales/payments/' . $saleId);
    }

    // ---------------- Sales Return ----------------

    public function returns()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title'   => 'Sales Return',
            'returns' => model(SaleReturnModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/returns_list', $data)
            . view('layout/footer');
    }

    public function returnForm($saleId)
    {
        $this->requirePermission('sales', 'add');

        $sale = model(SaleModel::class)->getWithLines((int) $saleId);
        if (! $sale) {
            return redirect()->to('/sales/list');
        }

        return view('layout/header', ['title' => 'Sales Return'])
            . view('sales/return_add', ['sale' => $sale])
            . view('layout/footer');
    }

    public function returnAdd()
    {
        $this->requirePermission('sales', 'add');

        $saleId = (int) $this->request->getPost('sale_id');
        $lines  = json_decode((string) $this->request->getPost('lines_json'), true) ?: [];

        if (empty($lines)) {
            $this->session->setFlashdata('error', 'Select at least one item to return.');
            return redirect()->to('/sales/return/' . $saleId);
        }

        $returnId = model(SaleReturnModel::class)->createWithLines([
            'branch_id'   => $this->branchId,
            'sale_id'     => $saleId,
            'return_date' => date('Y-m-d'),
            'receipt_ref' => $this->request->getPost('receipt_ref'),
            'narrative'   => $this->request->getPost('narrative'),
        ], $lines, (int) $this->currentUser['id']);

        $this->session->setFlashdata($returnId ? 'success' : 'error', $returnId ? 'Return processed and stock updated.' : 'Return failed.');
        return redirect()->to('/sales/returns');
    }
}
