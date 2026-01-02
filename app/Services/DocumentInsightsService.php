<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Smart Document Insights Service
 * 
 * Provides intelligent insights, warnings, and suggestions for documents
 * to help bookkeepers make better decisions
 */
class DocumentInsightsService
{
    /**
     * Get all insights for a document
     * 
     * @param Document $document
     * @return array
     */
    public function getInsights(Document $document): array
    {
        $insights = [];
        
        // Check for duplicates
        $duplicates = $this->checkDuplicates($document);
        if (!empty($duplicates)) {
            $insights['duplicates'] = $duplicates;
        }
        
        // Check for unusual amounts
        $unusualAmount = $this->checkUnusualAmount($document);
        if ($unusualAmount) {
            $insights['unusual_amount'] = $unusualAmount;
        }
        
        // Check for missing payment status on sales invoices
        $paymentWarning = $this->checkPaymentStatus($document);
        if ($paymentWarning) {
            $insights['payment_warning'] = $paymentWarning;
        }
        
        // Check for period mismatch
        $periodMismatch = $this->checkPeriodMismatch($document);
        if ($periodMismatch) {
            $insights['period_mismatch'] = $periodMismatch;
        }
        
        // Check for supplier consistency
        $supplierInconsistency = $this->checkSupplierConsistency($document);
        if ($supplierInconsistency) {
            $insights['supplier_inconsistency'] = $supplierInconsistency;
        }
        
        // Check for missing or unusual dates
        $dateWarning = $this->checkDateIssues($document);
        if ($dateWarning) {
            $insights['date_warning'] = $dateWarning;
        }
        
        return $insights;
    }
    
