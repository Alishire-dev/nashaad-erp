<?php

namespace App\Libraries;

/**
 * Generates Sales PDFs using the same vendored FPDF as PurchasePdf.
 * esc_pdf() comes from app/Helpers/pdf_helper.php, loaded explicitly
 * below rather than relying on autoloading (PHP doesn't autoload
 * namespaced functions the way it does classes).
 */
class SalesPdf
{
    public function __construct()
    {
        helper('pdf');
    }

    private const ORANGE = [232, 138, 46];

    /**
     * POS Invoice — thermal 80mm receipt, same real page-size approach
     * as PurchasePdf::thermal().
     */
    public function posInvoice(array $sale): string
    {
        $pdf = new \FPDF('P', 'mm', [80, 200]);
        $pdf->AddPage();
        $pdf->SetMargins(4, 4, 4);
        $pdf->SetAutoPageBreak(true, 4);

        $pdf->SetFont('Courier', 'B', 11);
        $pdf->Cell(0, 5, 'NASHAAD', 0, 1, 'C');
        $pdf->SetFont('Courier', '', 8);
        $pdf->Cell(0, 4, esc_pdf($sale['invoice_no']), 0, 1, 'C');
        $pdf->Cell(0, 4, esc_pdf(date('d-m-Y H:i', strtotime($sale['created_at'] ?? $sale['sale_date']))), 0, 1, 'C');
        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');

        $pdf->Cell(0, 4, 'Customer: ' . esc_pdf($sale['customer_name'] ?? 'WALK-IN'), 0, 1);
        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');

        foreach ($sale['lines'] as $line) {
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 4, esc_pdf($line['item_name']), 0, 1);
            $pdf->Cell(40, 4, number_format((float) $line['quantity'], 2) . ' x ' . number_format((float) $line['unit_price'], 2));
            $pdf->Cell(0, 4, number_format((float) $line['total_amount'], 2), 0, 1, 'R');
        }

        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');
        $pdf->SetFont('Courier', '', 8);
        $pdf->Cell(40, 4, 'Subtotal');
        $pdf->Cell(0, 4, number_format((float) $sale['subtotal'], 2), 0, 1, 'R');
        if ((float) $sale['discount_amt'] > 0) {
            $pdf->Cell(40, 4, 'Discount');
            $pdf->Cell(0, 4, '-' . number_format((float) $sale['discount_amt'], 2), 0, 1, 'R');
        }
        $pdf->SetFont('Courier', 'B', 9);
        $pdf->Cell(40, 5, 'GRAND TOTAL');
        $pdf->Cell(0, 5, number_format((float) $sale['grand_total'], 2), 0, 1, 'R');
        $pdf->SetFont('Courier', '', 8);
        $pdf->Cell(40, 4, 'Paid');
        $pdf->Cell(0, 4, number_format((float) $sale['amount_paid'], 2), 0, 1, 'R');

        $pdf->Ln(2);
        $pdf->Cell(0, 4, 'Thank you!', 0, 1, 'C');

