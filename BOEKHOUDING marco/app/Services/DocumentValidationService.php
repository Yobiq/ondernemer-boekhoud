<?php

namespace App\Services;

use App\Models\Document;
use App\Services\VatValidator;

/**
 * Centralized Document Validation Service
 * 
 * HIGH PRIORITY FIX: Consolidates all validation logic in one place
 * for better maintainability and consistency
 */
class DocumentValidationService
{
    protected VatValidator $vatValidator;

    public function __construct(VatValidator $vatValidator)
    {
        $this->vatValidator = $vatValidator;
    }

    /**
     * Validate document before approval
     * 
     * @param Document $document
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public function validateForApproval(Document $document): array
    {
        $errors = [];
        $warnings = [];

        // Required fields validation
        if (!$document->document_date) {
            $errors[] = 'Documentdatum is verplicht';
        }

        // Supplier name required (except for bank statements)
        if (!$document->supplier_name && $document->document_type !== 'bank_statement') {
            $errors[] = 'Leverancier naam is verplicht';
        }

        // Amount validation: excl + BTW = incl
        if ($document->amount_excl !== null && $document->amount_vat !== null && $document->amount_incl !== null) {
            $calculated = (float) $document->amount_excl + (float) $document->amount_vat;
            $actual = (float) $document->amount_incl;
            $difference = abs($calculated - $actual);
            
            if ($difference > 0.01) {
                $errors[] = "Bedragen kloppen niet: excl ({$document->amount_excl}) + BTW ({$document->amount_vat}) = " . 
                           number_format($calculated, 2, ',', '.') . 
                           " ≠ incl ({$document->amount_incl})";
            }
        }

        // VAT validation
        if ($document->amount_excl && $document->amount_vat && $document->vat_rate) {
            $vatValidation = $this->vatValidator->validate(
                (float) $document->amount_excl,
                (float) $document->amount_vat,
                $document->vat_rate
            );
            
            if (!$vatValidation['valid']) {
                $errors[] = 'BTW berekening is onjuist: ' . ($vatValidation['message'] ?? 'Onbekende fout');
            }
        }

        // Warnings (don't block approval but inform user)
        if ($document->document_type === 'sales_invoice' && !$document->is_paid) {
            $warnings[] = 'Onbetaalde verkoopfactuur wordt niet meegenomen in BTW berekening (kasstelsel)';
        }

        if (!$document->ledger_account_id) {
            $warnings[] = 'Geen grootboekrekening toegewezen';
        }

        if ($document->confidence_score && $document->confidence_score < 70) {
            $warnings[] = "Lage OCR confidence score ({$document->confidence_score}%)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate amounts are consistent
     */
    public function validateAmounts(Document $document): bool
    {
        if (!$document->amount_excl || !$document->amount_vat || !$document->amount_incl) {
            return false;
        }

        $calculated = (float) $document->amount_excl + (float) $document->amount_vat;
        $actual = (float) $document->amount_incl;
        
        return abs($calculated - $actual) <= 0.01;
    }

    /**
     * Validate VAT calculation
     */
    public function validateVat(Document $document): bool
    {
        if (!$document->amount_excl || !$document->amount_vat || !$document->vat_rate) {
            return false;
        }

        $validation = $this->vatValidator->validate(
            (float) $document->amount_excl,
            (float) $document->amount_vat,
            $document->vat_rate
        );

        return $validation['valid'];
    }

    /**
     * Check for business rule violations
     */
    public function hasBusinessRuleViolations(Document $document, array &$warnings): bool
    {
        $hasViolations = false;

        // Check for future dates
        if ($document->document_date && $document->document_date->isFuture()) {
            $warnings[] = 'Documentdatum ligt in de toekomst';
            $hasViolations = true;
        }

        // Check for very old dates (>2 years)
        if ($document->document_date && $document->document_date->lt(now()->subYears(2))) {
            $warnings[] = 'Documentdatum is meer dan 2 jaar geleden';
            $hasViolations = true;
        }

        return $hasViolations;
    }
}

