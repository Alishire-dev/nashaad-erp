<?php

namespace App\Libraries;

/**
 * Generates Purchase Order (LPO) and thermal-receipt PDFs using FPDF
 * (vendored at system/ThirdParty/FPDF, registered via classmap in
 * app/Config/Autoload.php — no Composer in this project, so no proper
 * PDF library was available until now).
 */
class PurchasePdf
{
    public function __construct()
    {
        helper('pdf');
    }

    private const ORANGE = [232, 138, 46]; // matches the app's #e88a2e brand color

    /**
     * @param array $purchase From PurchaseModel::getWithLines() — needs
     *                        supplier_name/phone/email, reference_no,
     *                        purchase_date, subtotal, grand_total, lines[].
     * @param array $branch   Row from `branches` (name, address, phone, email).
     */
    public function lpo(array $purchase, array $branch, bool $withPrices = true): string
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);

        $this->drawHeader($pdf, $branch, $purchase, $withPrices);
        $this->drawVendorShipTo($pdf, $purchase, $branch);

        if ($withPrices) {
            $this->drawPricedTable($pdf, $purchase);
        } else {
            $this->drawNoPriceTable($pdf, $purchase);
        }

        return $pdf->Output('S');
    }

    public function thermal(array $purchase): string
    {
        // 80mm roll width — matches the thermal receipt CSS trick already
        // used for Mnara's fee receipts (72mm print area within an 80mm
        // roll), just as a real PDF page size instead of @page CSS.
        $pdf = new \FPDF('P', 'mm', [80, 200]);
        $pdf->AddPage();
        $pdf->SetMargins(4, 4, 4);
        $pdf->SetAutoPageBreak(true, 4);

        $pdf->SetFont('Courier', 'B', 10);
        $pdf->Cell(0, 5, 'PURCHASE RECEIPT', 0, 1, 'C');
        $pdf->SetFont('Courier', '', 8);
        $pdf->Cell(0, 4, esc_pdf($purchase['reference_no'] ?? ('#' . $purchase['id'])), 0, 1, 'C');
        $pdf->Cell(0, 4, esc_pdf($purchase['purchase_date']), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');

        $pdf->Cell(0, 4, 'Supplier: ' . esc_pdf($purchase['supplier_name'] ?? '-'), 0, 1);
        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');

        foreach ($purchase['lines'] as $line) {
            $pdf->SetFont('Courier', '', 8);
            $pdf->Cell(0, 4, esc_pdf($line['item_name']), 0, 1);
            $pdf->Cell(40, 4, number_format((float) $line['quantity'], 2) . ' x ' . number_format((float) $line['cost_price'], 2));
            $pdf->Cell(0, 4, number_format((float) $line['total_amount'], 2), 0, 1, 'R');
        }

        $pdf->Cell(0, 4, str_repeat('-', 32), 0, 1, 'C');
        $pdf->SetFont('Courier', 'B', 9);
        $pdf->Cell(40, 5, 'GRAND TOTAL');
        $pdf->Cell(0, 5, number_format((float) $purchase['grand_total'], 2), 0, 1, 'R');

        return $pdf->Output('S');
    }

    private function drawHeader(\FPDF $pdf, array $branch, array $purchase, bool $withPrices): void
    {
        $pdf->SetFont('Arial', 'B', 20);
        [$r, $g, $b] = self::ORANGE;
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Cell(0, 10, esc_pdf(strtoupper($branch['name'] ?? 'PURCHASE ORDER')), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, 'PURCHASE ORDER', 0, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        if (! empty($branch['address'])) {
            $pdf->Cell(0, 5, esc_pdf($branch['address']), 0, 1, 'R');
        }
        $contact = trim(($branch['email'] ?? '') . (! empty($branch['phone']) ? '   Tel: ' . $branch['phone'] : ''));
        if ($contact !== '') {
            $pdf->Cell(0, 5, esc_pdf($contact), 0, 1, 'R');
        }
        $pdf->Ln(4);

        // DATE / LPO No box
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(95, 7, 'DATE', 1, 0, 'L', true);
        $pdf->Cell(95, 7, 'LPO No', 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(95, 7, esc_pdf(date('M-d-Y', strtotime($purchase['purchase_date']))), 1, 0);
        $pdf->Cell(95, 7, esc_pdf($purchase['reference_no'] ?? ('PUR_' . str_pad((string) $purchase['id'], 4, '0', STR_PAD_LEFT))), 1, 1);
        $pdf->Ln(4);
    }

    private function drawVendorShipTo(\FPDF $pdf, array $purchase, array $branch): void
    {
        [$r, $g, $b] = self::ORANGE;
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(95, 7, 'VENDOR', 1, 0, 'L', true);
        $pdf->Cell(95, 7, 'SHIP TO', 1, 1, 'L', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $vendorLines = array_filter([
            $purchase['supplier_name'] ?? '-',
            ! empty($purchase['supplier_phone']) ? 'Tel: ' . $purchase['supplier_phone'] : null,
        ]);
        $shipLines = array_filter([
            $branch['name'] ?? '-',
            $branch['address'] ?? null,
        ]);

        $max = max(count($vendorLines), count($shipLines));
        $vendorLines = array_values($vendorLines);
        $shipLines   = array_values($shipLines);
        for ($i = 0; $i < $max; $i++) {
            $pdf->Cell(95, 6, esc_pdf($vendorLines[$i] ?? ''), 1, 0);
            $pdf->Cell(95, 6, esc_pdf($shipLines[$i] ?? ''), 1, 1);
        }
        $pdf->Ln(4);
    }

    private function drawPricedTable(\FPDF $pdf, array $purchase): void
    {
        [$r, $g, $b] = self::ORANGE;
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(75, 7, 'DESCRIPTION', 1, 0, 'L', true);
        $pdf->Cell(30, 7, 'UNIT PRICE', 1, 0, 'R', true);
        $pdf->Cell(25, 7, 'QTY', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'TOTAL COST', 1, 1, 'R', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $totalQty = 0;
        foreach ($purchase['lines'] as $i => $line) {
            $pdf->Cell(10, 6, (string) ($i + 1), 1, 0, 'C');
            $pdf->Cell(75, 6, esc_pdf($line['item_name']), 1, 0);
            $pdf->Cell(30, 6, number_format((float) $line['cost_price'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, number_format((float) $line['quantity'], 0), 1, 0, 'C');
            $pdf->Cell(50, 6, number_format((float) $line['total_amount'], 2), 1, 1, 'R');
            $totalQty += (float) $line['quantity'];
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(85, 7, 'Total', 1, 0, 'C', true);
        $pdf->Cell(30, 7, '', 1, 0, '', true);
        $pdf->Cell(25, 7, number_format($totalQty, 0), 1, 0, 'C', true);
        $pdf->Cell(50, 7, number_format((float) $purchase['grand_total'], 2), 1, 1, 'R', true);

        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(140, 6, '', 0, 0);
        $pdf->Cell(30, 6, 'Subtotal', 0, 0, 'R');
        $pdf->Cell(20, 6, number_format((float) $purchase['subtotal'], 2), 0, 1, 'R');
        $pdf->Cell(140, 6, '', 0, 0);
        $pdf->Cell(30, 6, 'Tax Amt.', 0, 0, 'R');
        $pdf->Cell(20, 6, '0.00', 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(140, 7, '', 0, 0);
        $pdf->Cell(30, 7, 'Grand Total', 0, 0, 'R');
        $pdf->Cell(20, 7, number_format((float) $purchase['grand_total'], 2), 0, 1, 'R');
    }

    private function drawNoPriceTable(\FPDF $pdf, array $purchase): void
    {
        [$r, $g, $b] = self::ORANGE;
        $pdf->SetFillColor($r, $g, $b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(140, 7, 'DESCRIPTION', 1, 0, 'L', true);
        $pdf->Cell(30, 7, 'QTY', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        foreach ($purchase['lines'] as $i => $line) {
            $pdf->Cell(10, 6, (string) ($i + 1), 1, 0, 'C');
            $pdf->Cell(140, 6, esc_pdf($line['item_name']), 1, 0);
            $pdf->Cell(30, 6, number_format((float) $line['quantity'], 0), 1, 1, 'C');
        }
    }
}