        return $pdf->Output('S');
    }

    /**
     * A4 Invoice — branded "INVOICE" / "BILL TO" layout distinct from the
     * on-screen Sales Invoice Detail page, matching the original's
     * separate PDF template.
     */
    public function a4Invoice(array $sale, array $branch): string
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);
        [$r, $g, $b] = self::ORANGE;

        $pdf->SetFont('Arial', 'B', 22);
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Cell(95, 12, esc_pdf(strtoupper($branch['name'] ?? 'NASHAAD')), 0, 0);
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->Cell(95, 12, 'INVOICE', 0, 1, 'R');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(95, 5, '', 0, 0);
        if (! empty($branch['address'])) {
            $pdf->Cell(95, 5, esc_pdf($branch['address']), 0, 1, 'R');
        }
        $pdf->Cell(95, 5, '', 0, 0);
        $contact = trim(($branch['email'] ?? '') . (! empty($branch['phone']) ? '  Tel: ' . $branch['phone'] : ''));
        if ($contact !== '') {
            $pdf->Cell(95, 5, esc_pdf($contact), 0, 1, 'R');
        }
        $pdf->Ln(6);

        // BILL TO bar
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(120, 7, 'BILL TO:', 1, 0, 'L', true);
        $pdf->SetTextColor(0, 0, 0);

        // Meta box to the right (Sale Date/Due Date/Invoice No/LPO No)
        $pdf->SetFont('Arial', '', 9);
        $y = $pdf->GetY();
        $pdf->SetXY(140, $y);
        $pdf->Cell(25, 7, 'Sale Date:', 1);
        $pdf->Cell(35, 7, esc_pdf($sale['sale_date']), 1, 1);
        $pdf->SetX(140);
        $pdf->Cell(25, 7, 'Due Date:', 1);
        $pdf->Cell(35, 7, esc_pdf($sale['due_date'] ?? '-'), 1, 1);
        $pdf->SetX(140);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(25, 7, 'Invoice No:', 1);
        $pdf->Cell(35, 7, esc_pdf($sale['invoice_no']), 1, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX(140);
        $pdf->Cell(25, 7, 'LPO No:', 1);
        $pdf->Cell(35, 7, esc_pdf($sale['lpo_number'] ?? '-'), 1, 1);

        $pdf->SetXY(15, $y + 7);
        $pdf->Cell(120, 6, esc_pdf($sale['customer_name'] ?? 'WALK-IN'));
        $pdf->SetXY(15, $y + 13);
        $pdf->Cell(120, 6, esc_pdf($sale['customer_address'] ?? ''));
        $pdf->SetXY(15, $y + 19);
        $pdf->Cell(120, 6, 'Tel: ' . esc_pdf($sale['customer_phone'] ?? ''));
        $pdf->SetY($y + 30);

        // Items table
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(12, 7, 'No.', 1, 0, 'C', true);
        $pdf->Cell(78, 7, 'DESCRIPTION', 1, 0, 'L', true);
        $pdf->Cell(20, 7, 'QTY', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'UNIT', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'TAX', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'AMOUNT', 1, 1, 'R', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        foreach ($sale['lines'] as $i => $line) {
            $pdf->Cell(12, 6, (string) ($i + 1), 1, 0, 'C');
            $pdf->Cell(78, 6, esc_pdf($line['item_name']), 1, 0);
            $pdf->Cell(20, 6, number_format((float) $line['quantity'], 0), 1, 0, 'C');
            $pdf->Cell(20, 6, number_format((float) $line['unit_price'], 2), 1, 0, 'C');
            $pdf->Cell(20, 6, '0%', 1, 0, 'C');
            $pdf->Cell(30, 6, number_format((float) $line['total_amount'], 2), 1, 1, 'R');
        }
        $pdf->Ln(4);

        // Payment details (left) + Sub Total/Discount/Tax/Total (right)
        $y2 = $pdf->GetY();
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, 'PAYMENT DETAILS', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 6, 'Acc Name: ' . esc_pdf($branch['name'] ?? '-'), 0, 1);
        $pdf->Cell(90, 6, 'Till No: -', 0, 1);

        $pdf->SetXY(140, $y2);
        $pdf->Cell(25, 6, 'Sub Total', 0, 0, 'R');
        $pdf->Cell(35, 6, number_format((float) $sale['subtotal'], 2), 0, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(25, 6, 'Discount', 0, 0, 'R');
        $pdf->Cell(35, 6, number_format((float) $sale['discount_amt'], 2), 0, 1, 'R');
        $pdf->SetX(140);
        $pdf->Cell(25, 6, 'Tax', 0, 0, 'R');
        $pdf->Cell(35, 6, '0.00', 0, 1, 'R');
        $pdf->SetX(140);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 7, 'Total', 0, 0, 'R');
        $pdf->Cell(35, 7, number_format((float) $sale['grand_total'], 2), 0, 1, 'R');

        $pdf->Ln(6);
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, 'PAYMENTS', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(45, 6, 'Payment Date', 1);
        $pdf->Cell(45, 6, 'Pay Mode', 1);
        $pdf->Cell(50, 6, 'Trans Code', 1);
        $pdf->Cell(40, 6, 'Amount', 1, 1, 'R');

        if (empty($sale['payments'])) {
            $pdf->Cell(0, 8, 'No Payment Record Found!!', 1, 1, 'C');
        } else {
            foreach ($sale['payments'] as $p) {
                $pdf->Cell(45, 6, esc_pdf($p['payment_date']), 1);
                $pdf->Cell(45, 6, esc_pdf(ucfirst($p['payment_type'])), 1);
                $pdf->Cell(50, 6, '-', 1);
                $pdf->Cell(40, 6, number_format((float) $p['amount'], 2), 1, 1, 'R');
            }
        }

        $balance = (float) $sale['grand_total'] - (float) $sale['amount_paid'];
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(140, 7, '', 0, 0);
        $pdf->Cell(20, 7, 'Balance:', 0, 0, 'R');
        $pdf->Cell(30, 7, number_format($balance, 2), 0, 1, 'R');

        return $pdf->Output('S');
    }

    /**
     * Dispatch List — kitchen/delivery slip, just item names + qty, with
     * generous blank space for handwritten notes, matching the original.
     */
    public function dispatchList(array $sale): string
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);
        [$r, $g, $b] = self::ORANGE;

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Cell(0, 10, 'NASHAAD', 0, 1);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'DISPATCH LIST ' . date('d-m-Y'), 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 7, 'DISPATCH NO: ' . esc_pdf($sale['invoice_no']));
        $pdf->Cell(95, 7, 'CUSTOMER: ' . esc_pdf($sale['customer_name'] ?? 'WALK-IN'), 0, 1);
        $pdf->Ln(4);

        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 7, 'NO.', 1, 0, 'C', true);
        $pdf->Cell(140, 7, 'DETAILS', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'QTY', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        foreach ($sale['lines'] as $i => $line) {
            $pdf->Cell(15, 8, (string) ($i + 1), 1, 0, 'C');
            $pdf->Cell(140, 8, esc_pdf($line['item_name']), 1, 0);
            $pdf->Cell(35, 8, number_format((float) $line['quantity'], 0), 1, 1, 'C');
        }

        // Blank rows for handwritten additions
        for ($i = 0; $i < 5; $i++) {
            $pdf->Cell(15, 8, '', 1);
            $pdf->Cell(140, 8, '', 1);
            $pdf->Cell(35, 8, '', 1, 1);
        }

        $pdf->Ln(15);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 6, 'Prepared By: _______________________');
        $pdf->Cell(95, 6, 'Dispatched By: _______________________', 0, 1);
        $pdf->Ln(8);
        $pdf->Cell(95, 6, 'Signed By: _______________________');
        $pdf->Cell(95, 6, 'Transporting: _______________________', 0, 1);

        return $pdf->Output('S');
    }
}
