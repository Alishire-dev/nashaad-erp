<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\ChartOfAccountModel;

class Invoices extends BaseController
{
    public function index()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title'    => 'Invoices List',
            'invoices' => model(InvoiceModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('invoices/list', $data)
            . view('layout/footer');
    }

    public function addForm()
    {
        $this->requirePermission('sales', 'add');

        $data = [
            'title'     => 'New Invoice',
            'customers' => model(CustomerModel::class)->getForBranch($this->branchId),
            'accounts'  => model(ChartOfAccountModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('invoices/add', $data)
            . view('layout/footer');
    }

    public function add()
    {
        $this->requirePermission('sales', 'add');

        $id = model(InvoiceModel::class)->createInvoice([
            'branch_id'          => $this->branchId,
            'customer_id'        => (int) $this->request->getPost('customer_id'),
            'invoice_no'         => $this->request->getPost('invoice_no'),
            'invoice_date'       => $this->request->getPost('invoice_date') ?: date('Y-m-d'),
            'due_date'           => $this->request->getPost('due_date') ?: null,
            'note'               => $this->request->getPost('note'),
            'revenue_account_id' => $this->request->getPost('revenue_account_id') ?: null,
            'grand_total'        => (float) $this->request->getPost('grand_total'),
        ], (int) $this->currentUser['id']);

        $this->session->setFlashdata($id ? 'success' : 'error', $id ? 'Invoice created.' : 'Could not create invoice.');
        return redirect()->to('/invoices');
    }
}
