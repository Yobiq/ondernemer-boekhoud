<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use App\Services\OCR\OcrSpaceEngine;
use App\Services\OCR\TesseractEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    protected OcrEngine $engine;
    
    public function __construct()
    {
        // Use OCR.space by default (better accuracy, API-based, no local dependencies)
        // Fallback to Tesseract if OCR.space is not available
        $ocrSpaceEngine = new OcrSpaceEngine();
        if ($ocrSpaceEngine->isAvailable()) {
            $this->engine = $ocrSpaceEngine;
        } else {
            // Fallback to Tesseract if OCR.space is not configured
            $this->engine = new TesseractEngine();
        }
    }
    
    /**
     * Process a document and return normalized OCR data
     * 
     * @param string $filePath Path to document in storage
     * @return array Normalized OCR structure
     */
    public function processDocument(string $filePath): array
    {
        try {
            // Get full path to file - handle both 'local' disk (which points to private) and direct paths
            $fullPath = Storage::disk('local')->path($filePath);
            
            // If file doesn't exist, try alternative paths
            if (!file_exists($fullPath)) {
                // Try private path directly
                $privatePath = storage_path('app/private/' . $filePath);
                if (file_exists($privatePath)) {
                    $fullPath = $privatePath;
                } else {
                    // Try without directory prefix if file_path includes it
                    $basename = basename($filePath);
                    $altPath = storage_path('app/private/client-uploads/' . $basename);
                    if (file_exists($altPath)) {
                        $fullPath = $altPath;
                    } else {
                        Log::error("OCR: File not found: {$filePath} (tried: {$fullPath}, {$privatePath}, {$altPath})");
                        return $this->getEmptyResult();
                    }
                }
            }
            
            Log::info("OCR: Processing document: {$fullPath}");
            
            // Process with OCR engine
            $result = $this->engine->process($fullPath);
            
            // Validate and normalize result
            $normalized = $this->normalizeResult($result);
            
            Log::info("OCR: Completed processing", [
                'has_supplier' => !empty($normalized['supplier']['name']),
                'has_amounts' => !empty($normalized['amounts']['incl']),
                'has_date' => !empty($normalized['invoice']['date']),
            ]);
            
            return $normalized;
            
        } catch (\Exception $e) {
            Log::error("OCR: Processing failed: " . $e->getMessage());
            return $this->getEmptyResult();
        }
    }
    
    /**
     * Normalize OCR result to standard structure
     * Ensures all required fields exist
     */
    protected function normalizeResult(array $result): array
    {
        return [
            'supplier' => [
                'name' => $result['supplier']['name'] ?? null,
                'vat_number' => $result['supplier']['vat_number'] ?? null,
                'iban' => $result['supplier']['iban'] ?? null,
            ],
            'invoice' => [
                'number' => $result['invoice']['number'] ?? null,
                'date' => $result['invoice']['date'] ?? null,
            ],
            'amounts' => [
                'excl' => $this->normalizeAmount($result['amounts']['excl'] ?? null),
                'vat' => $this->normalizeAmount($result['amounts']['vat'] ?? null),
                'incl' => $this->normalizeAmount($result['amounts']['incl'] ?? null),
                'vat_rate' => $this->normalizeVatRate($result['amounts']['vat_rate'] ?? null),
            ],
            'currency' => $result['currency'] ?? 'EUR',
            'raw_text' => $result['raw_text'] ?? '',
        ];
    }
    
    /**
     * Normalize amount to 2 decimals or null
     */
    protected function normalizeAmount($amount): ?float
    {
        if ($amount === null || $amount === '') {
            return null;
        }
        
        return round((float)$amount, 2);
    }
    
    /**
     * Normalize VAT rate to standard values
     */
    protected function normalizeVatRate($rate): ?string
    {
        if ($rate === null || $rate === '') {
            return null;
        }
        
        $rate = (string)$rate;
        
        // Normalize to our enum values: 21, 9, 0, verlegd
        if (in_array($rate, ['21', '9', '0', 'verlegd'])) {
            return $rate;
        }
        
        // Try to match common variations
        if (preg_match('/21/', $rate)) return '21';
        if (preg_match('/9/', $rate)) return '9';
        if (preg_match('/0|nul|geen/', $rate)) return '0';
        if (preg_match('/verlegd|reverse|shifted/', strtolower($rate))) return 'verlegd';
        
        return null;
    }
    
    /**
     * Get empty result structure
     */
    protected function getEmptyResult(): array
    {
        return [
            'supplier' => ['name' => null, 'vat_number' => null, 'iban' => null],
            'invoice' => ['number' => null, 'date' => null],
            'amounts' => ['excl' => null, 'vat' => null, 'incl' => null, 'vat_rate' => null],
            'currency' => 'EUR',
            'raw_text' => '',
        ];
    }
    
    /**
     * Set custom OCR engine
     */
    public function setEngine(OcrEngine $engine): self
    {
        $this->engine = $engine;
        return $this;
    }
}



