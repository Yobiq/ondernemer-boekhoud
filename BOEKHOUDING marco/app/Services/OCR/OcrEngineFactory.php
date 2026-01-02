<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OcrEngineFactory
{
    /**
     * Create OCR engine based on configuration and document type
     * Prioritizes OCR.space as primary, Tesseract as fallback
     */
    public static function create(?string $preferredEngine = null, ?string $documentType = null): OcrEngine
    {
        $preferredEngine = $preferredEngine ?? config('ocr.default_engine', 'ocrspace');
        $documentType = $documentType ?? 'invoice';
        
        // Get engine configuration for document type
        $engineConfig = config("ocr.engines.{$documentType}", []);
        $engine = $engineConfig['engine'] ?? $preferredEngine;
        
        // Always try OCR.space first if it's the preferred engine or default
        if ($engine === 'ocrspace' || $preferredEngine === 'ocrspace') {
            $ocrSpaceEngine = new OcrSpaceEngine();
            if ($ocrSpaceEngine->isAvailable()) {
                Log::info("Using OCR.space as primary engine for document type: {$documentType}");
                return $ocrSpaceEngine;
            }
            Log::warning("OCR.space not available, trying fallback engines");
        }
        
        // Try to create preferred engine, fallback to others
        $engines = self::getEnginePriority($engine, $documentType);
        
        foreach ($engines as $engineName) {
            // Skip OCR.space if we already tried it
            if ($engineName === 'ocrspace' && isset($ocrSpaceEngine)) {
                continue;
            }
            
            try {
                $ocrEngine = self::createEngine($engineName);
                
                if ($ocrEngine && $ocrEngine->isAvailable()) {
                    Log::info("Using OCR engine: {$engineName} for document type: {$documentType}");
                    return $ocrEngine;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to create OCR engine {$engineName}: " . $e->getMessage());
                continue;
            }
        }
        
        // Fallback to Tesseract (always available as fallback)
        Log::warning('All OCR engines failed, using Tesseract fallback');
        return new TesseractEngine();
    }
    
    /**
     * Create specific OCR engine instance
     */
    protected static function createEngine(string $engineName): ?OcrEngine
    {
        return match(strtolower($engineName)) {
            'ocrspace', 'ocr.space', 'ocr_space' => new OcrSpaceEngine(),
            'aws', 'textract', 'aws_textract' => new AwsTextractEngine(),
            'google', 'vision', 'google_vision' => new GoogleVisionEngine(),
            'azure', 'form_recognizer', 'azure_form_recognizer' => new AzureFormRecognizerEngine(),
            'tesseract', 'local' => new TesseractEngine(),
            default => null,
        };
    }
    
    /**
     * Get engine priority list based on document complexity and cost
     */
    protected static function getEnginePriority(string $preferred, string $documentType): array
    {
        $priority = [$preferred];
        
        // For complex documents (invoices, forms), prefer cloud OCR
        if (in_array($documentType, ['invoice', 'form', 'receipt'])) {
            $cloudEngines = ['ocrspace', 'aws_textract', 'google_vision', 'azure_form_recognizer'];
            
            // Add cloud engines if not already preferred
            foreach ($cloudEngines as $cloudEngine) {
                if (!in_array($cloudEngine, $priority)) {
                    $priority[] = $cloudEngine;
                }
            }
        }
        
        // Always add Tesseract as final fallback
        if (!in_array('tesseract', $priority)) {
            $priority[] = 'tesseract';
        }
        
        return $priority;
    }
    
    /**
     * Get best engine for document based on cost and complexity
     * Prioritizes OCR.space as primary, Tesseract as fallback
     */
    public static function getBestEngine(string $filePath, string $documentType = 'invoice'): OcrEngine
    {
        // Always try OCR.space first (primary engine)
        $ocrSpaceEngine = new OcrSpaceEngine();
        if ($ocrSpaceEngine->isAvailable()) {
            Log::info("OCR: Using OCR.space as primary engine");
            return $ocrSpaceEngine;
        }
        
        // Fallback to Tesseract if OCR.space is not available
        Log::info("OCR: OCR.space not available, falling back to Tesseract");
        return new TesseractEngine();
    }
    
    /**
     * Batch process documents with optimal engine selection
     */
    public static function batchProcess(array $filePaths, string $documentType = 'invoice'): array
    {
        $results = [];
        
        foreach ($filePaths as $filePath) {
            try {
                $engine = self::getBestEngine($filePath, $documentType);
                $results[$filePath] = $engine->process($filePath);
            } catch (\Exception $e) {
                Log::error("Batch OCR processing failed for {$filePath}: " . $e->getMessage());
                $results[$filePath] = (new TesseractEngine())->getEmptyStructure();
            }
        }
        
        return $results;
    }
    
    /**
     * Get available engines
     */
    public static function getAvailableEngines(): array
    {
        $engines = [
            'ocrspace' => new OcrSpaceEngine(),
            'tesseract' => new TesseractEngine(),
            'aws_textract' => new AwsTextractEngine(),
            'google_vision' => new GoogleVisionEngine(),
            'azure_form_recognizer' => new AzureFormRecognizerEngine(),
        ];
        
        $available = [];
        
        foreach ($engines as $name => $engine) {
            if ($engine->isAvailable()) {
                $available[$name] = $name;
            }
        }
        
        return $available;
    }
}

