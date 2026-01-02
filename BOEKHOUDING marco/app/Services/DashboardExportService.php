<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class DashboardExportService
{
    /**
     * Export documents to CSV
     */
    public function exportToCsv(?int $clientId = null, ?string $dateFrom = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Document::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        
        $documents = $query->orderBy('created_at', 'desc')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="documenten_export_' . now()->format('Y-m-d_His') . '.csv"',
        ];
        
        $callback = function() use ($documents) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Bestandsnaam',
                'Type',
                'Status',
                'Leverancier',
                'BTW Nummer',
                'Bedrag Excl.',
                'BTW Bedrag',
                'Bedrag Incl.',
                'BTW Tarief',
                'Document Datum',
                'Upload Datum',
                'Zekerheid',
                'Grootboek Code',
            ], ';');
            
            // Data rows
            foreach ($documents as $doc) {
                fputcsv($file, [
                    $doc->id,
                    $doc->original_filename,
                    $this->getDocumentTypeLabel($doc->document_type),
                    $this->getStatusLabel($doc->status),
                    $doc->supplier_name ?? '',
                    $doc->supplier_vat ?? '',
                    $doc->amount_excl ? number_format($doc->amount_excl, 2, ',', '.') : '',
                    $doc->amount_vat ? number_format($doc->amount_vat, 2, ',', '.') : '',
                    $doc->amount_incl ? number_format($doc->amount_incl, 2, ',', '.') : '',
                    $doc->vat_rate ?? '',
                    $doc->document_date ? $doc->document_date->format('d-m-Y') : '',
                    $doc->created_at->format('d-m-Y H:i'),
                    $doc->confidence_score ? round($doc->confidence_score) . '%' : '',
                    $doc->ledgerAccount ? $doc->ledgerAccount->code : '',
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    /**
     * Export dashboard summary to PDF
     */
    public function exportToPdf(?int $clientId = null, ?string $dateFrom = null): string
    {
        // Basic HTML for PDF generation
        // In a real implementation, you would use a package like DomPDF or Snappy
        
        $query = Document::query();
        
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        
        $total = $query->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $pending = (clone $query)->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count();
        $totalAmount = (clone $query)->where('status', 'approved')->sum('amount_incl');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Dashboard Rapport</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                h1 { color: #1f2937; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
                .stat { margin: 20px 0; padding: 15px; background: #f3f4f6; border-radius: 8px; }
                .stat-label { font-weight: bold; color: #6b7280; }
                .stat-value { font-size: 24px; color: #111827; margin-top: 5px; }
                .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <h1>Dashboard Rapport - MARCOFIC</h1>
            <p>Gegenereerd op: " . now()->format('d-m-Y H:i') . "</p>
            <p>Periode: " . ($dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') . ' tot ' . now()->format('d-m-Y') : 'Alle periodes') . "</p>
            
            <div class='stat'>
                <div class='stat-label'>Totaal Documenten</div>
                <div class='stat-value'>" . number_format($total) . "</div>
            </div>
            
            <div class='stat'>
                <div class='stat-label'>Goedgekeurd</div>
                <div class='stat-value'>" . number_format($approved) . " (" . ($total > 0 ? round(($approved / $total) * 100) : 0) . "%)</div>
            </div>
            
            <div class='stat'>
                <div class='stat-label'>In Behandeling</div>
                <div class='stat-value'>" . number_format($pending) . "</div>
            </div>
            
            <div class='stat'>
                <div class='stat-label'>Totaal Bedrag (Goedgekeurde Documenten)</div>
                <div class='stat-value'>€ " . number_format($totalAmount, 2, ',', '.') . "</div>
            </div>
            
            <div class='footer'>
                <p>Dit rapport is automatisch gegenereerd door MARCOFIC Dashboard Export.</p>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
    /**
     * Schedule periodic email report
     */
    public function scheduleEmailReport(int $clientId, string $frequency = 'weekly'): void
    {
        // This would integrate with Laravel's scheduler
        // For now, it's a placeholder for future implementation
    }
    
    protected function getDocumentTypeLabel(?string $type): string
    {
        return match($type) {
            'receipt' => 'Bonnetje',
            'purchase_invoice' => 'Inkoopfactuur',
            'bank_statement' => 'Bankafschrift',
            'sales_invoice' => 'Verkoopfactuur',
            default => 'Overig',
        };
    }
    
    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'In wachtrij',
            'ocr_processing' => 'Wordt verwerkt',
            'review_required' => 'In beoordeling',
            'approved' => 'Goedgekeurd',
            'archived' => 'Gearchiveerd',
            'task_opened' => 'Taak geopend',
            default => $status,
        };
    }
}

