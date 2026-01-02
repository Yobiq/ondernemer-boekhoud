<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\OCR\OcrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestOcr extends Command
{
    protected $signature = 'ocr:test {document_id?}';
    protected $description = 'Test OCR extraction for a document';

    public function handle()
    {
        $documentId = $this->argument('document_id');
        
        if ($documentId) {
            $document = Document::find($documentId);
        } else {
            $document = Document::whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                ->whereNull('amount_incl')
                ->first();
        }
        
        if (!$document) {
            $this->error('No document found to test');
            return 1;
        }
        
        $this->info("Testing OCR for document #{$document->id}: {$document->original_filename}");
        $this->info("File path: {$document->file_path}");
        $this->info("Status: {$document->status}");
        
        // Check file exists
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($document->file_path);
        if (!file_exists($fullPath)) {
            $altPath = storage_path('app/private/' . $document->file_path);
            if (file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                $this->error("File not found at: {$fullPath}");
                $this->error("Also tried: {$altPath}");
                return 1;
            }
        }
        
        $this->info("File found at: {$fullPath}");
        $this->info("File size: " . filesize($fullPath) . " bytes");
        
        // Test OCR - Force Tesseract first
        $this->info("\nStarting OCR extraction with Tesseract (primary)...");
        $ocrService = new \App\Services\OCR\OcrService();
        
        // Force Tesseract as primary engine
        $tesseractEngine = new \App\Services\OCR\TesseractEngine();
        if ($tesseractEngine->isAvailable()) {
            $ocrService->setEngine($tesseractEngine);
            $this->info("✅ Using Tesseract as primary engine");
        } else {
            $this->warn("⚠️ Tesseract not available, will use fallback");
        }
        
        $ocrData = $ocrService->processDocument($fullPath);
        
        $this->info("\n=== OCR Results ===");
        $this->info("Raw text length: " . strlen($ocrData['raw_text'] ?? ''));
        $this->info("Supplier: " . ($ocrData['supplier']['name'] ?? 'NOT FOUND'));
        $this->info("Date: " . ($ocrData['invoice']['date'] ?? 'NOT FOUND'));
        $this->info("Amount (incl): " . ($ocrData['amounts']['incl'] ?? 'NOT FOUND'));
        $this->info("Amount (excl): " . ($ocrData['amounts']['excl'] ?? 'NOT FOUND'));
        $this->info("VAT: " . ($ocrData['amounts']['vat'] ?? 'NOT FOUND'));
        $this->info("VAT Rate: " . ($ocrData['amounts']['vat_rate'] ?? 'NOT FOUND'));
        
        if (!empty($ocrData['raw_text'])) {
            $this->info("\n=== Raw Text (first 500 chars) ===");
            $this->line(substr($ocrData['raw_text'], 0, 500));
        }
        
        // Update document
        $this->info("\nUpdating document...");
        $document->ocr_data = $ocrData;
        $document->amount_excl = $ocrData['amounts']['excl'];
        $document->amount_vat = $ocrData['amounts']['vat'];
        $document->amount_incl = $ocrData['amounts']['incl'];
        $document->vat_rate = $ocrData['amounts']['vat_rate'];
        $document->document_date = $ocrData['invoice']['date'];
        $document->supplier_name = $ocrData['supplier']['name'];
        $document->supplier_vat = $ocrData['supplier']['vat_number'];
        $document->save();
        
        $this->info("✅ Document updated!");
        $this->info("Check the document at: /admin/document-review?document={$document->id}");
        
        return 0;
    }
}

