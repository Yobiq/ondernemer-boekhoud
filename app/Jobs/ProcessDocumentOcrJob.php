<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\OCR\OcrService;
use App\Services\VatValidator;
use App\Services\LedgerSuggestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable as BusQueueable;
use Filament\Notifications\Notification;

class ProcessDocumentOcrJob implements ShouldQueue
{
    use BusQueueable, Queueable;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {
        // Queue on 'ocr' queue as per spec
        $this->onQueue('ocr');
    }

    /**
     * Execute the OCR workflow
     * 
     * Steps:
     * 1. Update status to ocr_processing
     * 2. Run OCR and extract data
     * 3. Validate BTW
     * 4. Suggest ledger account
     * 5. Auto-approve or require review
     */
    public function handle(
        OcrService $ocrService,
        VatValidator $vatValidator,
        LedgerSuggestionService $ledgerSuggester
    ): void
    {
        try {
            Log::info("OCR Job: Starting processing for document #{$this->document->id}");
            
            // SPECIAL CASE: Sales invoices created via FactuurMaken (client portal) don't need OCR
            // They already have all the data when created (amount_excl, amount_vat, amount_incl, document_date, ocr_data)
            if ($this->document->document_type === 'sales_invoice') {
                Log::info("OCR Job: Sales invoice detected - checking if created via portal", [
                    'document_id' => $this->document->id,
                    'has_amount_incl' => !is_null($this->document->amount_incl),
                    'has_document_date' => !is_null($this->document->document_date),
                    'upload_source' => $this->document->upload_source ?? 'unknown',
                    'filename' => $this->document->original_filename,
                ]);
                
                // Check if this is a form-created invoice (has data already)
                // Form-created invoices have: amount_incl, document_date, and ocr_data with invoice details
                $isFormCreated = $this->document->amount_incl 
                    && $this->document->document_date 
                    && ($this->document->upload_source === 'web' || str_starts_with($this->document->original_filename, 'Factuur_'));
                
                if ($isFormCreated) {
                    Log::info("OCR Job: Form-created sales invoice - skipping OCR, auto-approving");
                    
                    // Wrap in transaction to ensure data integrity
                    DB::transaction(function () use ($vatValidator, $ledgerSuggester) {
                        // Calculate VAT rubriek and code for form-created invoices
                        $vatCalculator = app(\App\Services\VatCalculatorService::class);
                        $this->document->vat_rubriek = $vatCalculator->calculateRubriek($this->document);
                        
                        // Determine VAT code if not set
                        if (!$this->document->vat_code) {
                            $vatRate = (float) ($this->document->vat_rate ?? 0);
                            $this->document->vat_code = match(true) {
                                $vatRate == 21 => 'NL21',
                                $vatRate == 9 => 'NL9',
                                $vatRate == 0 => 'NL0',
                                default => null,
                            };
                        }
                        
                        // Validate BTW if amounts are present
                        if ($this->document->amount_excl && $this->document->amount_vat && $this->document->vat_rate) {
                            $vatValidation = $vatValidator->validate(
                                $this->document->amount_excl,
                                $this->document->amount_vat,
                                $this->document->vat_rate
                            );
                            
                            if ($vatValidation['valid']) {
                                // Suggest ledger account
                                $suggestion = $ledgerSuggester->suggest($this->document);
                                $this->document->ledger_account_id = $suggestion['ledger_account_id'];
                                
                                $this->document->status = 'approved';
                                $this->document->auto_approved = true;
                                $this->document->confidence_score = 100; // Perfect confidence for manual entry
                                $this->document->save();
                                
                                Log::info("OCR Job: Sales invoice auto-approved", [
                                    'document_id' => $this->document->id,
                                    'vat_rubriek' => $this->document->vat_rubriek,
                                ]);
                                return;
                            }
                        } else {
                            // Has basic data but needs BTW calculation
                            // Suggest ledger account and approve anyway (data is from form, so trusted)
                            $suggestion = $ledgerSuggester->suggest($this->document);
                            $this->document->ledger_account_id = $suggestion['ledger_account_id'];
                            $this->document->confidence_score = 95; // High confidence for manual entry
                            $this->document->status = 'approved';
                            $this->document->auto_approved = true;
                            $this->document->save();
                            
                            Log::info("OCR Job: Sales invoice auto-approved (basic data from form)", [
                                'document_id' => $this->document->id,
                                'vat_rubriek' => $this->document->vat_rubriek,
                            ]);
                            return;
                        }
                    });
                    
                    // Refresh document after transaction
                    $this->document->refresh();
                    return;
                }
                
                // If it's an uploaded sales invoice (PDF/image), still process with OCR
                Log::info("OCR Job: Uploaded sales invoice - processing with OCR");
            }
            
            // Step 1: Update status
            $this->document->update(['status' => 'ocr_processing']);
            
            // Step 2: Run OCR
            $ocrData = $ocrService->processDocument($this->document->file_path);
            
            // Step 3-6: Wrap all document updates in transaction for data integrity
            DB::transaction(function () use ($ocrData, $vatValidator, $ledgerSuggester) {
                // Store OCR data in JSONB field
                $this->document->ocr_data = $ocrData;
                
                // Extract amounts and metadata
                $this->document->amount_excl = $ocrData['amounts']['excl'] ?? null;
                $this->document->amount_vat = $ocrData['amounts']['vat'] ?? null;
                $this->document->amount_incl = $ocrData['amounts']['incl'] ?? null;
                $this->document->vat_rate = $ocrData['amounts']['vat_rate'] ?? null;
                $this->document->document_date = $ocrData['invoice']['date'] ?? null;
                $this->document->supplier_name = $ocrData['supplier']['name'] ?? null;
                $this->document->supplier_vat = $ocrData['supplier']['vat_number'] ?? null;
                
                $this->document->save();
            
                Log::info("OCR Job: OCR data extracted", [
                    'document_id' => $this->document->id,
                    'has_amounts' => !is_null($this->document->amount_incl),
                    'has_supplier' => !empty($this->document->supplier_name),
                    'has_date' => !is_null($this->document->document_date),
                    'has_vat_rate' => !is_null($this->document->vat_rate),
                    'ocr_data_keys' => $ocrData ? array_keys($ocrData) : [],
                    'raw_text_length' => !empty($ocrData['raw_text']) ? strlen($ocrData['raw_text']) : 0,
                    'extracted_values' => [
                        'amount_excl' => $this->document->amount_excl,
                        'amount_vat' => $this->document->amount_vat,
                        'amount_incl' => $this->document->amount_incl,
                        'vat_rate' => $this->document->vat_rate,
                        'document_date' => $this->document->document_date?->format('Y-m-d'),
                        'supplier_name' => $this->document->supplier_name,
                    ],
                ]);
                
                // Step 3: Validate BTW
                $vatValid = false;
                if ($this->hasRequiredAmounts()) {
                    $vatValidation = $vatValidator->validate(
                        $this->document->amount_excl,
                        $this->document->amount_vat,
                        $this->document->vat_rate
                    );
                    $vatValid = $vatValidation['valid'];
                    
                    Log::info("OCR Job: BTW validation", [
                        'document_id' => $this->document->id,
                        'valid' => $vatValid,
                        'message' => $vatValidation['message'] ?? null,
                    ]);
                }
                
                // Step 4: Suggest ledger account
                $suggestion = $ledgerSuggester->suggest($this->document);
                $this->document->ledger_account_id = $suggestion['ledger_account_id'];
                $this->document->confidence_score = $suggestion['confidence_score'];
                
                Log::info("OCR Job: Ledger suggestion", [
                    'document_id' => $this->document->id,
                    'account_id' => $suggestion['ledger_account_id'],
                    'confidence' => $suggestion['confidence_score'],
                    'reason' => $suggestion['reason'] ?? null,
                ]);
                
                // Step 5: Calculate VAT rubriek and code BEFORE approval
                $vatCalculator = app(\App\Services\VatCalculatorService::class);
                $this->document->vat_rubriek = $vatCalculator->calculateRubriek($this->document);
                
                // Determine VAT code if not set
                if (!$this->document->vat_code) {
                    $vatRate = (float) ($this->document->vat_rate ?? 0);
                    $this->document->vat_code = match(true) {
                        $vatRate == 21 => 'NL21',
                        $vatRate == 9 => 'NL9',
                        $vatRate == 0 => 'NL0',
                        default => null,
                    };
                }
                
                // Step 6: Determine if auto-approval is possible
                $canAutoApprove = $this->canAutoApprove($vatValid, $suggestion['confidence_score']);
                
                // Build review reason
                $reviewReasons = [];
                if (!$vatValid) {
                    $reviewReasons[] = 'BTW validatie gefaald';
                }
            $threshold = config('bookkeeping.auto_approval.ocr_confidence_threshold', 90);
            if ($suggestion['confidence_score'] < $threshold) {
                $reviewReasons[] = 'Lage confidence score (' . $suggestion['confidence_score'] . '%, minimum: ' . $threshold . '%)';
            }
                if (is_null($this->document->document_date)) {
                    $reviewReasons[] = 'Datum niet geëxtraheerd';
                }
                if (is_null($this->document->amount_incl)) {
                    $reviewReasons[] = 'Bedrag niet geëxtraheerd';
                }
                if (empty($this->document->supplier_name)) {
                    $reviewReasons[] = 'Leverancier naam niet geëxtraheerd';
                }
                
                if ($canAutoApprove) {
                    $this->document->status = 'approved';
                    $this->document->auto_approved = true;
                    Log::info("OCR Job: Document AUTO-APPROVED", ['document_id' => $this->document->id]);
                } else {
                    $this->document->status = 'review_required';
                    $this->document->review_required_reason = !empty($reviewReasons) 
                        ? implode('; ', $reviewReasons) 
                        : 'Handmatige controle vereist';
                    Log::info("OCR Job: Document requires REVIEW", [
                        'document_id' => $this->document->id,
                        'reasons' => $reviewReasons,
                    ]);
                }
                
                // Final save within transaction
                $this->document->save();
            });
            
            // Refresh document after transaction
            $this->document->refresh();
            
            Log::info("OCR Job: Processing completed successfully", [
                'document_id' => $this->document->id,
                'final_status' => $this->document->status,
            ]);
            
        } catch (\Exception $e) {
            Log::error("OCR Job: Failed for document #{$this->document->id}: " . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Update to review required on error with reason
            $this->document->update([
                'status' => 'review_required',
                'review_required_reason' => 'OCR verwerking gefaald: ' . $e->getMessage(),
            ]);
            
            throw $e; // Re-throw for retry logic
        }
    }
    
    /**
     * Check if document has required amounts for BTW validation
     */
    protected function hasRequiredAmounts(): bool
    {
        return !is_null($this->document->amount_excl) 
            && !is_null($this->document->amount_vat)
            && !is_null($this->document->vat_rate);
    }
    
    /**
     * Determine if document can be auto-approved
     * 
     * Criteria (ALL must be met):
     * - BTW validation PASSED
     * - Confidence score >= 90
     * - Has required fields (date, amounts, supplier)
     */
    protected function canAutoApprove(bool $vatValid, float $confidenceScore): bool
    {
        // BTW MUST be valid (blocking condition)
        if (!$vatValid) {
            return false;
        }
        
        // Confidence must meet threshold (from config)
        $threshold = config('bookkeeping.auto_approval.ocr_confidence_threshold', 90);
        if ($confidenceScore < $threshold) {
            return false;
        }
        
        // Must have key fields
        if (is_null($this->document->document_date)) {
            return false;
        }
        
        if (is_null($this->document->amount_incl)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle job failure
     * 
     * ENHANCED: Now sends notifications to relevant users
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("OCR Job: Permanently failed for document #{$this->document->id}", [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        $this->document->update([
            'status' => 'review_required',
            'review_required_reason' => 'OCR job permanent gefaald: ' . $exception->getMessage(),
        ]);
        
        // ENHANCEMENT: Notify bookkeeper and admins about the failure
        try {
            $client = $this->document->client;
            if ($client) {
                // Notify client's bookkeeper
                $bookkeeper = \App\Models\User::where('client_id', $client->id)
                    ->where('role', 'bookkeeper')
                    ->first();
                
                if ($bookkeeper) {
                    Notification::make()
                        ->title('OCR Verwerking Gefaald')
                        ->body("Document '{$this->document->original_filename}' kon niet automatisch verwerkt worden. Handmatige controle vereist.")
                        ->warning()
                        ->persistent()
                        ->sendToDatabase($bookkeeper);
                }
            }
            
            // Also notify admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('OCR Job Permanent Gefaald')
                    ->body("Document #{$this->document->id} ({$this->document->original_filename}) kon niet verwerkt worden na {$this->tries} pogingen.")
                    ->danger()
                    ->persistent()
                    ->sendToDatabase($admin);
            }
        } catch (\Exception $e) {
            // Don't fail the job failure handler if notification fails
            Log::error("Failed to send notification for failed OCR job", [
                'document_id' => $this->document->id,
                'notification_error' => $e->getMessage(),
            ]);
        }
    }
}

