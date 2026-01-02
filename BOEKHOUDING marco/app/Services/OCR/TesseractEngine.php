<?php

namespace App\Services\OCR;

use App\Services\OCR\Contracts\OcrEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TesseractEngine implements OcrEngine
{
    protected array $confidenceScores = [];
    protected string $language = 'nld'; // Dutch language model
    
    /**
     * Process document using Tesseract OCR
     * Returns NORMALIZED structure as per spec
     */
    public function process(string $filePath): array
    {
        if (!$this->isAvailable()) {
            Log::warning('Tesseract OCR not available, using fallback parser');
            return $this->fallbackParser($filePath);
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
            Log::error('Tesseract OCR failed: ' . $e->getMessage());
            return $this->getEmptyStructure();
        }
    }
    
    /**
     * Process multi-page PDF
     */
    protected function processPdf(string $filePath): array
    {
        // For PDFs, extract all pages and combine results
        $allText = '';
        $allData = $this->getEmptyStructure();
        
        // Try to extract text from PDF using pdftotext if available
        if ($this->isPdfToolAvailable()) {
            $text = shell_exec("pdftotext -layout '{$filePath}' - 2>/dev/null");
            if ($text) {
                $allText = $text;
                $allData = $this->parseInvoiceData($allText);
            }
        }
        
        // If pdftotext not available, convert first page to image and process
        if (empty($allText)) {
            $imagePath = $this->convertPdfPageToImage($filePath, 1);
            if ($imagePath) {
                $allData = $this->processImage($imagePath);
                // Clean up temp image
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        
        // Extract structured data from combined text
        if (!empty($allText)) {
            $structured = $this->extractStructuredData($filePath, $allText);
            $allData = array_merge_recursive($allData, $structured);
        }
        
        return $allData;
    }
    
    /**
     * Process single image file
     */
    protected function processImage(string $filePath): array
    {
        // Preprocess image for better OCR accuracy
        $processedPath = $this->preprocessImage($filePath);
        
        try {
            // Run Tesseract OCR with Dutch language
            $outputFile = tempnam(sys_get_temp_dir(), 'tesseract_');
            $command = sprintf(
                "tesseract '%s' '%s' -l %s --psm 6 2>&1",
                escapeshellarg($processedPath),
                escapeshellarg($outputFile),
                escapeshellarg($this->language)
            );
            
            exec($command, $output, $returnCode);
            
            Log::info("Tesseract OCR command executed", [
                'command' => $command,
                'return_code' => $returnCode,
                'output_lines' => count($output),
                'output_file_exists' => file_exists($outputFile . '.txt'),
            ]);
            
            if ($returnCode === 0 && file_exists($outputFile . '.txt')) {
                $text = file_get_contents($outputFile . '.txt');
                unlink($outputFile . '.txt');
                
                Log::info("Tesseract OCR extracted text", [
                    'text_length' => strlen($text),
                    'preview' => substr($text, 0, 200),
                ]);
                
                // Get confidence scores
                $this->extractConfidenceScores($processedPath);
                
                // Parse the extracted text
                $data = $this->parseInvoiceData($text);
                
                // Extract structured data
                $structured = $this->extractStructuredData($filePath, $text);
                
                // Merge structured data (prefer structured over parsed)
                $data = array_merge($data, [
                    'supplier' => array_merge($data['supplier'] ?? [], $structured['supplier'] ?? []),
                    'invoice' => array_merge($data['invoice'] ?? [], $structured['invoice'] ?? []),
                    'amounts' => array_merge($data['amounts'] ?? [], $structured['amounts'] ?? []),
                    'raw_text' => $text, // Always use full text
                ]);
                
                // Clean up processed image if it's different from original
                if ($processedPath !== $filePath && file_exists($processedPath)) {
                    unlink($processedPath);
                }
                
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Tesseract image processing failed: ' . $e->getMessage());
        }
        
        // Fallback to basic parsing
        return $this->fallbackParser($filePath);
    }
    
    /**
     * Preprocess image for better OCR accuracy
     * - Deskew (rotate to correct angle)
     * - Denoise (remove noise)
     * - Contrast enhancement
     */
    protected function preprocessImage(string $filePath): string
    {
        // Check if ImageMagick or GD is available
        if (!extension_loaded('imagick') && !extension_loaded('gd')) {
            Log::warning('Image processing library not available, skipping preprocessing');
            return $filePath;
        }
        
        try {
            $outputPath = tempnam(sys_get_temp_dir(), 'preprocessed_') . '.png';
            
            if (extension_loaded('imagick')) {
                $image = new \Imagick($filePath);
                
                // Convert to grayscale for better OCR
                $image->transformImageColorspace(\Imagick::COLORSPACE_GRAY);
                
                // Enhance contrast
                $image->normalizeImage();
                $image->contrastImage(1);
                
                // Denoise
                $image->reduceNoiseImage(2);
                
                // Auto-deskew (detect and correct rotation)
                $image->deskewImage(0.4);
                
                // Sharpen slightly
                $image->sharpenImage(0, 1);
                
                // Save processed image
                $image->writeImage($outputPath);
                $image->clear();
                $image->destroy();
                
                return $outputPath;
            } elseif (extension_loaded('gd')) {
                // Basic GD processing
                $imageInfo = getimagesize($filePath);
                $image = null;
                
                switch ($imageInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($filePath);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($filePath);
                        break;
                }
                
                if ($image) {
                    // Convert to grayscale
                    imagefilter($image, IMG_FILTER_GRAYSCALE);
                    // Enhance contrast
                    imagefilter($image, IMG_FILTER_CONTRAST, -20);
                    // Save
                    imagepng($image, $outputPath);
                    imagedestroy($image);
                    
                    return $outputPath;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Image preprocessing failed: ' . $e->getMessage());
        }
        
        return $filePath;
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
        // Prioritize dates near "Factuurdatum", "Datum", "Date" keywords
        $datePatterns = [
            // Pattern 1: "Factuurdatum: DD-MM-YYYY" or "Factuurdatum: DD/MM/YYYY" (highest priority)
            '/factuurdatum[:\s]+(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/i',
            // Pattern 2: "Datum: DD-MM-YYYY"
            '/datum[:\s]+(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/i',
            // Pattern 3: General DD-MM-YYYY format
            '/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\b/',
            // Pattern 4: YYYY-MM-DD format
            '/\b(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})\b/',
            // Pattern 5: Dutch month names
            '/\b(\d{1,2})\s+(januari|februari|maart|april|mei|juni|juli|augustus|september|oktober|november|december)\s+(\d{4})\b/i',
        ];
        
        $months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 
                   'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
        
        foreach ($datePatterns as $patternIndex => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $day = null;
                    $month = null;
                    $year = null;
                    
                    if (isset($matches[3]) && in_array(strtolower($matches[2] ?? ''), $months)) {
                        // Dutch month name format
                    $monthNum = array_search(strtolower($matches[2]), $months) + 1;
                        $day = (int)$matches[1];
                        $month = $monthNum;
                        $year = (int)$matches[3];
                } elseif (isset($matches[3])) {
                        // Check if it's DD-MM-YYYY or YYYY-MM-DD format
                        if (strlen($matches[1]) === 4) {
                            // YYYY-MM-DD
                            $year = (int)$matches[1];
                            $month = (int)$matches[2];
                            $day = (int)$matches[3];
                        } else {
                            // DD-MM-YYYY
                            $day = (int)$matches[1];
                            $month = (int)$matches[2];
                            $year = (int)$matches[3];
                        }
                    }
                    
                    // Fix common OCR errors (40 -> 10, 41 -> 11, etc.)
                    if ($day > 31) {
                        if ($day == 40) $day = 10;
                        elseif ($day == 41) $day = 11;
                        elseif ($day == 42) $day = 12;
                        elseif ($day == 43) $day = 13;
                        elseif ($day == 44) $day = 14;
                        elseif ($day == 45) $day = 15;
                        elseif ($day == 46) $day = 16;
                        elseif ($day == 47) $day = 17;
                        elseif ($day == 48) $day = 18;
                        elseif ($day == 49) $day = 19;
                        elseif ($day == 50) $day = 20;
                        elseif ($day >= 51 && $day <= 61) $day = $day - 40;
                    }
                    
                    // Validate and create date
                    if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12 && $year >= 2000 && $year <= 2100) {
                        $data['invoice']['date'] = sprintf('%s-%02d-%02d', $year, $month, $day);
                        Log::info('Tesseract: Extracted date', [
                            'date' => $data['invoice']['date'],
                            'original_match' => $matches[0],
                            'pattern_index' => $patternIndex,
                        ]);
                        break;
                    }
                } catch (\Exception $e) {
                    Log::warning('Tesseract: Date parsing failed', ['matches' => $matches, 'error' => $e->getMessage()]);
                    continue;
                }
            }
        }
        
        // Enhanced amount extraction - prioritize "Totaal" as it's the final amount
        // Handle both Dutch format (4.104,93) and simple format (4104.93 or 4104,93)
        $amountPatterns = [
            // Pattern 1: "Totaal: € 4.104,93" or "Totaal: €4104.93" (highest priority)
            '/totaal[:\s]+€?\s*(\d{1,4}(?:\.\d{3})*(?:[.,]\d{2})?|\d{1,4}(?:,\d{2})?)/i',
            // Pattern 2: "Totaal inclusief: € 4.104,93"
            '/totaal\s+inclusief[:\s]+€?\s*(\d{1,4}(?:\.\d{3})*(?:[.,]\d{2})?|\d{1,4}(?:,\d{2})?)/i',
            // Pattern 3: "Subtotaal: € 3.392,50"
            '/subtotaal[:\s]+€?\s*(\d{1,4}(?:\.\d{3})*(?:[.,]\d{2})?|\d{1,4}(?:,\d{2})?)/i',
            // Pattern 4: "BTW (21%): € 712,43" or "BTW: € 712,43"
            '/BTW\s*(?:\(?\d+%?\)?)?[:\s]+€?\s*(\d{1,4}(?:\.\d{3})*(?:[.,]\d{2})?|\d{1,4}(?:,\d{2})?)/i',
            // Pattern 5: General € amount patterns (lower priority)
            '/€\s*(\d{1,4}(?:\.\d{3})*(?:[.,]\d{2})?|\d{1,4}(?:,\d{2})?)/',
        ];
        
        $totalAmount = null;
        $subtotalAmount = null;
        $vatAmount = null;
        
        foreach ($amountPatterns as $patternIndex => $pattern) {
            if (preg_match_all($pattern, $text, $allMatches, PREG_SET_ORDER)) {
                foreach ($allMatches as $matches) {
                    $amountStr = $matches[1];
                    // Handle Dutch format: 4.104,93 -> 4104.93
                    // Handle simple format: 4104.93 or 4104,93 -> 4104.93
                    if (strpos($amountStr, '.') !== false && strpos($amountStr, ',') !== false) {
                        // Dutch format: 4.104,93
                        $amountStr = str_replace(['.', ','], ['', '.'], $amountStr);
                    } elseif (strpos($amountStr, ',') !== false) {
                        // European format: 4104,93
                        $amountStr = str_replace(',', '.', $amountStr);
                    }
                    // If only dots, assume it's decimal separator (4104.93)
                    $amount = (float)$amountStr;
                
                    if ($amount > 0 && $amount < 1000000) {
                        // Only accept reasonable amounts (not false positives like 71243)
                        if ($amount > 100000) {
                            Log::warning('Tesseract: Skipping unreasonably large amount', ['amount' => $amount, 'pattern_index' => $patternIndex]);
                            continue;
                        }
                        
                        if ($patternIndex === 0 || $patternIndex === 1) {
                            // "Totaal" - this is the final amount
                            if ($totalAmount === null || $amount > $totalAmount) {
                                $totalAmount = $amount;
                            }
                        } elseif ($patternIndex === 2) {
                            // "Subtotaal"
                            if ($subtotalAmount === null || $amount > $subtotalAmount) {
                                $subtotalAmount = $amount;
                            }
                        } elseif ($patternIndex === 3) {
                            // "BTW" - should be smaller than total
                            if ($vatAmount === null || ($amount < 10000 && $amount > $vatAmount)) {
                                $vatAmount = $amount;
                            }
                        }
                    }
                }
            }
        }
        
        // Use Totaal if found, otherwise use largest reasonable amount
        if ($totalAmount !== null) {
            $data['amounts']['incl'] = $totalAmount;
            
            // Try to find BTW amount and rate
            if ($vatAmount !== null && $subtotalAmount !== null) {
                // We have both subtotal and VAT, calculate rate
                $calculatedRate = ($vatAmount / $subtotalAmount) * 100;
                if (abs($calculatedRate - 21) < 1) {
                    $data['amounts']['vat_rate'] = '21';
                    $data['amounts']['excl'] = $subtotalAmount;
                    $data['amounts']['vat'] = $vatAmount;
                } elseif (abs($calculatedRate - 9) < 1) {
                    $data['amounts']['vat_rate'] = '9';
                    $data['amounts']['excl'] = $subtotalAmount;
                    $data['amounts']['vat'] = $vatAmount;
                } else {
                    $data['amounts']['excl'] = $subtotalAmount;
                    $data['amounts']['vat'] = $vatAmount;
                    $data['amounts']['vat_rate'] = '21'; // Default
                }
            } else {
                // Look for VAT rate in text
                $vatRate = null;
                if (preg_match('/BTW\s*(?:\(?21|hoog|hoge)[\s%\)]*/i', $text)) {
                    $vatRate = '21';
                } elseif (preg_match('/BTW\s*(?:\(?9|laag|lage)[\s%\)]*/i', $text)) {
                    $vatRate = '9';
                } elseif (preg_match('/BTW\s*(?:\(?0|nul|geen)[\s%\)]*/i', $text)) {
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
            }
        }
        
        Log::info('Tesseract: Extracted amounts', [
            'incl' => $data['amounts']['incl'] ?? null,
            'excl' => $data['amounts']['excl'] ?? null,
            'vat' => $data['amounts']['vat'] ?? null,
            'vat_rate' => $data['amounts']['vat_rate'] ?? null,
            'found_total' => $totalAmount,
            'found_subtotal' => $subtotalAmount,
            'found_vat' => $vatAmount,
        ]);
        
        // Enhanced supplier name extraction
        // The supplier is usually the company issuing the invoice (at the top)
        // Look for company name patterns, especially with B.V., BV, etc.
        $supplierPatterns = [
            // Pattern 1: Company name with B.V./BV/NV suffix (highest priority)
            // Match: "YOHANNES HOVENIERSBEDRIJF B.V." or "YOHANNES HOVENIERSBEDRIJF BV"
            '/^([A-Z][A-Za-z0-9\s&\.\-]{3,60}(?:\s+(?:B\.V\.|BV|N\.V\.|NV|V\.O\.F\.|VOF))?)(?:\s+FACTUUR|\n|$|BTW|KVK)/m',
            // Pattern 2: Company name on its own line at the beginning (all caps)
            '/^([A-Z][A-Z][A-Za-z0-9\s&\.\-]{5,60})(?:\n|$)/m',
            // Pattern 3: Company name before address line
            '/^([A-Z][A-Za-z0-9\s&\.\-]{5,60})\s+\d+[A-Z]?\s+[A-Z]/m',
        ];
        
        $falsePositives = [
            'FACTUUR', 'INVOICE', 'BONNETJE', 'BON', 'Datum', 'Datum:', 
            'Totaal', 'TOTAAL', 'Bedrag', 'BEDRAG', 'FACTUURGEGEVENS', 
            'KLANTGEGEVENS', 'FACTUURITEMS', 'NOTITIES', 'BETALINGSINFORMATIE',
            'Aristotelesstraat', 'Apeldoorn', 'Nieuwegein', 'Vuilcop'
        ];
        
        // Get first few lines (supplier usually appears early)
        $lines = explode("\n", $text);
        $firstLines = array_slice($lines, 0, 10);
        $earlyText = implode("\n", $firstLines);
        
        foreach ($supplierPatterns as $patternIndex => $pattern) {
            if (preg_match_all($pattern, $earlyText, $allMatches, PREG_SET_ORDER)) {
                foreach ($allMatches as $matches) {
                    $supplierName = trim($matches[1]);
                    
                    // Filter out false positives
                    $isFalsePositive = false;
                    foreach ($falsePositives as $fp) {
                        if (stripos($supplierName, $fp) !== false) {
                            $isFalsePositive = true;
                            break;
                        }
                    }
                    
                    if ($isFalsePositive) continue;
                    if (!preg_match('/[A-Za-z]/', $supplierName)) continue; // Must have letters
                    if (preg_match('/^[\d\s\-\.]+$/', $supplierName)) continue; // Not just numbers
                    if (preg_match('/^NL\d{9}B\d{2}$/', str_replace(' ', '', $supplierName))) continue; // Not VAT number
                    if (preg_match('/^\d+[A-Z]?\s/', $supplierName)) continue; // Not address
                    if (strlen($supplierName) < 3 || strlen($supplierName) > 100) continue;
                    
                    // Clean up - remove common suffixes that are part of headers
                    $supplierName = preg_replace('/\s+FACTUUR.*$/i', '', $supplierName);
                    $supplierName = preg_replace('/\s+FACTUURGEGEVENS.*$/i', '', $supplierName);
                    $supplierName = trim($supplierName);
                    
                    // If name contains newlines, take the line with B.V./BV (company name)
                    if (strpos($supplierName, "\n") !== false) {
                        $lines = explode("\n", $supplierName);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (preg_match('/(?:B\.V\.|BV|N\.V\.|NV)/', $line)) {
                                $supplierName = $line;
                                break;
                            }
                        }
                        // If no B.V. found, take the longest line
                        if (strpos($supplierName, "\n") !== false) {
                            $longestLine = '';
                            foreach ($lines as $line) {
                                if (strlen(trim($line)) > strlen($longestLine)) {
                                    $longestLine = trim($line);
                                }
                            }
                            $supplierName = $longestLine;
                        }
                    }
                    
                    $supplierName = trim($supplierName);
                    
                    if (strlen($supplierName) >= 3) {
                        $data['supplier']['name'] = $supplierName;
                        Log::info('Tesseract: Extracted supplier name', [
                            'name' => $supplierName,
                            'pattern_index' => $patternIndex,
                        ]);
                        break 2; // Break out of both loops
                    }
                }
            }
        }
        
        // Enhanced BTW number extraction
        if (preg_match('/BTW[-\s]?(?:nummer|nr|number)?[\s:]*([A-Z]{2}\s?\d{2}\s?\d{3}\s?\d{3}\s?[B]\s?\d{2})/i', $text, $matches)) {
            $data['supplier']['vat_number'] = preg_replace('/\s+/', '', strtoupper($matches[1]));
        }
        
        // Enhanced IBAN extraction
        if (preg_match('/IBAN[\s:]*([A-Z]{2}\d{2}[A-Z0-9]{4,30})/i', $text, $matches)) {
            $data['supplier']['iban'] = strtoupper($matches[1]);
        }
        
        $data['raw_text'] = $text;
        
        return $data;
    }
    
    /**
     * Extract confidence scores from Tesseract output
     */
    protected function extractConfidenceScores(string $filePath): void
    {
        $this->confidenceScores = [];
        
        if (!$this->isAvailable()) {
            return;
        }
        
        try {
            // Get confidence scores using Tesseract's hOCR output
            $outputFile = tempnam(sys_get_temp_dir(), 'tesseract_conf_');
            $command = sprintf(
                "tesseract '%s' '%s' -l %s --psm 6 hocr 2>/dev/null",
                escapeshellarg($filePath),
                escapeshellarg($outputFile),
                escapeshellarg($this->language)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($outputFile . '.hocr')) {
                $hocr = file_get_contents($outputFile . '.hocr');
                unlink($outputFile . '.hocr');
                
                // Parse confidence scores from hOCR
                if (preg_match_all('/title="bbox\s+\d+\s+\d+\s+\d+\s+\d+; x_wconf\s+(\d+)"/', $hocr, $matches)) {
                    $scores = array_map('intval', $matches[1]);
                    $this->confidenceScores = [
                        'average' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0,
                        'min' => count($scores) > 0 ? min($scores) : 0,
                        'max' => count($scores) > 0 ? max($scores) : 0,
                        'count' => count($scores),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract confidence scores: ' . $e->getMessage());
        }
    }
    
    /**
     * Get confidence scores for the last processed document
     */
    public function getConfidenceScores(): array
    {
        return $this->confidenceScores;
    }
    
    /**
     * Convert PDF page to image
     */
    protected function convertPdfPageToImage(string $pdfPath, int $page = 1): ?string
    {
        if (!extension_loaded('imagick')) {
            return null;
        }
        
        try {
            $image = new \Imagick();
            $image->setResolution(300, 300); // High resolution for better OCR
            $image->readImage($pdfPath . '[' . ($page - 1) . ']');
            $image->setImageFormat('png');
            
            $outputPath = tempnam(sys_get_temp_dir(), 'pdf_page_') . '.png';
            $image->writeImage($outputPath);
            $image->clear();
            $image->destroy();
            
            return $outputPath;
        } catch (\Exception $e) {
            Log::warning('PDF to image conversion failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if PDF tools are available
     */
    protected function isPdfToolAvailable(): bool
    {
        $output = [];
        exec('which pdftotext 2>/dev/null', $output);
        return !empty($output);
    }
    
    /**
     * Check if Tesseract is available
     */
    public function isAvailable(): bool
    {
        // Check if tesseract command exists
        $output = [];
        exec('which tesseract 2>/dev/null', $output);
        return !empty($output);
    }
    
    /**
     * Fallback parser for basic text extraction
     * Returns normalized OCR structure
     */
    protected function fallbackParser(string $filePath): array
    {
        // Basic filename parsing for demo purposes
        $filename = basename($filePath);
        
        return $this->getEmptyStructure();
    }
    
    /**
     * Get the standardized empty OCR structure
     * This ensures all OCR engines return the same format
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
     * Parse common invoice patterns from text
     */
    protected function parseInvoiceData(string $text): array
    {
        $data = $this->getEmptyStructure();
        $data['raw_text'] = $text;
        
        // Extract dates (DD-MM-YYYY or DD/MM/YYYY)
        if (preg_match('/\b(\d{2})[-\/](\d{2})[-\/](\d{4})\b/', $text, $matches)) {
            $data['invoice']['date'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }
        
        // Extract amounts (common Dutch formats)
        // Pattern: € 121,00 or EUR 121,00
        if (preg_match('/€?\s*(\d+)[,.](\d{2})/', $text, $matches)) {
            $amount = (float)($matches[1] . '.' . $matches[2]);
            $data['amounts']['incl'] = $amount;
            
            // Try to detect BTW mentions
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
        
        // Extract BTW number (NL format: NL123456789B01)
        if (preg_match('/NL\s*\d{9}B\d{2}/', $text, $matches)) {
            $data['supplier']['vat_number'] = str_replace(' ', '', $matches[0]);
        }
        
        // Extract IBAN (NL format)
        if (preg_match('/NL\d{2}[A-Z]{4}\d{10}/', $text, $matches)) {
            $data['supplier']['iban'] = $matches[0];
        }
        
        return $data;
    }
}

