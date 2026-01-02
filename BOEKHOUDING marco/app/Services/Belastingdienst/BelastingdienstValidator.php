<?php

namespace App\Services\Belastingdienst;

use App\Models\Document;
use App\Models\VatPeriod;
use Illuminate\Support\Collection;

class BelastingdienstValidator
{
    protected VatRatesService $vatRatesService;
    
    public function __construct(VatRatesService $vatRatesService)
    {
        $this->vatRatesService = $vatRatesService;
    }
    
    /**
     * Validate BTW number format (Dutch)
     */
    public function validateBtwNumber(string $btwNumber): bool
    {
        // Remove spaces and convert to uppercase
        $btwNumber = strtoupper(str_replace(' ', '', $btwNumber));
        
        // Dutch BTW format: NL123456789B01
        // Pattern: 2 letters (country) + 9 digits + B + 2 digits
        if (!preg_match('/^[A-Z]{2}\d{9}B\d{2}$/', $btwNumber)) {
            return false;
        }
        
        // Additional validation: check digit algorithm (simplified)
        // Full validation would require implementing the actual algorithm
        return true;
    }
    
    /**
     * Validate KVK number format (Dutch Chamber of Commerce)
     */
    public function validateKvkNumber(string $kvkNumber): bool
    {
        // Remove spaces and dashes
        $kvkNumber = preg_replace('/[\s\-]/', '', $kvkNumber);
        
        // KVK format: 8 digits
        if (!preg_match('/^\d{8}$/', $kvkNumber)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate VAT calculation for a document
     */
    public function validateVatCalculation(Document $document): ValidationResult
    {
        $result = new ValidationResult();
        
        // Check if VAT code is set
        if (!$document->vat_code) {
            $result->addError('vat_code', 'BTW code is verplicht');
            return $result;
        }
        
        // Validate VAT rate
        if (!$this->vatRatesService->validateRate($document)) {
            $result->addError('vat_rate', 'BTW percentage komt niet overeen met BTW code');
        }
        
        // Validate calculation
        if ($document->amount_excl && $document->vat_rate) {
            $expectedVat = $document->amount_excl * ($document->vat_rate / 100);
            $expectedIncl = $document->amount_excl + $expectedVat;
            
            if ($document->amount_vat) {
                $difference = abs($document->amount_vat - $expectedVat);
                if ($difference > 0.01) {
                    $result->addError('amount_vat', "BTW bedrag klopt niet. Verwacht: €" . number_format($expectedVat, 2));
                }
            }
            
            if ($document->amount_incl) {
                $difference = abs($document->amount_incl - $expectedIncl);
                if ($difference > 0.01) {
                    $result->addError('amount_incl', "Totaal bedrag klopt niet. Verwacht: €" . number_format($expectedIncl, 2));
                }
            }
        }
        
        // Validate rubriek assignment - only warn if we can't calculate it
        if ($document->vat_code && !$document->vat_rubriek) {
            // Try to calculate rubriek if we have document type and vat rate
            if ($document->document_type && $document->vat_rate) {
                // Rubriek can be calculated, so this is just informational
                $result->addWarning('vat_rubriek', 'BTW rubriek wordt automatisch berekend bij goedkeuring');
            } else {
                // Can't calculate rubriek - this is a real warning
                $result->addWarning('vat_rubriek', 'BTW rubriek kan niet worden berekend (ontbrekende gegevens)');
            }
        }
        
        return $result;
    }
    
    /**
     * Validate a VAT period
     */
    public function validatePeriod(VatPeriod $period): ValidationResult
    {
        $result = new ValidationResult();
        
        // Check if period has documents
        $documents = $period->documents;
        if ($documents->isEmpty()) {
            $result->addWarning('documents', 'Periode heeft geen documenten');
        }
        
        // Validate all documents in period
        $documentErrors = [];
        foreach ($documents as $document) {
            $docResult = $this->validateVatCalculation($document);
            if (!$docResult->isValid) {
                $documentErrors[$document->id] = $docResult->errors;
            }
        }
        
        if (!empty($documentErrors)) {
            $result->addError('documents', 'Sommige documenten hebben validatiefouten');
            $result->errors['document_details'] = $documentErrors;
        }
        
        // Check period completeness
        if (!$period->period_start || !$period->period_end) {
            $result->addError('period', 'Periode start- en einddatum zijn verplicht');
        }
        
        if ($period->period_start && $period->period_end && $period->period_start->gt($period->period_end)) {
            $result->addError('period', 'Startdatum moet voor einddatum liggen');
        }
        
        // Check required rubrieken
        $requiredRubrieken = ['1a', '1b', '4a', '5b'];
        $presentRubrieken = $documents->pluck('vat_rubriek')->filter()->unique()->toArray();
        
        foreach ($requiredRubrieken as $rubriek) {
            if (!in_array($rubriek, $presentRubrieken)) {
                // Not an error, just a warning if no documents for this rubriek
                $result->addWarning('rubrieken', "Rubriek {$rubriek} heeft geen documenten");
            }
        }
        
        return $result;
    }
    
    /**
     * Get all validation errors for a period
     */
    public function getValidationErrors(VatPeriod $period): array
    {
        $result = $this->validatePeriod($period);
        
        return [
            'is_valid' => $result->isValid,
            'errors' => $result->errors,
            'warnings' => $result->warnings,
        ];
    }
    
    /**
     * Validate BTW number format with detailed error
     */
    public function validateBtwNumberDetailed(string $btwNumber): array
    {
        $btwNumber = strtoupper(str_replace(' ', '', $btwNumber));
        
        $errors = [];
        
        if (empty($btwNumber)) {
            $errors[] = 'BTW nummer is verplicht';
            return ['valid' => false, 'errors' => $errors];
        }
        
        if (!preg_match('/^[A-Z]{2}/', $btwNumber)) {
            $errors[] = 'BTW nummer moet beginnen met 2 letters (landcode)';
        }
        
        if (!preg_match('/\d{9}/', $btwNumber)) {
            $errors[] = 'BTW nummer moet 9 cijfers bevatten';
        }
        
        if (!preg_match('/B\d{2}$/', $btwNumber)) {
            $errors[] = 'BTW nummer moet eindigen met B gevolgd door 2 cijfers';
        }
        
        if (!preg_match('/^[A-Z]{2}\d{9}B\d{2}$/', $btwNumber)) {
            $errors[] = 'BTW nummer formaat is ongeldig (verwacht: NL123456789B01)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Validate KVK number with detailed error
     */
    public function validateKvkNumberDetailed(string $kvkNumber): array
    {
        $kvkNumber = preg_replace('/[\s\-]/', '', $kvkNumber);
        
        $errors = [];
        
        if (empty($kvkNumber)) {
            $errors[] = 'KVK nummer is verplicht';
            return ['valid' => false, 'errors' => $errors];
        }
        
        if (!preg_match('/^\d+$/', $kvkNumber)) {
            $errors[] = 'KVK nummer mag alleen cijfers bevatten';
        }
        
        if (strlen($kvkNumber) !== 8) {
            $errors[] = 'KVK nummer moet precies 8 cijfers bevatten';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}

