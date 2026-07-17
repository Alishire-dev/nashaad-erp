<?php

namespace App\Controllers;

use App\Models\CreditNoteModel;
use App\Models\SaleModel;
use App\Models\CustomerModel;

class CreditNotes extends BaseController
{
    public function index()
    {
        $this->requirePermission('sales', 'view');

        $data = [
            'title'       => 'Credit Notes',
            'creditNotes' => model(CreditNoteModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/credit_notes', $data)
            . view('layout/footer');
    }

    public function raiseForm()
    {
        $this->requirePermission('sales', 'add');

        $data = [
            'title'     => 'Raise Credit Note',
            'customers' => model(CustomerModel::class)->getForBranch($this->branchId),
        ];

        return view('layout/header', $data)
            . view('sales/credit_note_raise', $data)
            . view('layout/footer');
    }

    public function raise()
    {
        $this->requirePermission('sales', 'add');

        model(CreditNoteModel::class)->raiseManual(
            $this->branchId,
            $this->request->getPost('customer_id') ?: null,
            (float) $this->request->getPost('amount'),
            $this->request->getPost('note'),
            (int) $this->currentUser['id']
        );

        $this->session->setFlashdata('success', 'Credit note raised.');
        return redirect()->to('/sales/credit-notes');
    }

    /**
     * Reached from Cancelled Sales' Action menu — gets-or-creates the
     * credit note for that sale, then serves the PDF in the requested
     * format. Both "Thermal Credit Note" and "A4 Credit Note" hit this
     * same method with a different $format, sharing the same underlying
     * record rather than creating a duplicate each time.
     */
    public function fromCancelledSale($saleId, $format)
    {
        $this->requirePermission('sales', 'view');

        $sale = model(SaleModel::class)->getWithLines((int) $saleId);
        if (! $sale || $sale['status'] !== 'cancelled') {
            return redirect()->to('/sales/cancelled');
        }

        $creditNote = model(CreditNoteModel::class)->getOrCreateForSale($sale, (int) $this->currentUser['id']);
        $sale['customer_name'] = $sale['customer_name'] ?? 'WALK-IN';

        $branch = \Config\Database::connect()->table('branches')->where('id', $this->branchId)->get()->getRowArray() ?: [];

        $pdfLib = new \App\Libraries\SalesPdf();
        $content = $format === 'a4'
            ? $pdfLib->creditNoteA4($creditNote, $sale, $branch)
            : $pdfLib->creditNoteThermal($creditNote, $sale);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $creditNote['serial_no'] . '.pdf"')
            ->setBody($content);
    }
}
