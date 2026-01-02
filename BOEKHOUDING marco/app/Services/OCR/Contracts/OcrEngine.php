<?php

namespace App\Services\OCR\Contracts;

interface OcrEngine
{
    /**
     * Process a document and extract text and structured data
     * 
     * @param string $filePath Path to the document file
     * @return array Normalized OCR data
     */
    public function process(string $filePath): array;
    
    /**
     * Check if the OCR engine is available and configured
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}

