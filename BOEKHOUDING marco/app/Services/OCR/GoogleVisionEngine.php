<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;

class GoogleVisionEngine implements OcrEngine
{
    protected ?\Google\Cloud\Vision\V1\ImageAnnotatorClient $client = null;
    protected array $confidenceScores = [];
    
    public function __construct()
    {
        $this->initializeClient();
    }
    
    /**
     * Initialize Google Cloud Vision client
     */
    protected function initializeClient(): void
    {
        if (!$this->isAvailable()) {
            return;
        }
        
        try {
            $credentialsPath = config('services.google.credentials_path');
            
            if ($credentialsPath && file_exists($credentialsPath)) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
            }
            
            $this->client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Vision client: ' . $e->getMessage());
            $this->client = null;
        }
    }
    
    /**
     * Process document using Google Cloud Vision
     */
    public function process(string $filePath): array
    {
        if (!$this->isAvailable() || !$this->client) {
            Log::warning('Google Vision not available, returning empty structure');
            return $this->getEmptyStructure();
        }
        
        try {
            $imageContent = file_get_contents($filePath);
            
            // Create image object
            $image = new \Google\Cloud\Vision\V1\Image();
            $image->setContent($imageContent);
            
            // Perform text detection
            $response = $this->client->textDetection($image);
            $annotations = $response->getTextAnnotations();
            
            if (count($annotations) === 0) {
                return $this->getEmptyStructure();
            }
            
            // First annotation contains all text
            $fullTextAnnotation = $annotations[0];
            $text = $fullTextAnnotation->getDescription();
            
            // Extract structured data
            $data = $this->parseInvoiceData($text);
            
            // Calculate confidence from all annotations
            $this->calculateConfidenceScores($annotations);
            
            // Try document text detection for better structured extraction
            try {
                $docResponse = $this->client->documentTextDetection($image);
                $docAnnotation = $docResponse->getFullTextAnnotation();
                
                if ($docAnnotation) {
                    $structuredData = $this->extractStructuredData($docAnnotation);
                    $data = array_merge_recursive($data, $structuredData);
                }
            } catch (\Exception $e) {
                Log::warning('Document text detection failed, using basic text: ' . $e->getMessage());
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Log::error('Google Vision processing failed: ' . $e->getMessage());
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Extract structured data from document annotation
     */
    protected function extractStructuredData($docAnnotation): array
    {
        $data = $this->getEmptyStructure();
        $text = $docAnnotation->getText();
        $data['raw_text'] = $text;
        
        // Extract pages and blocks for better structure
        $pages = $docAnnotation->getPages();
        
        foreach ($pages as $page) {
            $blocks = $page->getBlocks();
            
            foreach ($blocks as $block) {
                $paragraphs = $block->getParagraphs();
                
                foreach ($paragraphs as $paragraph) {
                    $words = $paragraph->getWords();
                    $wordText = '';
                    
                    foreach ($words as $word) {
                        $symbols = $word->getSymbols();
                        foreach ($symbols as $symbol) {
                            $wordText .= $symbol->getText();
                        }
                        $wordText .= ' ';
                    }
                    
                    // Try to identify invoice fields
                    $wordTextLower = strtolower(trim($wordText));
                    
                    if (str_contains($wordTextLower, 'factuur') || str_contains($wordTextLower, 'invoice')) {
                        // Next word might be invoice number
                        $data['invoice']['number'] = trim($wordText);
                    } elseif (preg_match('/\b(\d{2}[-\/]\d{2}[-\/]\d{4})\b/', $wordText, $matches)) {
                        $data['invoice']['date'] = $this->parseDate($matches[1]);
                    } elseif (preg_match('/€?\s*(\d+[.,]\d{2})\b/', $wordText, $matches)) {
                        $amount = $this->parseAmount($matches[1]);
                        if ($amount && !$data['amounts']['incl']) {
                            $data['amounts']['incl'] = $amount;
                        }
                    }
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Calculate confidence scores from annotations
     */
    protected function calculateConfidenceScores(array $annotations): void
    {
        $scores = [];
        
        foreach ($annotations as $annotation) {
            // Google Vision doesn't provide per-word confidence in basic API
            // But we can estimate based on detection quality
            if ($annotation->getDescription()) {
                $scores[] = 90.0; // Estimated confidence
            }
        }
        
        if (count($scores) > 0) {
            $this->confidenceScores = [
                'average' => round(array_sum($scores) / count($scores), 2),
                'min' => min($scores),
                'max' => max($scores),
                'count' => count($scores),
            ];
        }
    }
    
    /**
     * Get confidence scores
     */
    public function getConfidenceScores(): array
    {
        return $this->confidenceScores;
    }
    
    /**
     * Parse date string
     */
    protected function parseDate(string $dateStr): ?string
    {
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, trim($dateStr));
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }
    
    /**
     * Parse amount string
     */
    protected function parseAmount(string $amountStr): ?float
    {
        $amountStr = preg_replace('/[€$£\s]/', '', $amountStr);
        
        if (preg_match('/(\d{1,3}(?:\.\d{3})*),(\d{2})/', $amountStr, $matches)) {
            return (float)(str_replace('.', '', $matches[1]) . '.' . $matches[2]);
        }
        
        if (preg_match('/(\d+(?:\.\d{2})?)/', $amountStr, $matches)) {
            return (float)$matches[1];
        }
        
        return null;
    }
    
    /**
     * Check if Google Vision is available
     */
    public function isAvailable(): bool
    {
        return class_exists('\Google\Cloud\Vision\V1\ImageAnnotatorClient')
            && (config('services.google.credentials_path') || config('services.google.api_key'));
    }
    
    /**
     * Get empty structure
     */
    protected function getEmptyStructure(): array
    {
        return [
            'supplier' => [
                'name' => null,
                'vat_number' => null,
                'iban' => null,
            ],
            'invoice' => [
                'number' => null,
                'date' => null,
            ],
            'amounts' => [
                'excl' => null,
                'vat' => null,
                'incl' => null,
                'vat_rate' => null,
            ],
            'currency' => 'EUR',
            'raw_text' => '',
        ];
    }
    
    /**
     * Parse invoice data from text
     */
    protected function parseInvoiceData(string $text): array
    {
        $data = $this->getEmptyStructure();
        $data['raw_text'] = $text;
        
        // Extract dates
        if (preg_match('/\b(\d{2})[-\/](\d{2})[-\/](\d{4})\b/', $text, $matches)) {
            $data['invoice']['date'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        // Extract amounts
        if (preg_match('/€?\s*(\d+)[,.](\d{2})/', $text, $matches)) {
            $amount = (float)($matches[1] . '.' . $matches[2]);
            $data['amounts']['incl'] = $amount;
            
            if (preg_match('/21%?\s*BTW/i', $text)) {
                $data['amounts']['vat_rate'] = '21';
                $data['amounts']['excl'] = round($amount / 1.21, 2);
                $data['amounts']['vat'] = round($amount - $data['amounts']['excl'], 2);
            } elseif (preg_match('/9%?\s*BTW/i', $text)) {
                $data['amounts']['vat_rate'] = '9';
                $data['amounts']['excl'] = round($amount / 1.09, 2);
                $data['amounts']['vat'] = round($amount - $data['amounts']['excl'], 2);
            }
        }
        
        // Extract BTW number
        if (preg_match('/NL\s*\d{9}B\d{2}/', $text, $matches)) {
            $data['supplier']['vat_number'] = str_replace(' ', '', $matches[0]);
        }
        
        // Extract IBAN
        if (preg_match('/NL\d{2}[A-Z]{4}\d{10}/', $text, $matches)) {
            $data['supplier']['iban'] = $matches[0];
        }
        
        return $data;
    }
}

