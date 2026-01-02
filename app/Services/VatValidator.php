<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VatValidator
{
    /**
     * Maximum allowed deviation in euros (from config, default 2 cents)
     */
    public function getMaxTolerance(): float
    {
        return config('bookkeeping.vat.tolerance', 0.02);
    }
    
    /**
     * Dutch BTW rates
     */
    const VALID_RATES = ['21', '9', '0', 'verlegd'];
    
    /**
     * Validate Dutch BTW (VAT) calculation
     * 
     * Rules:
     * - 21% (standard rate)
     * - 9% (reduced rate - food, books, etc.)
     * - 0% (export, intracommunautair)
     * - verlegd (reverse charge)
     * 
     * Tolerance: Maximum €0.02 deviation
     * 
     * @param float|null $excl Amount excluding BTW
     * @param float|null $vat BTW amount
     * @param string|null $rate BTW rate (21, 9, 0, verlegd)
     * @return array ['valid' => bool, 'message' => string, 'expected_vat' => float|null]
     */
    public function validate(?float $excl, ?float $vat, ?string $rate): array
    {
        // Check if all required values are present
        if (is_null($excl) || is_null($vat) || is_null($rate)) {
            return [
                'valid' => false,
                'message' => 'Ontbrekende BTW gegevens',
                'expected_vat' => null,
            ];
        }
        
        // Validate rate is one of our allowed rates
        if (!in_array($rate, self::VALID_RATES)) {
            return [
                'valid' => false,
                'message' => "Ongeldig BTW tarief: {$rate}",
                'expected_vat' => null,
            ];
        }
        
        // Special handling for 0% and verlegd
        $maxTolerance = $this->getMaxTolerance();
        if ($rate === '0') {
            $isValid = abs($vat) <= $maxTolerance;
            return [
                'valid' => $isValid,
                'message' => $isValid ? 'BTW 0%: Geldig' : 'BTW 0%: Verwacht €0,00',
                'expected_vat' => 0.00,
            ];
        }
        
        if ($rate === 'verlegd') {
            $isValid = abs($vat) <= $maxTolerance;
            return [
                'valid' => $isValid,
                'message' => $isValid ? 'BTW verlegd: Geldig' : 'BTW verlegd: Verwacht €0,00',
                'expected_vat' => 0.00,
            ];
        }
        
        // Calculate expected BTW
        $rateDecimal = (float)$rate / 100;
        $expectedVat = round($excl * $rateDecimal, 2);
        
        // Check deviation
        $deviation = abs($vat - $expectedVat);
        $maxTolerance = $this->getMaxTolerance();
        
        $isValid = $deviation <= $maxTolerance;
        
        Log::debug('BTW Validation', [
            'excl' => $excl,
            'vat_provided' => $vat,
            'vat_expected' => $expectedVat,
            'rate' => $rate,
            'deviation' => $deviation,
            'tolerance' => $maxTolerance,
            'valid' => $isValid,
        ]);
        
        if (!$isValid) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'BTW berekening incorrect: Verwacht €%.2f (%s%%), gekregen €%.2f (afwijking: €%.2f)',
                    $expectedVat,
                    $rate,
                    $vat,
                    $deviation
                ),
                'expected_vat' => $expectedVat,
            ];
        }
        
        return [
            'valid' => true,
            'message' => sprintf('BTW %s%%: Correct', $rate),
            'expected_vat' => $expectedVat,
        ];
    }
    
    /**
     * Validate BTW using total amount
     * Calculates excl and BTW from incl amount
     * 
     * @param float $incl Total amount including BTW
     * @param string $rate BTW rate
     * @return array ['valid' => bool, 'excl' => float, 'vat' => float, 'message' => string]
     */
    public function calculateFromTotal(float $incl, string $rate): array
    {
        if (!in_array($rate, self::VALID_RATES)) {
            return [
                'valid' => false,
                'excl' => null,
                'vat' => null,
                'message' => "Ongeldig BTW tarief: {$rate}",
            ];
        }
        
        // Special cases
        if ($rate === '0' || $rate === 'verlegd') {
            return [
                'valid' => true,
                'excl' => $incl,
                'vat' => 0.00,
                'message' => "BTW {$rate}",
            ];
        }
        
        // Calculate excl from incl
        $rateDecimal = (float)$rate / 100;
        $divisor = 1 + $rateDecimal;
        
        $excl = round($incl / $divisor, 2);
        $vat = round($incl - $excl, 2);
        
        // Verify calculation (reverse check)
        $verification = $this->validate($excl, $vat, $rate);
        
        return [
            'valid' => $verification['valid'],
            'excl' => $excl,
            'vat' => $vat,
            'message' => $verification['message'],
        ];
    }
    
    /**
     * Get BTW rate from percentage
     * Normalizes input to our standard format
     */
    public function normalizeRate($input): ?string
    {
        if (is_null($input)) {
            return null;
        }
        
        $input = (string)$input;
        
        // Already in correct format
        if (in_array($input, self::VALID_RATES)) {
            return $input;
        }
        
        // Try to extract number
        if (preg_match('/21/', $input)) return '21';
        if (preg_match('/9/', $input)) return '9';
        if (preg_match('/0/', $input)) return '0';
        if (preg_match('/verlegd|reverse|shifted/i', $input)) return 'verlegd';
        
        return null;
    }
    
    /**
     * Get all valid BTW rates
     */
    public function getValidRates(): array
    {
        return self::VALID_RATES;
    }
    
    /**
     * Get rate description in Dutch
     */
    public function getRateDescription(string $rate): string
    {
        return match($rate) {
            '21' => '21% (Standaard)',
            '9' => '9% (Verlaagd)',
            '0' => '0% (Export/Intracommunautair)',
            'verlegd' => 'BTW Verlegd',
            default => 'Onbekend',
        };
    }
}

