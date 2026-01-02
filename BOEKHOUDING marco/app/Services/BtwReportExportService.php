<?php

namespace App\Services;

use App\Models\BtwReport;
use Illuminate\Support\Facades\Storage;

class BtwReportExportService
{
    /**
     * Export BTW report to XML format for Dutch tax authorities
     * 
     * @param BtwReport $report
     * @return string Path to generated XML file
     */
    public function exportToXml(BtwReport $report): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><BtwAangifte/>');
        
        // Add report metadata
        $xml->addChild('Periode', $report->period);
        $xml->addChild('ClientId', $report->client_id);
        $xml->addChild('GeneratedAt', now()->toIso8601String());
        
        // Add rubrieken (Dutch VAT categories)
        $rubrieken = $xml->addChild('Rubrieken');
        
        $totals = $report->totals ?? [];
        
        // Rubriek 1a: Leveringen/diensten belast met hoog tarief (21%)
        if (isset($totals['1a'])) {
            $r1a = $rubrieken->addChild('Rubriek1a');
            $r1a->addChild('Omzet', number_format($totals['1a']['amount'] ?? 0, 2, '.', ''));
            $r1a->addChild('BTW', number_format($totals['1a']['vat'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 1b: Leveringen/diensten belast met laag tarief (9%)
        if (isset($totals['1b'])) {
            $r1b = $rubrieken->addChild('Rubriek1b');
            $r1b->addChild('Omzet', number_format($totals['1b']['amount'] ?? 0, 2, '.', ''));
            $r1b->addChild('BTW', number_format($totals['1b']['vat'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 1c: Leveringen/diensten belast met overige tarieven
        if (isset($totals['1c'])) {
            $r1c = $rubrieken->addChild('Rubriek1c');
            $r1c->addChild('Omzet', number_format($totals['1c']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 2a: Privégebruik
        if (isset($totals['2a'])) {
            $r2a = $rubrieken->addChild('Rubriek2a');
            $r2a->addChild('BTW', number_format($totals['2a']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 3a: Leveringen naar landen binnen de EU (intracommunautair)
        if (isset($totals['3a'])) {
            $r3a = $rubrieken->addChild('Rubriek3a');
            $r3a->addChild('Omzet', number_format($totals['3a']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 3b: Leveringen naar landen buiten de EU (export)
        if (isset($totals['3b'])) {
            $r3b = $rubrieken->addChild('Rubriek3b');
            $r3b->addChild('Omzet', number_format($totals['3b']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 4a: Leveringen van binnen de EU (intracommunautair)
        if (isset($totals['4a'])) {
            $r4a = $rubrieken->addChild('Rubriek4a');
            $r4a->addChild('Omzet', number_format($totals['4a']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 4b: Leveringen van buiten de EU (import)
        if (isset($totals['4b'])) {
            $r4b = $rubrieken->addChild('Rubriek4b');
            $r4b->addChild('Omzet', number_format($totals['4b']['amount'] ?? 0, 2, '.', ''));
        }
        
        // Rubriek 5b: Voorbelasting
        if (isset($totals['5b'])) {
            $r5b = $rubrieken->addChild('Rubriek5b');
            $r5b->addChild('BTW', number_format($totals['5b']['vat'] ?? 0, 2, '.', ''));
        }
        
        // Calculate totals
        $totaalBTWVerschuldigd = ($totals['1a']['vat'] ?? 0) + 
                                  ($totals['1b']['vat'] ?? 0) + 
                                  ($totals['2a']['amount'] ?? 0);
        
        $voorbelasting = $totals['5b']['vat'] ?? 0;
        $teBetalenTerug = $totaalBTWVerschuldigd - $voorbelasting;
        
        $samenvatting = $xml->addChild('Samenvatting');
        $samenvatting->addChild('TotaalBTWVerschuldigd', number_format($totaalBTWVerschuldigd, 2, '.', ''));
        $samenvatting->addChild('Voorbelasting', number_format($voorbelasting, 2, '.', ''));
        $samenvatting->addChild('TeBetalen', number_format(max(0, $teBetalenTerug), 2, '.', ''));
        $samenvatting->addChild('TerugtVragen', number_format(max(0, -$teBetalenTerug), 2, '.', ''));
        
        // Format and save
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        $filename = "btw-report-{$report->period}-client-{$report->client_id}.xml";
        $path = "btw-reports/{$filename}";
        
        Storage::put($path, $dom->saveXML());
        
        return $path;
    }
    
    /**
     * Export to PDF format (for client review)
     */
    public function exportToPdf(BtwReport $report): string
    {
        // TODO: Implement PDF export using TCPDF or similar
        // For now, return a simple HTML representation
        
        $html = view('btw-reports.pdf', ['report' => $report])->render();
        
        $filename = "btw-report-{$report->period}-client-{$report->client_id}.html";
        $path = "btw-reports/{$filename}";
        
        Storage::put($path, $html);
        
        return $path;
    }
    
    /**
     * Calculate BTW report totals from approved documents
     * Only includes PAID sales invoices for taxable revenue
     */
    public function calculateTotals(BtwReport $report): array
    {
        $clientId = $report->client_id;
        
        // Parse period (e.g., "2024-Q1" or "2024-01")
        $period = $report->period;
        $startDate = null;
        $endDate = null;
        
        if (preg_match('/^(\d{4})-Q([1-4])$/', $period, $matches)) {
            // Quarterly period
            $year = (int) $matches[1];
            $quarter = (int) $matches[2];
            $startDate = \Carbon\Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
            $endDate = $startDate->copy()->addMonths(3)->subDay()->endOfDay();
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            // Monthly period
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        } else {
            // Default to current month
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        
        // Get SALES INVOICES - only PAID ones are taxable
        $salesInvoices = \App\Models\Document::where('client_id', $clientId)
            ->where('document_type', 'sales_invoice')
            ->where('is_paid', true) // Only paid invoices are taxable
            ->where('status', 'approved')
            ->whereBetween('document_date', [$startDate, $endDate])
            ->whereNotNull('amount_excl')
            ->whereNotNull('amount_vat')
            ->get();
        
        // Get PURCHASE INVOICES (for input VAT - Rubriek 5b)
        $purchaseInvoices = \App\Models\Document::where('client_id', $clientId)
            ->where('document_type', '!=', 'sales_invoice')
            ->where('status', 'approved')
            ->whereBetween('document_date', [$startDate, $endDate])
            ->whereNotNull('amount_vat')
            ->get();
        
        // Initialize totals
        $totals = [
            '1a' => ['amount' => 0, 'vat' => 0], // 21% (high rate)
            '1b' => ['amount' => 0, 'vat' => 0], // 9% (low rate)
            '1c' => ['amount' => 0], // Other rates
            '2a' => ['amount' => 0], // Private use
            '3a' => ['amount' => 0], // EU sales
            '3b' => ['amount' => 0], // Export
            '4a' => ['amount' => 0], // EU purchases
            '4b' => ['amount' => 0], // Import
            '5b' => ['vat' => 0], // Input VAT (from purchase invoices)
        ];
        
        // Process SALES invoices (only paid ones)
        foreach ($salesInvoices as $invoice) {
            $vatRate = $invoice->vat_rate ?? '21';
            $amountExcl = (float) ($invoice->amount_excl ?? 0);
            $vatAmount = (float) ($invoice->amount_vat ?? 0);
            
            // Categorize by VAT rate
            if ($vatRate === '21') {
                $totals['1a']['amount'] += $amountExcl;
                $totals['1a']['vat'] += $vatAmount;
            } elseif ($vatRate === '9') {
                $totals['1b']['amount'] += $amountExcl;
                $totals['1b']['vat'] += $vatAmount;
            } elseif ($vatRate === '0') {
                $totals['1c']['amount'] += $amountExcl;
            } else {
                // Other rates go to 1c
                $totals['1c']['amount'] += $amountExcl;
            }
        }
        
        // Process PURCHASE invoices (for input VAT)
        foreach ($purchaseInvoices as $invoice) {
            $vatAmount = (float) ($invoice->amount_vat ?? 0);
            $totals['5b']['vat'] += $vatAmount;
        }
        
        // Round all values to 2 decimals
        foreach ($totals as $key => $value) {
            if (isset($value['amount'])) {
                $totals[$key]['amount'] = round($value['amount'], 2);
            }
            if (isset($value['vat'])) {
                $totals[$key]['vat'] = round($value['vat'], 2);
            }
        }
        
        return $totals;
    }
}

