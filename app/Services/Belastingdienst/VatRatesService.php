<?php

namespace App\Services\Belastingdienst;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class VatRatesService
{
    /**
     * Dutch VAT rates (current as of 2024)
     */
    protected array $dutchRates = [
        'NL21' => 21.0,  // Hoog tarief
        'NL9' => 9.0,    // Laag tarief
        'NL0' => 0.0,    // Vrijgesteld
        'VERL' => 0.0,   // Verleggingsregeling
    ];
    
    /**
     * EU VAT rates by country (standard rates)
     */
    protected array $euRates = [
        'BE' => 21.0, 'BG' => 20.0, 'CZ' => 21.0, 'DK' => 25.0,
        'DE' => 19.0, 'EE' => 20.0, 'IE' => 23.0, 'GR' => 24.0,
        'ES' => 21.0, 'FR' => 20.0, 'HR' => 25.0, 'IT' => 22.0,
        'CY' => 19.0, 'LV' => 21.0, 'LT' => 21.0, 'LU' => 17.0,
        'HU' => 27.0, 'MT' => 18.0, 'NL' => 21.0, 'AT' => 20.0,
        'PL' => 23.0, 'PT' => 23.0, 'RO' => 19.0, 'SI' => 22.0,
        'SK' => 20.0, 'FI' => 24.0, 'SE' => 25.0,
    ];
    
    /**
     * Historical VAT rate changes (NL)
     */
    protected array $historicalRates = [
        // Format: 'YYYY-MM-DD' => ['NL21' => rate, 'NL9' => rate]
        '2012-10-01' => ['NL21' => 21.0, 'NL9' => 6.0], // 6% was low rate until 2019
        '2019-01-01' => ['NL21' => 21.0, 'NL9' => 9.0], // Changed to 9%
    ];
    
    /**
     * Get current VAT rate for a code
     */
    public function getCurrentRate(string $code): float
    {
        $code = strtoupper($code);
        
        // Check Dutch rates
        if (isset($this->dutchRates[$code])) {
            return $this->dutchRates[$code];
        }
        
        // Check if it's an EU country code
        if (strlen($code) === 2 && isset($this->euRates[$code])) {
            return $this->euRates[$code];
        }
        
        // Try to extract country code from VAT code (e.g., "NL21" -> "NL")
        $countryCode = substr($code, 0, 2);
        if (isset($this->euRates[$countryCode])) {
            return $this->euRates[$countryCode];
        }
        
        // Default to 21% if unknown
        return 21.0;
    }
    
    /**
     * Get historical VAT rate for a specific date
     */
    public function getHistoricalRate(string $code, Carbon $date): float
    {
        $code = strtoupper($code);
        
        // Check historical rates for NL
        if (str_starts_with($code, 'NL')) {
            foreach ($this->historicalRates as $changeDate => $rates) {
                if ($date->gte(Carbon::parse($changeDate))) {
                    $rateKey = $code;
                    if (isset($rates[$rateKey])) {
                        return $rates[$rateKey];
                    }
                }
            }
        }
        
        // For other dates, use current rate (historical EU rates not tracked)
        return $this->getCurrentRate($code);
    }
    
