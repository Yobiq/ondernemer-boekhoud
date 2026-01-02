<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     * Create immutable audit log entry
     */
    public function created(Document $document): void
    {
        AuditLog::create([
            'entity_type' => 'Document',
            'entity_id' => $document->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => $document->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Document "updated" event.
     * Create immutable audit log with old and new values
     */
    public function updated(Document $document): void
    {
        // Get changed attributes
        $changes = $document->getChanges();
        $original = $document->getOriginal();
        
        // Filter to only changed attributes
        $oldValues = [];
        $newValues = [];
        
        foreach ($changes as $key => $newValue) {
            if ($key === 'updated_at') continue; // Skip timestamp
            
            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $newValue;
        }
        
        // Special handling for approval
        $action = 'updated';
        if (isset($changes['status']) && $changes['status'] === 'approved') {
            $action = 'approved';
            
            // Auto-attach approved document to VAT period
            $this->attachToVatPeriod($document);
        }
        
        AuditLog::create([
            'entity_type' => 'Document',
            'entity_id' => $document->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
        ]);
    }
    
    /**
     * Attach approved document to appropriate VAT period
     * 
     * CRITICAL FIXES APPLIED:
     * 1. Database transaction for data integrity
     * 2. Cash basis rule: Skip unpaid sales invoices
     * 3. Race condition: Use syncWithoutDetaching to prevent duplicates
     * 4. Better error handling with notifications
     */
    private function attachToVatPeriod(Document $document): void
    {
        try {
            // CRITICAL: Cash basis rule - Don't attach unpaid sales invoices to VAT period
            // Only PAID sales invoices are taxable (cash basis accounting)
            if ($document->document_type === 'sales_invoice' && !$document->is_paid) {
                \Illuminate\Support\Facades\Log::info('Skipping unpaid sales invoice from VAT period (cash basis rule)', [
                    'document_id' => $document->id,
                    'document_type' => $document->document_type,
                    'is_paid' => $document->is_paid,
                ]);
                return;
            }
            
            $client = $document->client;
            if (!$client) {
                \Illuminate\Support\Facades\Log::warning('Document has no client, cannot attach to VAT period', [
                    'document_id' => $document->id,
                ]);
                return;
            }
            
            // Wrap in transaction for data integrity
            DB::transaction(function () use ($document, $client) {
                // Get or create current period
                $workflowService = app(\App\Services\ClientTaxWorkflowService::class);
                $period = $workflowService->getOrCreateCurrentPeriod($client);
                
                // Check if document is in period date range
                $inPeriod = false;
                if ($document->document_date) {
                    $inPeriod = $document->document_date >= $period->period_start 
                        && $document->document_date <= $period->period_end;
                } else {
                    $inPeriod = $document->created_at >= $period->period_start 
                        && $document->created_at <= $period->period_end;
                }
                
                if (!$inPeriod) {
                    \Illuminate\Support\Facades\Log::info('Document date outside current period range', [
                        'document_id' => $document->id,
                        'document_date' => $document->document_date?->format('Y-m-d'),
                        'period_start' => $period->period_start->format('Y-m-d'),
                        'period_end' => $period->period_end->format('Y-m-d'),
                    ]);
                    return;
                }
                
                // RACE CONDITION FIX: Check if already attached (unique constraint handles duplicates)
                // Use syncWithoutDetaching to prevent duplicate attachments
                if ($period->documents()->where('documents.id', $document->id)->exists()) {
                    \Illuminate\Support\Facades\Log::debug('Document already attached to period', [
                        'document_id' => $document->id,
                        'period_id' => $period->id,
                    ]);
                    return;
                }
                
                // Calculate rubriek and VAT code
                $vatCalculator = app(\App\Services\VatCalculatorService::class);
                $rubriek = $document->vat_rubriek ?? $vatCalculator->calculateRubriek($document);
                
                // Determine VAT code
                $vatRate = (float) ($document->vat_rate ?? 0);
                $vatCode = $document->vat_code ?? match(true) {
                    $vatRate == 21 => 'NL21',
                    $vatRate == 9 => 'NL9',
                    $vatRate == 0 => 'NL0',
                    default => null,
                };
                
                // Update document with rubriek if not set (within transaction)
                if (!$document->vat_rubriek) {
                    $document->vat_rubriek = $rubriek;
                    $document->save();
                }
                
                // IMPORTANT: Check if period is locked
                $periodIsLocked = $period->isLocked();
                
                // RACE CONDITION FIX: Use syncWithoutDetaching to prevent duplicates
                // The unique constraint will catch any race condition attempts
                try {
                    $period->documents()->syncWithoutDetaching([
                        $document->id => [
                            'rubriek' => $rubriek,
                            'btw_code' => $vatCode,
                        ]
                    ]);
                    
                    \Illuminate\Support\Facades\Log::info('Document attached to VAT period', [
                        'document_id' => $document->id,
                        'period_id' => $period->id,
                        'rubriek' => $rubriek,
                        'vat_code' => $vatCode,
                        'period_locked' => $periodIsLocked,
                    ]);
                    
                    // Log warning if period is locked
                    if ($periodIsLocked) {
                        \Illuminate\Support\Facades\Log::warning('Document added to locked VAT period - recalculation recommended', [
                            'document_id' => $document->id,
                            'period_id' => $period->id,
                            'period' => $period->period_string,
                        ]);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    // Unique constraint violation - document already attached (race condition handled)
                    if (str_contains($e->getMessage(), 'UNIQUE constraint')) {
                        \Illuminate\Support\Facades\Log::info('Document already attached to period (race condition handled)', [
                            'document_id' => $document->id,
                            'period_id' => $period->id,
                        ]);
                        return;
                    }
                    throw $e; // Re-throw if it's a different error
                }
            });
        } catch (\Exception $e) {
            // Enhanced error handling with notification
            \Illuminate\Support\Facades\Log::error('Failed to attach document to VAT period', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Notify bookkeeper about the failure
            try {
                if (Auth::check()) {
                    Notification::make()
                        ->title('Waarschuwing: Document niet gekoppeld aan periode')
                        ->body("Document {$document->original_filename} is goedgekeurd maar niet gekoppeld aan BTW periode. Controleer handmatig.")
                        ->warning()
                        ->persistent()
                        ->sendToDatabase(Auth::user());
                }
            } catch (\Exception $notificationError) {
                // Don't break if notification fails
                \Illuminate\Support\Facades\Log::warning('Failed to send notification', [
                    'error' => $notificationError->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        AuditLog::create([
            'entity_type' => 'Document',
            'entity_id' => $document->id,
            'action' => 'updated', // We don't actually delete, just update status
            'old_values' => $document->getOriginal(),
            'new_values' => ['deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        AuditLog::create([
            'entity_type' => 'Document',
            'entity_id' => $document->id,
            'action' => 'updated',
            'old_values' => ['deleted' => true],
            'new_values' => $document->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        // Force deletes should be very rare
        AuditLog::create([
            'entity_type' => 'Document',
            'entity_id' => $document->id,
            'action' => 'updated',
            'old_values' => $document->getOriginal(),
            'new_values' => ['force_deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }
}
