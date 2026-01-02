<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Client;
use App\Models\VatPeriod;
use App\Models\LedgerAccount;
use App\Services\VatCalculatorService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExcelExportService
{
    protected VatCalculatorService $vatCalculator;
    
    public function __construct(VatCalculatorService $vatCalculator)
    {
        $this->vatCalculator = $vatCalculator;
    }
    
    /**
     * Export documents to Excel
     * 
     * @param \Illuminate\Database\Eloquent\Collection|array $documents
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportDocuments($documents, string $filename = 'documenten-export.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Documenten');
        
        // Headers
        $headers = [
            'ID', 'Klant', 'Type', 'Leverancier', 'Factuurdatum', 
            'Bedrag Excl. BTW', 'BTW Bedrag', 'Bedrag Incl. BTW', 
            'BTW Tarief', 'BTW Code', 'BTW Rubriek',
            'Grootboekrekening', 'Status', 'OCR Confidence', 
            'Geüpload Op', 'Betaald', 'Betaald Op'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        // Calculate last column letter from header count
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:' . $lastColumn . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'] // MARCOFIC green
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        
        // Data rows
        $row = 2;
        foreach ($documents as $document) {
            $sheet->setCellValue('A' . $row, $document->id);
            $sheet->setCellValue('B' . $row, $document->client->name ?? '');
            $sheet->setCellValue('C' . $row, $this->getDocumentTypeLabel($document->document_type));
            $sheet->setCellValue('D' . $row, $document->supplier_name ?? '');
            $sheet->setCellValue('E' . $row, $document->document_date ? $document->document_date->format('d-m-Y') : '');
            $sheet->setCellValue('F' . $row, $document->amount_excl ?? 0);
            $sheet->setCellValue('G' . $row, $document->amount_vat ?? 0);
            $sheet->setCellValue('H' . $row, $document->amount_incl ?? 0);
            $sheet->setCellValue('I' . $row, $document->vat_rate ? $document->vat_rate . '%' : '');
            $sheet->setCellValue('J' . $row, $document->vat_code ?? '');
            $sheet->setCellValue('K' . $row, $document->vat_rubriek ?? '');
            $sheet->setCellValue('L' . $row, $document->ledgerAccount ? "{$document->ledgerAccount->code} - {$document->ledgerAccount->description}" : '');
            $sheet->setCellValue('M' . $row, $this->getStatusLabel($document->status));
            $sheet->setCellValue('N' . $row, $document->confidence_score ? $document->confidence_score . '%' : '');
            $sheet->setCellValue('O' . $row, $document->created_at->format('d-m-Y H:i'));
            $sheet->setCellValue('P' . $row, $document->is_paid ? 'Ja' : 'Nee');
            $sheet->setCellValue('Q' . $row, $document->paid_at ? $document->paid_at->format('d-m-Y') : '');
            
            // Format amounts as currency
            $sheet->getStyle('F' . $row . ':H' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        return $this->downloadSpreadsheet($spreadsheet, $filename);
    }
    
    /**
     * Export BTW period to Excel with detailed breakdown
     */
    public function exportVatPeriod(VatPeriod $period, string $filename = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $filename ?? "btw-aangifte-{$period->period_string}-{$period->client->name}.xlsx";
        
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Overzicht
        $overzichtSheet = $spreadsheet->getActiveSheet();
        $overzichtSheet->setTitle('BTW Overzicht');
        $this->createVatOverviewSheet($overzichtSheet, $period);
        
        // Sheet 2: Documenten per Rubriek
        $rubriekenSheet = $spreadsheet->createSheet();
        $rubriekenSheet->setTitle('Documenten per Rubriek');
        $this->createRubriekenSheet($rubriekenSheet, $period);
        
        // Sheet 3: Grootboek Overzicht
        $grootboekSheet = $spreadsheet->createSheet();
        $grootboekSheet->setTitle('Grootboek Overzicht');
        $this->createGrootboekSheet($grootboekSheet, $period);
        
        // Sheet 4: Alle Documenten
        $documentenSheet = $spreadsheet->createSheet();
        $documentenSheet->setTitle('Alle Documenten');
        $this->createDocumentsSheet($documentenSheet, $period);
        
        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);
        
        return $this->downloadSpreadsheet($spreadsheet, $filename);
    }
    
    /**
     * Export Grootboek (Chart of Accounts) to Excel
     */
    public function exportGrootboek(string $filename = 'grootboek-rekenschema.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Grootboek Rekenschema');
        
        // Headers
        $headers = ['Code', 'Omschrijving', 'Type', 'BTW Standaard', 'Actief', 'Aantal Documenten', 'Totaal Bedrag'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $this->styleHeaders($sheet, 'A1:G1');
        
        // Get all ledger accounts with document counts
        $accounts = LedgerAccount::withCount('documents')
            ->withSum('documents', 'amount_incl')
            ->orderBy('code')
            ->get();
        
        $row = 2;
        foreach ($accounts as $account) {
            $sheet->setCellValue('A' . $row, $account->code);
            $sheet->setCellValue('B' . $row, $account->description);
            $sheet->setCellValue('C' . $row, $account->type === 'balans' ? 'Balans' : 'Winst & Verlies');
            $sheet->setCellValue('D' . $row, $account->vat_default ? $account->vat_default . '%' : '');
            $sheet->setCellValue('E' . $row, $account->active ? 'Ja' : 'Nee');
            $sheet->setCellValue('F' . $row, $account->documents_count ?? 0);
            $sheet->setCellValue('G' . $row, $account->documents_sum_amount_incl ?? 0);
            
            // Format amount
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $sheet->freezePane('A2');
        
        return $this->downloadSpreadsheet($spreadsheet, $filename);
    }
    
    /**
     * Create BTW Overview sheet
     */
    protected function createVatOverviewSheet($sheet, VatPeriod $period): void
    {
        $totals = $this->vatCalculator->calculatePeriodTotals($period);
        
        // Title
        $sheet->setCellValue('A1', 'BTW Aangifte Overzicht');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Period info
        $row = 3;
        $sheet->setCellValue('A' . $row, 'Klant:');
        $sheet->setCellValue('B' . $row, $period->client->name);
        $row++;
        $sheet->setCellValue('A' . $row, 'Periode:');
        $sheet->setCellValue('B' . $row, $period->period_string);
        $row++;
        $sheet->setCellValue('A' . $row, 'Status:');
        $sheet->setCellValue('B' . $row, $this->getPeriodStatusLabel($period->status));
        $row += 2;
        
        // Rubrieken table
        $sheet->setCellValue('A' . $row, 'Rubriek');
        $sheet->setCellValue('B' . $row, 'Omschrijving');
        $sheet->setCellValue('C' . $row, 'Grondslag');
        $sheet->setCellValue('D' . $row, 'BTW Bedrag');
        
        $this->styleHeaders($sheet, 'A' . $row . ':D' . $row);
        $row++;
        
        $rubrieken = [
            '1a' => 'Leveringen/diensten belast met hoog tarief (21%)',
            '1b' => 'Leveringen/diensten belast met laag tarief (9%)',
            '1c' => 'Leveringen/diensten belast met overige tarieven',
            '2a' => 'Voorbelasting (inkopen 21%)',
            '2b' => 'Voorbelasting (inkopen 9%)',
            '4a' => 'Voorbelasting EU',
            '4b' => 'Voorbelasting buiten EU',
            '5b' => 'Totaal verschuldigd/te ontvangen',
        ];
        
        foreach ($rubrieken as $code => $description) {
            $amount = $totals[$code]['amount'] ?? 0;
            $vat = $totals[$code]['vat'] ?? 0;
            
            $sheet->setCellValue('A' . $row, $code);
            $sheet->setCellValue('B' . $row, $description);
            $sheet->setCellValue('C' . $row, $amount);
            $sheet->setCellValue('D' . $row, $vat);
            
            $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row++;
        }
        
        // Totals
        $row++;
        $sheet->setCellValue('B' . $row, 'TOTAAL:');
        $sheet->setCellValue('C' . $row, array_sum(array_column($totals, 'amount')));
        $sheet->setCellValue('D' . $row, array_sum(array_column($totals, 'vat')));
        
        $sheet->getStyle('B' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]],
        ]);
        
        $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.00');
        
        // Auto-size
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create Rubrieken breakdown sheet
     */
    protected function createRubriekenSheet($sheet, VatPeriod $period): void
    {
        $documents = $period->documents()
            ->where('status', 'approved')
            ->with(['ledgerAccount', 'client'])
            ->get();
        
        // Group by rubriek
        $byRubriek = $documents->groupBy(function ($doc) {
            return $doc->vat_rubriek ?? $this->vatCalculator->calculateRubriek($doc);
        });
        
        $row = 1;
        foreach ($byRubriek as $rubriek => $docs) {
            $sheet->setCellValue('A' . $row, "Rubriek {$rubriek}");
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ]);
            $row++;
            
            // Headers
            $headers = ['Datum', 'Leverancier', 'Bedrag Excl.', 'BTW', 'Bedrag Incl.', 'Grootboek', 'BTW Code'];
            $sheet->fromArray($headers, null, 'A' . $row);
            $this->styleHeaders($sheet, 'A' . $row . ':G' . $row);
            $row++;
            
            foreach ($docs as $doc) {
                $sheet->setCellValue('A' . $row, $doc->document_date ? $doc->document_date->format('d-m-Y') : '');
                $sheet->setCellValue('B' . $row, $doc->supplier_name ?? '');
                $sheet->setCellValue('C' . $row, $doc->amount_excl ?? 0);
                $sheet->setCellValue('D' . $row, $doc->amount_vat ?? 0);
                $sheet->setCellValue('E' . $row, $doc->amount_incl ?? 0);
                $sheet->setCellValue('F' . $row, $doc->ledgerAccount ? "{$doc->ledgerAccount->code} - {$doc->ledgerAccount->description}" : '');
                $sheet->setCellValue('G' . $row, $doc->vat_code ?? '');
                
                $sheet->getStyle('C' . $row . ':E' . $row)->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                
                $row++;
            }
            
            // Subtotal
            $sheet->setCellValue('B' . $row, 'Subtotaal:');
            $sheet->setCellValue('C' . $row, $docs->sum('amount_excl'));
            $sheet->setCellValue('D' . $row, $docs->sum('amount_vat'));
            $sheet->setCellValue('E' . $row, $docs->sum('amount_incl'));
            
            $sheet->getStyle('B' . $row . ':E' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            
            $sheet->getStyle('C' . $row . ':E' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row += 2;
        }
        
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create Grootboek overview sheet
     */
    protected function createGrootboekSheet($sheet, VatPeriod $period): void
    {
        $documents = $period->documents()
            ->where('status', 'approved')
            ->with('ledgerAccount')
            ->get();
        
        // Group by ledger account
        $byAccount = $documents->groupBy('ledger_account_id');
        
        $headers = ['Grootboek Code', 'Omschrijving', 'Aantal', 'Totaal Excl.', 'Totaal BTW', 'Totaal Incl.'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, 'A1:F1');
        
        $row = 2;
        foreach ($byAccount as $accountId => $docs) {
            $account = $docs->first()->ledgerAccount;
            
            $sheet->setCellValue('A' . $row, $account ? $account->code : '');
            $sheet->setCellValue('B' . $row, $account ? $account->description : 'Onbekend');
            $sheet->setCellValue('C' . $row, $docs->count());
            $sheet->setCellValue('D' . $row, $docs->sum('amount_excl'));
            $sheet->setCellValue('E' . $row, $docs->sum('amount_vat'));
            $sheet->setCellValue('F' . $row, $docs->sum('amount_incl'));
            
            $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row++;
        }
        
        // Totals
        $sheet->setCellValue('B' . $row, 'TOTAAL:');
        $sheet->setCellValue('C' . $row, $documents->count());
        $sheet->setCellValue('D' . $row, $documents->sum('amount_excl'));
        $sheet->setCellValue('E' . $row, $documents->sum('amount_vat'));
        $sheet->setCellValue('F' . $row, $documents->sum('amount_incl'));
        
        $sheet->getStyle('B' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]],
        ]);
        
        $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.00');
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create detailed documents sheet
     */
    protected function createDocumentsSheet($sheet, VatPeriod $period): void
    {
        $documents = $period->documents()
            ->where('status', 'approved')
            ->with(['ledgerAccount', 'client'])
            ->orderBy('document_date')
            ->get();
        
        $headers = [
            'Datum', 'Type', 'Leverancier', 'Bedrag Excl.', 'BTW', 'Bedrag Incl.',
            'BTW Tarief', 'BTW Code', 'BTW Rubriek', 'Grootboek', 'Status'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaders($sheet, 'A1:K1');
        
        $row = 2;
        foreach ($documents as $doc) {
            $sheet->setCellValue('A' . $row, $doc->document_date ? $doc->document_date->format('d-m-Y') : '');
            $sheet->setCellValue('B' . $row, $this->getDocumentTypeLabel($doc->document_type));
            $sheet->setCellValue('C' . $row, $doc->supplier_name ?? '');
            $sheet->setCellValue('D' . $row, $doc->amount_excl ?? 0);
            $sheet->setCellValue('E' . $row, $doc->amount_vat ?? 0);
            $sheet->setCellValue('F' . $row, $doc->amount_incl ?? 0);
            $sheet->setCellValue('G' . $row, $doc->vat_rate ? $doc->vat_rate . '%' : '');
            $sheet->setCellValue('H' . $row, $doc->vat_code ?? '');
            $sheet->setCellValue('I' . $row, $doc->vat_rubriek ?? '');
            $sheet->setCellValue('J' . $row, $doc->ledgerAccount ? "{$doc->ledgerAccount->code} - {$doc->ledgerAccount->description}" : '');
            $sheet->setCellValue('K' . $row, $this->getStatusLabel($doc->status));
            
            $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $row++;
        }
        
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $sheet->freezePane('A2');
    }
    
    /**
     * Style header row
     */
    protected function styleHeaders($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
    
    /**
     * Download spreadsheet as file
     */
    protected function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    
    /**
     * Helper methods
     */
    protected function getDocumentTypeLabel(?string $type): string
    {
        return match($type) {
            'receipt' => 'Bonnetje',
            'purchase_invoice' => 'Inkoopfactuur',
            'sales_invoice' => 'Verkoopfactuur',
            'bank_statement' => 'Bankafschrift',
            default => 'Overig',
        };
    }
    
    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'In Wachtrij',
            'ocr_processing' => 'Wordt Verwerkt',
            'review_required' => 'In Beoordeling',
            'approved' => 'Goedgekeurd',
            'archived' => 'Gearchiveerd',
            default => $status,
        };
    }
    
    protected function getPeriodStatusLabel(string $status): string
    {
        return match($status) {
            'open' => 'Open',
            'voorbereid' => 'Voorbereid',
            'ingediend' => 'Ingediend',
            'afgesloten' => 'Afgesloten',
            default => $status,
        };
    }
}