    /**
     * Validate VAT rate against Belastingdienst rules
     */
    public function validateRate(\App\Models\Document $document): bool
    {
        $vatCode = $document->vat_code;
        $vatRate = $document->vat_rate;
        $amountExcl = $document->amount_excl;
        $amountVat = $document->amount_vat;
        $amountIncl = $document->amount_incl;
        
        if (!$vatCode) {
            return false;
        }
        
        // If vat_rate is not set, we can't validate - but this is not necessarily an error
        // (vat_rate might be set from vat_code)
        if (!$vatRate) {
            // Try to infer vat_rate from vat_code
            $expectedRate = $this->getCurrentRate($vatCode);
            // If we can determine the rate from code, that's acceptable
            if ($expectedRate > 0 || $vatCode === 'VERL' || $vatCode === 'NL0') {
                return true; // Rate can be inferred from code
            }
            return false;
        }
        
        // Get expected rate for code
        $expectedRate = $this->getCurrentRate($vatCode);
        
        // Normalize vat_rate - handle string "21" vs float 21.0
        $vatRateFloat = is_numeric($vatRate) ? (float)$vatRate : 0.0;
        
        // Special handling for "verlegd" - should match VERL code
        if ($vatRate === 'verlegd' || $vatRate === 'VERL') {
            return ($vatCode === 'VERL' || $expectedRate === 0.0);
        }
        
        // Check if rate matches (with small tolerance for rounding)
        if (abs($vatRateFloat - $expectedRate) > 0.01) {
            \Log::debug('VAT Rate Mismatch', [
                'vat_code' => $vatCode,
                'vat_rate' => $vatRate,
                'vat_rate_float' => $vatRateFloat,
                'expected_rate' => $expectedRate,
                'difference' => abs($vatRateFloat - $expectedRate),
            ]);
            return false;
        }
        
        // Validate calculation
        if ($amountExcl && $amountVat && $amountIncl) {
            $calculatedVat = $amountExcl * ($expectedRate / 100);
            $calculatedIncl = $amountExcl + $calculatedVat;
            
            // Allow 0.01 tolerance for rounding
            if (abs($amountVat - $calculatedVat) > 0.01) {
                return false;
            }
            
            if (abs($amountIncl - $calculatedIncl) > 0.01) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get all applicable VAT rates for a country
     */
    public function getApplicableRates(string $country = 'NL'): array
    {
        $country = strtoupper($country);
        
        if ($country === 'NL') {
            return [
                'NL21' => [
                    'code' => 'NL21',
                    'rate' => 21.0,
                    'description' => 'Hoog tarief (algemeen)',
                    'category' => 'standard',
                ],
                'NL9' => [
                    'code' => 'NL9',
                    'rate' => 9.0,
                    'description' => 'Laag tarief (voeding, medicijnen, boeken)',
                    'category' => 'reduced',
                ],
                'NL0' => [
                    'code' => 'NL0',
                    'rate' => 0.0,
                    'description' => 'Vrijgesteld van BTW',
                    'category' => 'exempt',
                ],
                'VERL' => [
                    'code' => 'VERL',
                    'rate' => 0.0,
                    'description' => 'Verleggingsregeling (BTW verschuldigd door afnemer)',
                    'category' => 'reverse_charge',
                ],
            ];
        }
        
        // For EU countries, return standard rate
        if (isset($this->euRates[$country])) {
            return [
                $country => [
                    'code' => $country,
                    'rate' => $this->euRates[$country],
                    'description' => "Standaard BTW tarief voor {$country}",
                    'category' => 'standard',
                ],
            ];
        }
        
        return [];
    }
    
    /**
     * Get VAT rate description
     */
    public function getRateDescription(string $code): string
    {
        $rates = $this->getApplicableRates('NL');
        
        if (isset($rates[$code])) {
            return $rates[$code]['description'];
        }
        
        return "BTW code: {$code}";
    }
    
    /**
     * Update rates from external source (can be called via cron)
     */
    public function updateRatesFromSource(): bool
    {
        try {
            // In the future, this could fetch from Belastingdienst API or EU VAT database
            // For now, rates are hardcoded but this method allows for future expansion
            
            Cache::put('vat_rates_updated_at', now(), now()->addDays(30));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update VAT rates: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get rate change history for a code
     */
    public function getRateHistory(string $code): array
    {
        $code = strtoupper($code);
        $history = [];
        
        if (str_starts_with($code, 'NL')) {
            foreach ($this->historicalRates as $date => $rates) {
                if (isset($rates[$code])) {
                    $history[] = [
                        'date' => $date,
                        'rate' => $rates[$code],
                    ];
                }
            }
        }
        
        // Add current rate
        $history[] = [
            'date' => 'current',
            'rate' => $this->getCurrentRate($code),
        ];
        
        return $history;
    }
}