    /**
     * Check for duplicate or very similar documents
     * 
     * ENHANCED: Now checks invoice number first (most reliable), then amount+date+supplier
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkDuplicates(Document $document): ?array
    {
        // ENHANCEMENT: Check by invoice number first (most reliable)
        $invoiceNumber = $this->extractInvoiceNumber($document);
        if ($invoiceNumber) {
            $duplicates = Document::where('client_id', $document->client_id)
                ->where('id', '!=', $document->id)
                ->where('status', '!=', 'archived')
                ->where(function ($query) use ($invoiceNumber) {
                    // Check in OCR data JSON
                    $query->whereJsonContains('ocr_data->invoice->number', $invoiceNumber)
                        // Also check if stored in a dedicated field (if exists in future)
                        ->orWhere('invoice_number', $invoiceNumber);
                })
                ->get();
            
            if ($duplicates->isNotEmpty()) {
                return [
                    'type' => 'warning',
                    'title' => 'Duplicaat gevonden (factuurnummer)',
                    'message' => "Er is een document met hetzelfde factuurnummer ({$invoiceNumber}) gevonden.",
                    'documents' => $duplicates->map(fn($doc) => [
                        'id' => $doc->id,
                        'filename' => $doc->original_filename,
                        'date' => $doc->document_date?->format('d-m-Y'),
                        'amount' => '€' . number_format($doc->amount_incl ?? 0, 2, ',', '.'),
                        'supplier' => $doc->supplier_name,
                        'invoice_number' => $this->extractInvoiceNumber($doc),
                    ])->toArray(),
                ];
            }
        }
        
        // FALLBACK: Check by amount + date + supplier name (more reliable than just amount+date)
        if (!$document->amount_incl || !$document->document_date) {
            return null;
        }
        
        $query = Document::where('client_id', $document->client_id)
            ->where('id', '!=', $document->id)
            ->where('status', '!=', 'archived')
            ->where('amount_incl', $document->amount_incl)
            ->whereBetween('document_date', [
                Carbon::parse($document->document_date)->subDay(),
                Carbon::parse($document->document_date)->addDay(),
            ]);
        
        // ENHANCEMENT: Also check supplier name if available
        if ($document->supplier_name) {
            $query->where('supplier_name', $document->supplier_name);
        }
        
        $similar = $query->get();
        
        if ($similar->isEmpty()) {
            return null;
        }
        
        return [
            'type' => 'warning',
            'title' => 'Mogelijke duplicaat gevonden',
            'message' => "Er zijn {$similar->count()} document(en) met hetzelfde bedrag, datum" . 
                        ($document->supplier_name ? " en leverancier ({$document->supplier_name})" : "") . 
                        " gevonden.",
            'documents' => $similar->map(fn($doc) => [
                'id' => $doc->id,
                'filename' => $doc->original_filename,
                'date' => $doc->document_date?->format('d-m-Y'),
                'amount' => '€' . number_format($doc->amount_incl, 2, ',', '.'),
                'supplier' => $doc->supplier_name,
            ])->toArray(),
        ];
    }
    
    /**
     * Extract invoice number from document
     * 
     * @param Document $document
     * @return string|null
     */
    protected function extractInvoiceNumber(Document $document): ?string
    {
        // Check OCR data first
        $ocrData = $document->ocr_data ?? [];
        if (isset($ocrData['invoice']['number']) && !empty($ocrData['invoice']['number'])) {
            return trim((string) $ocrData['invoice']['number']);
        }
        
        // Check if stored in dedicated field (future enhancement)
        if (isset($document->invoice_number) && !empty($document->invoice_number)) {
            return trim((string) $document->invoice_number);
        }
        
        // Try to extract from filename
        if (preg_match('/(?:factuur|invoice|inv)[\s_-]*([A-Z0-9\-]+)/i', $document->original_filename, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Check if amount is unusual compared to historical data
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkUnusualAmount(Document $document): ?array
    {
        if (!$document->amount_incl || !$document->supplier_name) {
            return null;
        }
        
        // Get average amount from same supplier
        $historical = Document::where('client_id', $document->client_id)
            ->where('supplier_name', $document->supplier_name)
            ->where('id', '!=', $document->id)
            ->where('status', 'approved')
            ->whereNotNull('amount_incl')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('AVG(amount_incl) as avg_amount, COUNT(*) as count')
            ->first();
        
        $minHistory = config('bookkeeping.insights.min_history_for_deviation', 3);
        if (!$historical || $historical->count < $minHistory) {
            return null; // Not enough history
        }
        
        $avgAmount = (float) $historical->avg_amount;
        $currentAmount = (float) $document->amount_incl;
        
        // Flag if amount deviates significantly (from config)
        $multiplier = config('bookkeeping.insights.deviation_multiplier', 3);
        if ($currentAmount > ($avgAmount * $multiplier) || $currentAmount < ($avgAmount / $multiplier)) {
            return [
                'type' => 'info',
                'title' => 'Ongebruikelijk bedrag',
                'message' => "Dit bedrag ({$this->formatAmount($currentAmount)}) wijkt af van het gemiddelde bij deze leverancier ({$this->formatAmount($avgAmount)}).",
            ];
        }
        
        return null;
    }
    
    /**
     * Check payment status on sales invoices
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkPaymentStatus(Document $document): ?array
    {
        if ($document->document_type !== 'sales_invoice') {
            return null;
        }
        
        if (!$document->is_paid && $document->status === 'approved') {
            // Check if invoice is old (older than 30 days)
            $daysOld = $document->document_date 
                ? Carbon::parse($document->document_date)->diffInDays(now())
                : null;
            
            if ($daysOld && $daysOld > 30) {
                return [
                    'type' => 'warning',
                    'title' => 'Onbetaalde factuur (>30 dagen)',
                    'message' => "Deze verkoopfactuur is {$daysOld} dagen oud en nog niet gemarkeerd als betaald. Controleer de betalingsstatus.",
                ];
            } elseif (!$document->is_paid) {
                return [
                    'type' => 'info',
                    'title' => 'Betalingsstatus niet ingesteld',
                    'message' => 'Deze verkoopfactuur is nog niet gemarkeerd als betaald. Onbetaalde facturen worden niet meegenomen in BTW berekening (kasstelsel).',
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Check if document date is in correct VAT period
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkPeriodMismatch(Document $document): ?array
    {
        if (!$document->document_date) {
            return null;
        }
        
        $client = $document->client;
        if (!$client) {
            return null;
        }
        
        $workflowService = app(ClientTaxWorkflowService::class);
        $currentPeriod = $workflowService->getOrCreateCurrentPeriod($client);
        
        $docDate = Carbon::parse($document->document_date);
        $periodStart = Carbon::parse($currentPeriod->period_start);
        $periodEnd = Carbon::parse($currentPeriod->period_end);
        
        if ($docDate->lt($periodStart) || $docDate->gt($periodEnd)) {
            return [
                'type' => 'warning',
                'title' => 'Datum buiten huidige periode',
                'message' => "Documentdatum ({$docDate->format('d-m-Y')}) valt buiten de huidige BTW periode ({$periodStart->format('d-m-Y')} t/m {$periodEnd->format('d-m-Y')}).",
            ];
        }
        
        return null;
    }
    
    /**
     * Check for supplier name inconsistencies
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkSupplierConsistency(Document $document): ?array
    {
        if (!$document->supplier_name) {
            return null;
        }
        
        // Check if supplier name appears with different variations
        $variations = Document::where('client_id', $document->client_id)
            ->where('id', '!=', $document->id)
            ->where('supplier_name', '!=', $document->supplier_name)
            ->whereNotNull('supplier_name')
            ->select('supplier_name')
            ->distinct()
            ->get()
            ->filter(function ($doc) use ($document) {
                // Simple similarity check (same first word or very similar)
                $currentFirst = strtolower(explode(' ', $document->supplier_name)[0] ?? '');
                $otherFirst = strtolower(explode(' ', $doc->supplier_name)[0] ?? '');
                return $currentFirst === $otherFirst && $currentFirst !== '';
            })
            ->pluck('supplier_name')
            ->take(3);
        
        if ($variations->isNotEmpty()) {
            return [
                'type' => 'info',
                'title' => 'Mogelijke naamvariant leverancier',
                'message' => "Er zijn andere documenten met vergelijkbare leveranciersnamen: " . $variations->join(', ') . ". Controleer of dit dezelfde leverancier is.",
            ];
        }
        
        return null;
    }
    
    /**
     * Check for date issues (future dates, very old dates)
     * 
     * @param Document $document
     * @return array|null
     */
    protected function checkDateIssues(Document $document): ?array
    {
        if (!$document->document_date) {
            return [
                'type' => 'warning',
                'title' => 'Geen documentdatum',
                'message' => 'Dit document heeft geen datum. Dit is nodig voor BTW periode toewijzing.',
            ];
        }
        
        $docDate = Carbon::parse($document->document_date);
        $now = now();
        
        // Future date
        if ($docDate->isFuture()) {
            return [
                'type' => 'warning',
                'title' => 'Toekomstige datum',
                'message' => "Documentdatum ({$docDate->format('d-m-Y')}) ligt in de toekomst. Controleer of dit correct is.",
            ];
        }
        
        // Very old date (>2 years)
        if ($docDate->lt(now()->subYears(2))) {
            return [
                'type' => 'info',
                'title' => 'Oude documentdatum',
                'message' => "Documentdatum ({$docDate->format('d-m-Y')}) is meer dan 2 jaar geleden. Controleer of dit document bij de juiste periode hoort.",
            ];
        }
        
        return null;
    }
    
    /**
     * Format amount for display
     * 
     * @param float $amount
     * @return string
     */
    protected function formatAmount(float $amount): string
    {
        return '€' . number_format($amount, 2, ',', '.');
    }
    
    /**
     * Get summary of all insights for quick overview
     * 
     * @param Document $document
     * @return array
     */
    public function getSummary(Document $document): array
    {
        $insights = $this->getInsights($document);
        
        $summary = [
            'total' => count($insights),
            'warnings' => 0,
            'info' => 0,
        ];
        
        foreach ($insights as $insight) {
            if ($insight['type'] === 'warning') {
                $summary['warnings']++;
            } elseif ($insight['type'] === 'info') {
                $summary['info']++;
            }
        }
        
        return $summary;
    }
}


