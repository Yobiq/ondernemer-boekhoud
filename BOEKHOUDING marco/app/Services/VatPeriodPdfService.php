<?php

namespace App\Services;

use App\Models\VatPeriod;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class VatPeriodPdfService
{
    private VatCalculatorService $vatCalculator;

    public function __construct(VatCalculatorService $vatCalculator)
    {
        $this->vatCalculator = $vatCalculator;
    }

    /**
     * Create DomPDF instance with configured options
     */
    private function createPdf(string $html): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('enableCssFloat', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Generate PDF for VAT period
     */
    public function generate(VatPeriod $period): string
    {
        $totals = $this->vatCalculator->calculatePeriodTotals($period);
        
        $html = View::make('filament.btw-reports.vat-period-pdf', [
            'period' => $period,
            'totals' => $totals,
            'vatCalculator' => $this->vatCalculator,
        ])->render();

        $pdf = $this->createPdf($html);

        $filename = "btw-rapport-{$period->period_string}-client-{$period->client_id}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $filename); // Sanitize filename
        $path = "btw-reports/{$filename}";

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Stream PDF to browser
     */
    public function stream(VatPeriod $period)
    {
        $totals = $this->vatCalculator->calculatePeriodTotals($period);
        
        $html = View::make('filament.btw-reports.vat-period-pdf', [
            'period' => $period,
            'totals' => $totals,
            'vatCalculator' => $this->vatCalculator,
        ])->render();

        $pdf = $this->createPdf($html);

        $filename = "btw-rapport-{$period->period_string}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $filename);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Download PDF
     */
    public function download(VatPeriod $period)
    {
        $totals = $this->vatCalculator->calculatePeriodTotals($period);
        
        $html = View::make('filament.btw-reports.vat-period-pdf', [
            'period' => $period,
            'totals' => $totals,
            'vatCalculator' => $this->vatCalculator,
        ])->render();

        $pdf = $this->createPdf($html);

        $filename = "btw-rapport-{$period->period_string}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $filename);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}


