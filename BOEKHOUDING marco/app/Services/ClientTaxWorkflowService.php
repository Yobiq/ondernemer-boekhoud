<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\VatPeriod;
use App\Services\VatCalculatorService;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ClientTaxWorkflowService
{
    protected VatCalculatorService $vatCalculator;

    public function __construct(VatCalculatorService $vatCalculator)
    {
        $this->vatCalculator = $vatCalculator;
    }

    /**
     * Get workflow status for a client and period
     */
    public function getWorkflowStatus(Client $client, ?VatPeriod $period = null): array
    {
        // Get or create current period
        if (!$period) {
            $period = $this->getOrCreateCurrentPeriod($client);
        }

        // Get documents for this period
        $documents = $this->getClientDocuments($client, $period);

        // Calculate document status
        $documentStatus = $this->getDocumentStatus($documents);

        // Calculate tax totals
        $taxTotals = $this->calculateTaxTotals($period, $documents);

        // Get issues
        $issues = $this->getIssues($documents);

        // Determine current step
        $currentStep = $this->determineCurrentStep($documentStatus, $taxTotals, $issues, $period);

        return [
            'client' => $client,
            'period' => $period,
            'current_step' => $currentStep,
            'document_status' => $documentStatus,
            'tax_totals' => $taxTotals,
            'issues' => $issues,
            'can_submit' => $this->canSubmit($documentStatus, $issues, $period),
            'progress_percentage' => $this->calculateProgress($currentStep),
        ];
    }

    /**
     * Get or create current VAT period for client
     */
    public function getOrCreateCurrentPeriod(Client $client): VatPeriod
    {
        $now = Carbon::now();
        $year = $now->year;
        $quarter = ceil($now->month / 3);

        // Try to find existing open period for this quarter
        $period = VatPeriod::where('client_id', $client->id)
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('status', '!=', 'afgesloten')
            ->first();

        if (!$period) {
            // Create new period
            $period = VatPeriod::create([
                'client_id' => $client->id,
                'year' => $year,
                'quarter' => $quarter,
                'period_start' => Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth(),
                'period_end' => Carbon::create($year, ($quarter - 1) * 3 + 1, 1)
                    ->addMonths(3)
                    ->subDay()
                    ->endOfDay(),
                'status' => 'open',
            ]);
        }

        return $period;
    }

    /**
     * Get documents for client and period
     * 
     * PERFORMANCE FIX: Split OR query into two separate queries for better index usage
     */
    public function getClientDocuments(Client $client, VatPeriod $period): Collection
    {
        // PERFORMANCE FIX: Split into two queries to allow index usage
        // OR conditions prevent efficient index usage, so we query separately and merge
        
        // Query 1: Documents with date in period (can use document_date index)
        $withDate = Document::where('client_id', $client->id)
            ->whereNotNull('document_date')
            ->whereBetween('document_date', [$period->period_start, $period->period_end])
            ->with(['ledgerAccount', 'client'])
            ->get();
        
        // Query 2: Documents without date but created in period (can use created_at index)
        $withoutDate = Document::where('client_id', $client->id)
            ->whereNull('document_date')
            ->whereBetween('created_at', [$period->period_start, $period->period_end])
            ->with(['ledgerAccount', 'client'])
            ->get();
        
        // Merge results (no duplicates possible due to mutually exclusive conditions)
        return $withDate->merge($withoutDate);
    }

    /**
     * Get document status breakdown
     */
    public function getDocumentStatus(Collection $documents): array
    {
        return [
            'total' => $documents->count(),
            'pending' => $documents->where('status', 'pending')->count(),
            'processing' => $documents->where('status', 'ocr_processing')->count(),
            'review_required' => $documents->where('status', 'review_required')->count(),
            'approved' => $documents->where('status', 'approved')->count(),
            'archived' => $documents->where('status', 'archived')->count(),
            'all_approved' => $documents->where('status', 'approved')->count() === $documents->count() && $documents->count() > 0,
        ];
    }

    /**
     * Calculate tax totals for period
     */
    public function calculateTaxTotals(VatPeriod $period, Collection $documents): array
    {
        // Get approved documents
        $approvedDocuments = $documents->where('status', 'approved');

        if ($approvedDocuments->isEmpty()) {
            return [
                'calculated' => false,
                'totals' => [],
                'grand_total' => 0,
                'grand_vat' => 0,
            ];
        }

        // Calculate totals per rubriek
        $totals = $this->vatCalculator->calculatePeriodTotals($period);

        // Calculate grand totals
        $grandTotal = 0;
        $grandVat = 0;
        foreach ($totals as $rubriek => $data) {
            $grandTotal += $data['amount'];
            $grandVat += $data['vat'];
        }

        return [
            'calculated' => true,
            'totals' => $totals,
            'grand_total' => round($grandTotal, 2),
            'grand_vat' => round($grandVat, 2),
            'document_count' => $approvedDocuments->count(),
        ];
    }

    /**
     * Get issues that need review
     * Only check documents that have been processed (not pending)
     */
    public function getIssues(Collection $documents): array
    {
        $issues = [];
        $warnings = [];

        // Only check documents that have been processed (not pending or ocr_processing)
        $processedDocuments = $documents->filter(function ($document) {
            return !in_array($document->status, ['pending', 'ocr_processing']);
        });

        foreach ($processedDocuments as $document) {
            // Low confidence OCR (only if document has been processed)
            if ($document->confidence_score && $document->confidence_score < 70) {
                $warnings[] = [
                    'type' => 'low_confidence',
                    'document_id' => $document->id,
                    'document_name' => $document->original_filename,
                    'confidence' => $document->confidence_score,
                    'message' => "Lage OCR confidence ({$document->confidence_score}%)",
                ];
            }

            // IMPORTANT: Unpaid sales invoices should not be included in VAT calculation
            // Only PAID sales invoices are taxable (cash basis rule)
            if ($document->document_type === 'sales_invoice' && $document->status === 'approved' && !$document->is_paid) {
                $warnings[] = [
                    'type' => 'unpaid_sales_invoice',
                    'document_id' => $document->id,
                    'document_name' => $document->original_filename,
                    'message' => 'Onbetaalde verkoopfactuur wordt niet meegenomen in BTW berekening (alleen betaalde facturen zijn belastbaar)',
                ];
            }

            // Missing required fields (only if document has been processed)
            // Don't flag as issue if document is still pending/processing
            if ($document->status !== 'pending' && $document->status !== 'ocr_processing') {
                if (!$document->amount_incl || !$document->document_date) {
                    $issues[] = [
                        'type' => 'missing_fields',
                        'document_id' => $document->id,
                        'document_name' => $document->original_filename,
                        'message' => 'Ontbrekende verplichte velden',
                    ];
                }
            }

            // VAT calculation mismatch (only for review_required documents)
            if ($document->status === 'review_required' && $document->review_required_reason) {
                $issues[] = [
                    'type' => 'vat_mismatch',
                    'document_id' => $document->id,
                    'document_name' => $document->original_filename,
                    'message' => $document->review_required_reason,
                ];
            }
        }

        return [
            'issues' => $issues,
            'warnings' => $warnings,
            'has_issues' => !empty($issues),
            'has_warnings' => !empty($warnings),
            'total_count' => count($issues) + count($warnings),
            'issues_count' => count($issues),
            'warnings_count' => count($warnings),
        ];
    }

    /**
     * Determine current workflow step
     */
    public function determineCurrentStep(array $documentStatus, array $taxTotals, array $issues, VatPeriod $period): string
    {
        // Step 1: Documents Processing
        // If there are pending/processing documents, we're still in step 1
        if (($documentStatus['pending'] > 0 || $documentStatus['processing'] > 0) && $documentStatus['total'] > 0) {
            return 'documents_processing';
        }

        // If there are review_required documents, we need to review first
        if ($documentStatus['review_required'] > 0) {
            return 'review_required';
        }

        // Step 2: Tax Calculation (all approved, but not calculated yet)
        if ($documentStatus['all_approved'] && $documentStatus['total'] > 0 && !$taxTotals['calculated']) {
            return 'tax_calculating';
        }

        // Step 3: Review (if issues after calculation)
        if ($taxTotals['calculated'] && $issues['has_issues']) {
            return 'review_required';
        }

        // Step 4: Ready to Submit
        if ($taxTotals['calculated'] && !$issues['has_issues'] && $period->status !== 'afgesloten') {
            return 'ready_to_submit';
        }

        // Submitted
        if ($period->status === 'afgesloten') {
            return 'submitted';
        }

        return 'documents_processing';
    }

    /**
     * Check if can submit
     */
    public function canSubmit(array $documentStatus, array $issues, VatPeriod $period): bool
    {
        return $documentStatus['all_approved']
            && !$issues['has_issues']
            && $period->status !== 'afgesloten';
    }

    /**
     * Calculate progress percentage
     */
    public function calculateProgress(string $currentStep): int
    {
        return match($currentStep) {
            'documents_processing' => 25,
            'tax_calculating' => 50,
            'review_required' => 75,
            'ready_to_submit' => 100,
            'submitted' => 100,
            default => 0,
        };
    }

    /**
     * Auto-calculate tax for period
     * 
     * DATA INTEGRITY FIX: Wrap in transaction
     * CASH BASIS FIX: Skip unpaid sales invoices
     */
    public function autoCalculateTax(VatPeriod $period): array
    {
        $client = $period->client;
        $documents = $this->getClientDocuments($client, $period);
        
        // Only calculate for approved documents
        $approvedDocuments = $documents->where('status', 'approved');
        
        // DATA INTEGRITY FIX: Wrap document updates in transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($period, $approvedDocuments) {
            // Collect documents to attach
            $documentsToAttach = [];
            $documentsToUpdate = [];
            
            foreach ($approvedDocuments as $document) {
                // CRITICAL: Cash basis rule - Skip unpaid sales invoices
                if ($document->document_type === 'sales_invoice' && !$document->is_paid) {
                    continue;
                }
                
                // Check if already attached (avoid duplicate queries)
                if (!$period->documents()->where('documents.id', $document->id)->exists()) {
                    // Calculate rubriek and VAT code
                    $rubriek = $document->vat_rubriek ?? $this->vatCalculator->calculateRubriek($document);
                    $vatCode = $document->vat_code ?? $this->determineVatCode($document);
                    
                    // Collect for batch update
                    if (!$document->vat_rubriek) {
                        $documentsToUpdate[] = ['document' => $document, 'rubriek' => $rubriek];
                    }
                    
                    $documentsToAttach[$document->id] = [
                        'rubriek' => $rubriek,
                        'btw_code' => $vatCode,
                    ];
                }
            }
            
            // Batch update documents
            foreach ($documentsToUpdate as $item) {
                $item['document']->vat_rubriek = $item['rubriek'];
                $item['document']->save();
            }
            
            // Batch attach to period (using syncWithoutDetaching to prevent duplicates)
            if (!empty($documentsToAttach)) {
                $period->documents()->syncWithoutDetaching($documentsToAttach);
            }
        });

        // Recalculate totals
        return $this->calculateTaxTotals($period, $documents);
    }
    
    /**
     * Determine VAT code from document
     */
    private function determineVatCode($document): ?string
    {
        $vatRate = (float) ($document->vat_rate ?? 0);
        
        if ($vatRate == 21) {
            return 'NL21';
        } elseif ($vatRate == 9) {
            return 'NL9';
        } elseif ($vatRate == 0) {
            return 'NL0';
        }
        
        return null;
    }

    /**
     * Get next actions for workflow
     */
    public function getNextActions(string $currentStep, array $documentStatus, array $issues): array
    {
        $actions = [];

        switch ($currentStep) {
            case 'documents_processing':
                if ($documentStatus['pending'] > 0) {
                    $actions[] = [
                        'type' => 'process_documents',
                        'label' => "Verwerk {$documentStatus['pending']} document(en)",
                        'icon' => 'heroicon-o-arrow-path',
                    ];
                }
                if ($documentStatus['review_required'] > 0) {
                    $actions[] = [
                        'type' => 'review_documents',
                        'label' => "Beoordeel {$documentStatus['review_required']} document(en)",
                        'icon' => 'heroicon-o-eye',
                    ];
                }
                break;

            case 'tax_calculating':
                $actions[] = [
                    'type' => 'calculate_tax',
                    'label' => 'Bereken BTW',
                    'icon' => 'heroicon-o-calculator',
                ];
                break;

            case 'review_required':
                $actions[] = [
                    'type' => 'review_issues',
                    'label' => "Los {$issues['total_count']} probleem(en) op",
                    'icon' => 'heroicon-o-exclamation-triangle',
                ];
                break;

            case 'ready_to_submit':
                $actions[] = [
                    'type' => 'submit',
                    'label' => 'Dien BTW aangifte in',
                    'icon' => 'heroicon-o-paper-airplane',
                ];
                break;
        }

        return $actions;
    }
}

