<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AzureFormRecognizerEngine implements OcrEngine
{
    protected string $endpoint;
    protected string $apiKey;
    protected array $confidenceScores = [];
    
    public function __construct()
    {
        $this->endpoint = config('services.azure.form_recognizer.endpoint', '');
        $this->apiKey = config('services.azure.form_recognizer.api_key', '');
    }
    
    /**
     * Process document using Azure Form Recognizer
     */
    public function process(string $filePath): array
    {
        if (!$this->isAvailable()) {
            Log::warning('Azure Form Recognizer not available, returning empty structure');
            return $this->getEmptyStructure();
        }
        
        try {
            // For invoices, use prebuilt invoice model
            $modelId = 'prebuilt-invoice';
            
            // Submit document for analysis
            $result = $this->analyzeDocument($filePath, $modelId);
            
            // Extract structured data
            return $this->extractDataFromResult($result);
            
        } catch (\Exception $e) {
            Log::error('Azure Form Recognizer processing failed: ' . $e->getMessage());
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Analyze document using Azure Form Recognizer
     */
    protected function analyzeDocument(string $filePath, string $modelId): array
    {
        $fileContent = file_get_contents($filePath);
        $base64Content = base64_encode($fileContent);
        
        // Submit for analysis
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint . '/formrecognizer/v2.1/prebuilt/invoice/analyze', [
            'source' => 'data:application/pdf;base64,' . $base64Content,
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Azure API request failed: ' . $response->body());
        }
        
        $operationLocation = $response->header('Operation-Location');
        
        if (!$operationLocation) {
            throw new \Exception('No operation location returned');
        }
        
        // Poll for results
        return $this->waitForResult($operationLocation);
    }
    
    /**
     * Wait for analysis result
     */
    protected function waitForResult(string $operationLocation, int $maxWait = 300): array
    {
        $startTime = time();
        
        while (true) {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
            ])->get($operationLocation);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to get result: ' . $response->body());
            }
            
            $result = $response->json();
            $status = $result['status'] ?? 'unknown';
            
            if ($status === 'succeeded') {
                return $result;
            }
            
            if ($status === 'failed') {
                throw new \Exception('Analysis failed: ' . ($result['error']['message'] ?? 'Unknown error'));
            }
            
            if (time() - $startTime > $maxWait) {
                throw new \Exception('Analysis timeout');
            }
            
            sleep(2);
        }
    }
    
    /**
     * Extract structured data from Azure result
     */
    protected function extractDataFromResult(array $result): array
    {
        $data = $this->getEmptyStructure();
        
        $analyzeResult = $result['analyzeResult'] ?? [];
        $documentResults = $analyzeResult['documentResults'] ?? [];
        
        if (empty($documentResults)) {
            return $data;
        }
        
        $document = $documentResults[0];
        $fields = $document['fields'] ?? [];
        
        // Map Azure fields to our structure
        foreach ($fields as $key => $field) {
            $value = $field['valueString'] ?? $field['valueNumber'] ?? $field['valueDate'] ?? null;
            $confidence = $field['confidence'] ?? 0;
            
            $keyLower = strtolower($key);
            
            if (str_contains($keyLower, 'invoicenumber') || str_contains($keyLower, 'invoiceno')) {
                $data['invoice']['number'] = (string)$value;
            } elseif (str_contains($keyLower, 'invoicedate') || str_contains($keyLower, 'date')) {
                $data['invoice']['date'] = $this->parseDate($value);
            } elseif (str_contains($keyLower, 'total') || str_contains($keyLower, 'amountdue')) {
                $data['amounts']['incl'] = $this->parseAmount($value);
            } elseif (str_contains($keyLower, 'subtotal') || str_contains($keyLower, 'amountexcl')) {
                $data['amounts']['excl'] = $this->parseAmount($value);
            } elseif (str_contains($keyLower, 'totalvat') || str_contains($keyLower, 'tax')) {
                $data['amounts']['vat'] = $this->parseAmount($value);
            } elseif (str_contains($keyLower, 'vendorname') || str_contains($keyLower, 'supplier')) {
                $data['supplier']['name'] = (string)$value;
            } elseif (str_contains($keyLower, 'vendoraddress') || str_contains($keyLower, 'remittanceaddress')) {
                // Could extract address components
            } elseif (str_contains($keyLower, 'vatid') || str_contains($keyLower, 'taxid')) {
                $data['supplier']['vat_number'] = (string)$value;
            }
        }
        
        // Calculate VAT rate if we have excl and incl
        if ($data['amounts']['excl'] && $data['amounts']['incl']) {
            $vatAmount = $data['amounts']['incl'] - $data['amounts']['excl'];
            $vatRate = ($vatAmount / $data['amounts']['excl']) * 100;
            
            if (abs($vatRate - 21) < 1) {
                $data['amounts']['vat_rate'] = '21';
            } elseif (abs($vatRate - 9) < 1) {
                $data['amounts']['vat_rate'] = '9';
            } else {
                $data['amounts']['vat_rate'] = (string)round($vatRate, 2);
            }
            
            if (!$data['amounts']['vat']) {
                $data['amounts']['vat'] = $vatAmount;
            }
        }
        
        // Extract text content
        $pages = $analyzeResult['pages'] ?? [];
        $text = '';
        foreach ($pages as $page) {
            $lines = $page['lines'] ?? [];
            foreach ($lines as $line) {
                $text .= ($line['text'] ?? '') . "\n";
            }
        }
        $data['raw_text'] = $text;
        
        // Calculate confidence scores
        $this->calculateConfidenceScores($fields);
        
        return $data;
    }
    
    /**
     * Calculate confidence scores from fields
     */
    protected function calculateConfidenceScores(array $fields): void
    {
        $scores = [];
        
        foreach ($fields as $field) {
            if (isset($field['confidence'])) {
                $scores[] = (float)$field['confidence'] * 100; // Convert to 0-100 scale
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
    protected function parseDate($dateValue): ?string
    {
        if (is_string($dateValue)) {
            $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, trim($dateValue));
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }
        }
        
        return null;
    }
    
    /**
     * Parse amount value
     */
    protected function parseAmount($amountValue): ?float
    {
        if (is_numeric($amountValue)) {
            return (float)$amountValue;
        }
        
        if (is_string($amountValue)) {
            $amountStr = preg_replace('/[€$£\s]/', '', $amountValue);
            
            if (preg_match('/(\d{1,3}(?:\.\d{3})*),(\d{2})/', $amountStr, $matches)) {
                return (float)(str_replace('.', '', $matches[1]) . '.' . $matches[2]);
            }
            
            if (preg_match('/(\d+(?:\.\d{2})?)/', $amountStr, $matches)) {
                return (float)$matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Check if Azure Form Recognizer is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->endpoint) 
            && !empty($this->apiKey);
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
}

