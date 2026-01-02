<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AwsTextractEngine implements OcrEngine
{
    protected ?\Aws\Textract\TextractClient $client = null;
    protected array $confidenceScores = [];
    
    public function __construct()
    {
        $this->initializeClient();
    }
    
    /**
     * Initialize AWS Textract client
     */
    protected function initializeClient(): void
    {
        if (!$this->isAvailable()) {
            return;
        }
        
        try {
            $this->client = new \Aws\Textract\TextractClient([
                'version' => 'latest',
                'region' => config('services.aws.region', 'eu-west-1'),
                'credentials' => [
                    'key' => config('services.aws.key'),
                    'secret' => config('services.aws.secret'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize AWS Textract client: ' . $e->getMessage());
            $this->client = null;
        }
    }
    
    /**
     * Process document using AWS Textract
     */
    public function process(string $filePath): array
    {
        if (!$this->isAvailable() || !$this->client) {
            Log::warning('AWS Textract not available, returning empty structure');
            return $this->getEmptyStructure();
        }
        
        try {
            // Read file content
            $fileContent = file_get_contents($filePath);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // Determine document type
            $featureTypes = ['TABLES', 'FORMS'];
            
            if ($extension === 'pdf') {
                // For PDFs, use async analysis
                $result = $this->analyzeDocumentAsync($fileContent, $featureTypes);
            } else {
                // For images, use sync detection
                $result = $this->analyzeDocument($fileContent, $featureTypes);
            }
            
            // Extract structured data from Textract response
            return $this->extractDataFromTextract($result);
            
        } catch (\Exception $e) {
            Log::error('AWS Textract processing failed: ' . $e->getMessage());
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Analyze document synchronously (for images)
     */
    protected function analyzeDocument(string $fileContent, array $featureTypes): array
    {
        $result = $this->client->analyzeDocument([
            'Document' => [
                'Bytes' => $fileContent,
            ],
            'FeatureTypes' => $featureTypes,
        ]);
        
        return $result->toArray();
    }
    
    /**
     * Analyze document asynchronously (for PDFs)
     */
    protected function analyzeDocumentAsync(string $fileContent, array $featureTypes): array
    {
        // Upload to S3 first (required for async)
        $s3Key = 'textract/' . uniqid() . '.pdf';
        Storage::disk('s3')->put($s3Key, $fileContent);
        
        try {
            // Start async job
            $jobId = $this->client->startDocumentAnalysis([
                'DocumentLocation' => [
                    'S3Object' => [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Name' => $s3Key,
                    ],
                ],
                'FeatureTypes' => $featureTypes,
            ])['JobId'];
            
            // Poll for completion
            $result = $this->waitForJobCompletion($jobId);
            
            return $result;
        } finally {
            // Clean up S3 file
            Storage::disk('s3')->delete($s3Key);
        }
    }
    
    /**
     * Wait for async job to complete
     */
    protected function waitForJobCompletion(string $jobId, int $maxWait = 300): array
    {
        $startTime = time();
        
        while (true) {
            $status = $this->client->getDocumentAnalysis([
                'JobId' => $jobId,
            ]);
            
            $jobStatus = $status['JobStatus'];
            
            if ($jobStatus === 'SUCCEEDED') {
                return $status;
            }
            
            if ($jobStatus === 'FAILED') {
                throw new \Exception('Textract job failed: ' . ($status['StatusMessage'] ?? 'Unknown error'));
            }
            
            if (time() - $startTime > $maxWait) {
                throw new \Exception('Textract job timeout');
            }
            
            sleep(2); // Wait 2 seconds before checking again
        }
    }
    
    /**
     * Extract structured data from Textract response
     */
    protected function extractDataFromTextract(array $result): array
    {
        $data = $this->getEmptyStructure();
        
        // Extract blocks
        $blocks = $result['Blocks'] ?? [];
        $text = '';
        $keyValuePairs = [];
        $tables = [];
        
        // Build text and extract key-value pairs
        foreach ($blocks as $block) {
            if ($block['BlockType'] === 'LINE') {
                $text .= ($block['Text'] ?? '') . "\n";
            }
            
            if ($block['BlockType'] === 'KEY_VALUE_SET') {
                if (isset($block['EntityTypes']) && in_array('KEY', $block['EntityTypes'])) {
                    $key = $this->getBlockText($block, $blocks);
                    $valueBlock = $this->findValueBlock($block, $blocks);
                    if ($valueBlock) {
                        $value = $this->getBlockText($valueBlock, $blocks);
                        $keyValuePairs[strtolower($key)] = $value;
                    }
                }
            }
        }
        
        // Map key-value pairs to our structure
        foreach ($keyValuePairs as $key => $value) {
            $keyLower = strtolower($key);
            
            if (str_contains($keyLower, 'factuur') || str_contains($keyLower, 'invoice')) {
                $data['invoice']['number'] = $value;
            } elseif (str_contains($keyLower, 'datum') || str_contains($keyLower, 'date')) {
                $data['invoice']['date'] = $this->parseDate($value);
            } elseif (str_contains($keyLower, 'totaal') || str_contains($keyLower, 'total') || str_contains($keyLower, 'bedrag')) {
                $data['amounts']['incl'] = $this->parseAmount($value);
            } elseif (str_contains($keyLower, 'btw') || str_contains($keyLower, 'vat')) {
                if (preg_match('/(\d+(?:[.,]\d+)?)/', $value, $matches)) {
                    $data['amounts']['vat'] = $this->parseAmount($matches[1]);
                }
            } elseif (str_contains($keyLower, 'leverancier') || str_contains($keyLower, 'supplier')) {
                $data['supplier']['name'] = $value;
            }
        }
        
        // Extract from raw text if key-value extraction didn't work
        if (empty($data['invoice']['number'])) {
            $data = array_merge($data, $this->parseInvoiceData($text));
        }
        
        // Calculate confidence scores
        $this->calculateConfidenceScores($blocks);
        
        $data['raw_text'] = $text;
        
        return $data;
    }
    
    /**
     * Get text from a block
     */
    protected function getBlockText(array $block, array $allBlocks): string
    {
        $text = '';
        
        if (isset($block['Relationships'])) {
            foreach ($block['Relationships'] as $relationship) {
                if ($relationship['Type'] === 'CHILD') {
                    foreach ($relationship['Ids'] as $id) {
                        $childBlock = $this->findBlockById($id, $allBlocks);
                        if ($childBlock && $childBlock['BlockType'] === 'WORD') {
                            $text .= ($childBlock['Text'] ?? '') . ' ';
                        }
                    }
                }
            }
        }
        
        return trim($text);
    }
    
    /**
     * Find block by ID
     */
    protected function findBlockById(string $id, array $blocks): ?array
    {
        foreach ($blocks as $block) {
            if ($block['Id'] === $id) {
                return $block;
            }
        }
        return null;
    }
    
    /**
     * Find value block for a key block
     */
    protected function findValueBlock(array $keyBlock, array $allBlocks): ?array
    {
        if (!isset($keyBlock['Relationships'])) {
            return null;
        }
        
        foreach ($keyBlock['Relationships'] as $relationship) {
            if ($relationship['Type'] === 'VALUE') {
                foreach ($relationship['Ids'] as $id) {
                    $valueBlock = $this->findBlockById($id, $allBlocks);
                    if ($valueBlock) {
                        return $valueBlock;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Calculate confidence scores from blocks
     */
    protected function calculateConfidenceScores(array $blocks): void
    {
        $scores = [];
        
        foreach ($blocks as $block) {
            if (isset($block['Confidence'])) {
                $scores[] = (float)$block['Confidence'];
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
        // Try various date formats
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
        // Remove currency symbols and spaces
        $amountStr = preg_replace('/[€$£\s]/', '', $amountStr);
        
        // Handle Dutch format (1.234,56)
        if (preg_match('/(\d{1,3}(?:\.\d{3})*),(\d{2})/', $amountStr, $matches)) {
            return (float)(str_replace('.', '', $matches[1]) . '.' . $matches[2]);
        }
        
        // Handle standard format (1234.56)
        if (preg_match('/(\d+(?:\.\d{2})?)/', $amountStr, $matches)) {
            return (float)$matches[1];
        }
        
        return null;
    }
    
    /**
     * Check if AWS Textract is available
     */
    public function isAvailable(): bool
    {
        return !empty(config('services.aws.key')) 
            && !empty(config('services.aws.secret'))
            && class_exists('\Aws\Textract\TextractClient');
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
     * Parse invoice data from text (fallback)
     */
    protected function parseInvoiceData(string $text): array
    {
        $data = $this->getEmptyStructure();
        $data['raw_text'] = $text;
        
        // Basic parsing similar to TesseractEngine
        if (preg_match('/\b(\d{2})[-\/](\d{2})[-\/](\d{4})\b/', $text, $matches)) {
            $data['invoice']['date'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        if (preg_match('/€?\s*(\d+)[,.](\d{2})/', $text, $matches)) {
            $amount = (float)($matches[1] . '.' . $matches[2]);
            $data['amounts']['incl'] = $amount;
        }
        
        return $data;
    }
}

