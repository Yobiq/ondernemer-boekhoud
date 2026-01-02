<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class OcrSpaceEngine implements OcrEngine
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.ocr.space/parse/image';
    protected array $confidenceScores = [];
    
    public function __construct()
    {
        $this->apiKey = config('ocr.ocrspace_api_key', env('OCRSPACE_API_KEY', 'K81873206488957'));
    }
    
    /**
     * Process document using OCR.space API
     * Returns NORMALIZED structure as per spec
     */
    public function process(string $filePath): array
    {
        if (!$this->isAvailable()) {
            Log::warning('OCR.space API not configured, using fallback');
            return $this->getEmptyStructure();
        }

        try {
            // Determine file type
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if ($extension === 'pdf') {
                return $this->processPdf($filePath);
            } else {
                return $this->processImage($filePath);
            }
            
        } catch (\Exception $e) {
            Log::error('OCR.space processing failed: ' . $e->getMessage());
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Process PDF file
     */
    protected function processPdf(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                Log::error('OCR.space: PDF file does not exist', ['path' => $filePath]);
                return $this->getEmptyStructure();
            }
            
            $fileContents = file_get_contents($filePath);
            if ($fileContents === false) {
                Log::error('OCR.space: Could not read PDF file', ['path' => $filePath]);
                return $this->getEmptyStructure();
            }
            
            Log::info('OCR.space: Sending PDF to API', [
                'file_path' => $filePath,
                'file_size' => strlen($fileContents),
                'api_key' => substr($this->apiKey, 0, 5) . '...',
            ]);
            
            // OCR.space API parameters - only include valid ones
            $response = Http::timeout(180) // PDFs can take longer
                ->asMultipart()
                ->attach('file', $fileContents, basename($filePath))
                ->post($this->apiUrl, [
                    'apikey' => $this->apiKey,
                    'language' => 'dut', // Dutch
                    'OCREngine' => 2, // Use OCR Engine 2 for better accuracy
                    // Removed invalid parameters: isOverlayRequired, detectOrientation, scale
                ]);
            
            if (!$response->successful()) {
                Log::error('OCR.space API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getEmptyStructure();
            }
            
            return $this->parseResponse($response->json(), $filePath);
            
        } catch (\Exception $e) {
            Log::error('OCR.space PDF processing failed: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Process image file
     */
    protected function processImage(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                Log::error('OCR.space: Image file does not exist', ['path' => $filePath]);
                return $this->getEmptyStructure();
            }
            
            $fileContents = file_get_contents($filePath);
            if ($fileContents === false) {
                Log::error('OCR.space: Could not read image file', ['path' => $filePath]);
                return $this->getEmptyStructure();
            }
            
            Log::info('OCR.space: Sending image to API', [
                'file_path' => $filePath,
                'file_size' => strlen($fileContents),
                'api_key' => substr($this->apiKey, 0, 5) . '...',
            ]);
            
            // OCR.space API parameters - only include valid ones
            $response = Http::timeout(120) // Increased timeout for large images
                ->asMultipart()
                ->attach('file', $fileContents, basename($filePath))
                ->post($this->apiUrl, [
                    'apikey' => $this->apiKey,
                    'language' => 'dut', // Dutch
                    'OCREngine' => 2, // Use OCR Engine 2 for better accuracy
                    // Removed invalid parameters: isOverlayRequired, detectOrientation, scale
                ]);
            
            if (!$response->successful()) {
                Log::error('OCR.space API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getEmptyStructure();
            }
            
            return $this->parseResponse($response->json(), $filePath);
            
        } catch (\Exception $e) {
            Log::error('OCR.space image processing failed: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Parse OCR.space API response to normalized structure
     */
    protected function parseResponse(?array $response, string $filePath): array
    {
        $data = $this->getEmptyStructure();
        
        // Log full response for debugging
        Log::info('OCR.space API Response', [
            'has_response' => !empty($response),
            'response_keys' => $response ? array_keys($response) : [],
            'error_message' => $response['ErrorMessage'] ?? null,
            'processing_time' => $response['ProcessingTimeInMilliseconds'] ?? null,
        ]);
        
        if (!$response) {
            Log::warning('OCR.space returned null response');
            return $data;
        }
        
        // Check for API errors
        if (isset($response['ErrorMessage'])) {
            $errorMessage = is_array($response['ErrorMessage']) 
                ? implode('; ', $response['ErrorMessage']) 
                : $response['ErrorMessage'];
            
            if (!empty($errorMessage)) {
                Log::error('OCR.space API Error: ' . $errorMessage, [
                    'error_code' => $response['ErrorCode'] ?? null,
                    'is_errored' => $response['IsErroredOnProcessing'] ?? false,
                    'ocr_exit_code' => $response['OCRExitCode'] ?? null,
                ]);
                return $data;
            }
        }
        
        if (!isset($response['ParsedResults']) || empty($response['ParsedResults'])) {
            Log::warning('OCR.space returned empty ParsedResults', [
                'response' => $response,
            ]);
            return $data;
        }
        
        // Combine all parsed results (for multi-page PDFs)
        $allText = '';
        $confidenceScores = [];
        
        foreach ($response['ParsedResults'] as $index => $result) {
            // Check for ParsedText
            if (isset($result['ParsedText'])) {
                $parsedText = trim($result['ParsedText']);
                if (!empty($parsedText)) {
                    $allText .= $parsedText . "\n";
                    Log::info("OCR.space extracted text from page " . ($index + 1), [
                        'text_length' => strlen($parsedText),
                        'preview' => substr($parsedText, 0, 200),
                    ]);
                } else {
                    Log::warning("OCR.space ParsedText is empty for page " . ($index + 1), [
                        'result_keys' => array_keys($result),
                    ]);
                }
            } else {
                Log::warning("OCR.space result has no ParsedText", [
                    'result_index' => $index,
                    'result_keys' => array_keys($result),
                    'error_message' => $result['ErrorMessage'] ?? null,
                ]);
            }
            
            // Extract confidence scores if available
            if (isset($result['TextOverlay']['HasOverlay']) && $result['TextOverlay']['HasOverlay']) {
                // OCR.space doesn't provide per-word confidence, but we can estimate
                // based on the quality of the result
                if (isset($result['TextOverlay']['Message'])) {
                    // If there's a message, confidence might be lower
                    $confidenceScores[] = 85; // Default confidence for OCR.space
                } else {
                    $confidenceScores[] = 90; // Higher confidence if no issues
                }
            }
        }
        
        $data['raw_text'] = trim($allText);
        
        // Log extraction summary
        Log::info('OCR.space extraction summary', [
            'raw_text_length' => strlen($data['raw_text']),
            'has_text' => !empty($data['raw_text']),
            'pages_processed' => count($response['ParsedResults']),
        ]);
        
        // If no text extracted, log warning
        if (empty($data['raw_text'])) {
            Log::warning('OCR.space extracted NO text from document', [
                'file_path' => $filePath,
                'response_structure' => array_keys($response),
                'parsed_results_count' => count($response['ParsedResults']),
            ]);
        }
        
        // Calculate average confidence
        if (!empty($confidenceScores)) {
            $this->confidenceScores = [
                'average' => round(array_sum($confidenceScores) / count($confidenceScores), 2),
                'min' => min($confidenceScores),
                'max' => max($confidenceScores),
                'count' => count($confidenceScores),
            ];
        } else {
            $this->confidenceScores = [
                'average' => 85, // Default confidence for OCR.space
                'min' => 80,
                'max' => 90,
                'count' => 1,
            ];
        }
        
        // Extract structured data from text
        $structured = $this->extractStructuredData($filePath, $allText);
        $data = array_merge_recursive($data, $structured);
        
        return $data;
    }
    
    /**
     * Extract structured data (tables, invoices) from text
     */
    protected function extractStructuredData(string $filePath, string $text): array
    {
        $data = $this->getEmptyStructure();
        
        // Enhanced invoice number extraction
        if (preg_match('/(?:factuur|invoice|factuurnummer|invoice\s*number)[\s:]*([A-Z0-9\-]+)/i', $text, $matches)) {
            $data['invoice']['number'] = trim($matches[1]);
        }
        
        // Enhanced date extraction (multiple formats)
        $datePatterns = [
            '/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\b/', // DD-MM-YYYY
            '/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/', // YYYY-MM-DD
            '/\b(\d{1,2})\s+(januari|februari|maart|april|mei|juni|juli|augustus|september|oktober|november|december)\s+(\d{4})\b/i', // Dutch month names
        ];
        
        $months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 
                   'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
        
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                if (isset($matches[3]) && in_array(strtolower($matches[2]), $months)) {
                    $monthNum = array_search(strtolower($matches[2]), $months) + 1;
                    $data['invoice']['date'] = sprintf('%s-%02d-%02d', $matches[3], $monthNum, $matches[1]);
                } elseif (isset($matches[3])) {
                    // Check if it's DD-MM-YYYY or YYYY-MM-DD format
                    if (strlen($matches[1]) === 4) {
                        // YYYY-MM-DD
                        $data['invoice']['date'] = sprintf('%s-%02d-%02d', $matches[1], $matches[2], $matches[3]);
                    } else {
                        // DD-MM-YYYY
                        $data['invoice']['date'] = sprintf('%s-%02d-%02d', $matches[3], $matches[2], $matches[1]);
                    }
                }
                break;
            }
        }
        
        // Enhanced amount extraction with multiple formats
        $amountPatterns = [
            '/€\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/', // € 1.234,56
            '/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)\s*€/', // 1.234,56 €
            '/EUR\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // EUR 1.234,56
            '/Totaal[:\s]+€?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // Totaal: € 1.234,56
            '/Totaal\s+inclusief[:\s]+€?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // Totaal inclusief: € 1.234,56
            '/Totaal\s+exclusief[:\s]+€?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // Totaal exclusief: € 1.234,56
            '/BTW[:\s]+€?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // BTW: € 1.234,56
            '/bedrag[:\s]+€?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)/i', // Bedrag: € 1.234,56
        ];
        
        $foundAmounts = [];
        
        foreach ($amountPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $allMatches, PREG_SET_ORDER)) {
                foreach ($allMatches as $matches) {
                    $amountStr = str_replace(['.', ','], ['', '.'], $matches[1]);
                    $amount = (float)$amountStr;
                    
                    if ($amount > 0) {
                        $foundAmounts[] = $amount;
                    }
                }
            }
        }
        
        // Use the largest amount as total (usually the incl amount)
        if (!empty($foundAmounts)) {
            $totalAmount = max($foundAmounts);
            $data['amounts']['incl'] = $totalAmount;
            
            // Look for VAT rate in text
            $vatRate = null;
            if (preg_match('/(?:21|hoog|hoge)\s*%?\s*BTW/i', $text)) {
                $vatRate = '21';
            } elseif (preg_match('/(?:9|laag|lage)\s*%?\s*BTW/i', $text)) {
                $vatRate = '9';
            } elseif (preg_match('/(?:0|nul|geen)\s*%?\s*BTW/i', $text)) {
                $vatRate = '0';
            }
            
            if ($vatRate) {
                $data['amounts']['vat_rate'] = $vatRate;
                if ($vatRate === '21') {
                    $data['amounts']['excl'] = round($totalAmount / 1.21, 2);
                    $data['amounts']['vat'] = round($totalAmount - $data['amounts']['excl'], 2);
                } elseif ($vatRate === '9') {
                    $data['amounts']['excl'] = round($totalAmount / 1.09, 2);
                    $data['amounts']['vat'] = round($totalAmount - $data['amounts']['excl'], 2);
                } else {
                    $data['amounts']['excl'] = $totalAmount;
                    $data['amounts']['vat'] = 0;
                }
            }
            
            Log::info('OCR: Extracted amounts', [
                'incl' => $data['amounts']['incl'],
                'excl' => $data['amounts']['excl'] ?? null,
                'vat' => $data['amounts']['vat'] ?? null,
                'vat_rate' => $data['amounts']['vat_rate'] ?? null,
            ]);
        }
        
        // Enhanced supplier name extraction (look for common patterns)
        $supplierPatterns = [
            '/(?:van|leverancier|supplier|aan|naar|verkoper)[\s:]+([A-Z][A-Za-z\s&\.]+?)(?:\n|$|BTW|KVK|IBAN)/i',
            '/^([A-Z][A-Za-z\s&\.]{3,50})(?:\n|BTW|KVK|IBAN)/m', // Company name at start of line
        ];
        
        foreach ($supplierPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $supplierName = trim($matches[1]);
                // Filter out common false positives
                if (strlen($supplierName) > 3 && !preg_match('/^(FACTUUR|INVOICE|BONNETJE|BON|Datum|Datum:)/i', $supplierName)) {
                    $data['supplier']['name'] = $supplierName;
                    break;
                }
            }
        }
        
        // Enhanced BTW number extraction
        if (preg_match('/BTW[-\s]?(?:nummer|nr|number)?[\s:]*([A-Z]{2}\s?\d{2}\s?\d{3}\s?\d{3}\s?[B]\s?\d{2})/i', $text, $matches)) {
            $data['supplier']['vat_number'] = preg_replace('/\s+/', '', strtoupper($matches[1]));
        } elseif (preg_match('/NL\s?\d{9}B\d{2}/i', $text, $matches)) {
            $data['supplier']['vat_number'] = preg_replace('/\s+/', '', strtoupper($matches[0]));
        }
        
        // Enhanced IBAN extraction
        if (preg_match('/IBAN[\s:]*([A-Z]{2}\d{2}[A-Z0-9]{4,30})/i', $text, $matches)) {
            $data['supplier']['iban'] = strtoupper($matches[1]);
        } elseif (preg_match('/NL\d{2}[A-Z]{4}\d{10}/i', $text, $matches)) {
            $data['supplier']['iban'] = $matches[0];
        }
        
        return $data;
    }
    
    /**
     * Get confidence scores for the last processed document
     */
    public function getConfidenceScores(): array
    {
        return $this->confidenceScores;
    }
    
    /**
     * Check if OCR.space API is available and configured
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }
    
    /**
     * Get the standardized empty OCR structure
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
}

