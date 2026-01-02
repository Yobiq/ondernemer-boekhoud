<?php

namespace App\Services;

use App\Models\Document;
use App\Services\VatCalculatorService;

class AutoApprovalService
{
    private VatCalculatorService $vatCalculator;

    public function __construct(VatCalculatorService $vatCalculator)
    {
        $this->vatCalculator = $vatCalculator;
    }

    /**
     * Determine if document should be auto-approved
     */
    public function shouldAutoApprove(Document $document): bool
    {
        // Check if document is already approved
        if ($document->status === 'approved') {
            return false;
        }

        // Check validation
        $validation = $this->vatCalculator->validateVatCalculation($document);
        if (!$validation['valid']) {
            return false;
        }

        // Check if grootboek is matched
        if (!$document->ledger_account_id) {
            return false;
        }

        // Check confidence score (from config)
        $confidenceScore = (float) ($document->confidence_score ?? 0);
        $threshold = config('bookkeeping.auto_approval.confidence_threshold', 0.85);
        if ($confidenceScore < $threshold) {
            return false;
        }

        // Check for historical deviations
        if ($this->hasHistoricalDeviations($document)) {
            return false;
        }

        return true;
    }

    /**
     * Auto-approve a document
     */
    public function autoApprove(Document $document): void
    {
        if (!$this->shouldAutoApprove($document)) {
            throw new \Exception('Document kan niet automatisch worden goedgekeurd');
        }

        $reasons = $this->getAutoApprovalReasons($document);

        $document->update([
            'status' => 'approved',
            'auto_approved' => true,
            'auto_approval_reason' => implode('; ', $reasons),
            'vat_rubriek' => $this->vatCalculator->calculateRubriek($document),
            'vat_code' => $document->vat_code ?? $this->determineVatCode($document),
        ]);
    }

    /**
     * Get reasons why document should/shouldn't be auto-approved
     */
    public function getAutoApprovalReasons(Document $document): array
    {
        $reasons = [];

        // Validation check
        $validation = $this->vatCalculator->validateVatCalculation($document);
        if ($validation['valid']) {
            $reasons[] = 'BTW berekening klopt';
        } else {
            $reasons[] = 'BTW berekening fout: ' . implode(', ', $validation['errors']);
        }

        // Grootboek check
        if ($document->ledger_account_id) {
            $reasons[] = 'Grootboekrekening gematcht';
        } else {
            $reasons[] = 'Geen grootboekrekening toegewezen';
        }

        // Confidence score
        $confidenceScore = (float) ($document->confidence_score ?? 0);
        if ($confidenceScore >= 0.85) {
            $reasons[] = "Hoge confidence score ({$confidenceScore})";
        } else {
            $reasons[] = "Lage confidence score ({$confidenceScore})";
        }

        // Historical check
        if (!$this->hasHistoricalDeviations($document)) {
            $reasons[] = 'Geen afwijkingen t.o.v. historie';
        } else {
            $reasons[] = 'Afwijkingen t.o.v. historie gedetecteerd';
        }

        return $reasons;
    }

    /**
     * Check for historical deviations
     */
    private function hasHistoricalDeviations(Document $document): bool
    {
        // Get historical documents from same supplier
        $historicalDocs = Document::where('client_id', $document->client_id)
            ->where('supplier_name', $document->supplier_name)
            ->where('status', 'approved')
            ->where('id', '!=', $document->id)
            ->orderBy('document_date', 'desc')
            ->limit(10)
            ->get();

        if ($historicalDocs->isEmpty()) {
            // No history, allow auto-approval
            return false;
        }

        // Check for significant deviations in amounts
        $avgAmount = $historicalDocs->avg('amount_incl');
        $currentAmount = (float) ($document->amount_incl ?? 0);

        // If current amount is more than 3x average, flag as deviation
        if ($avgAmount > 0 && $currentAmount > ($avgAmount * 3)) {
            return true;
        }

        // Check for VAT rate changes
        $avgVatRate = $historicalDocs->avg('vat_rate');
        $currentVatRate = (float) ($document->vat_rate ?? 0);

        if (abs($currentVatRate - $avgVatRate) > 5) {
            return true;
        }

        return false;
    }

    /**
     * Determine VAT code from document
     */
    private function determineVatCode(Document $document): ?string
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
}


