<?php

namespace App\Services;

use App\Models\Document;

class VatCalculatorService
{
    /**
     * BTW code to rubriek mapping
     */
    private const RUBRIEK_MAPPING = [
        'NL21' => '1a', // Hoog tarief (21%)
        'NL9' => '1b',  // Laag tarief (9%)
        'NL0' => '1c',  // Vrijgesteld (0%)
        'VERL' => '2a', // Verleggingsregeling binnenland
        'VERL2' => '2b', // Verleggingsregeling buitenland
        'EU' => '3a',   // Intracommunautaire levering
        'EU2' => '3b',  // Diensten naar/in buitenland
        'IMPORT' => '4a', // Import binnen EU
        'IMPORT2' => '4b', // Import buiten EU
        'VOORBELASTING' => '5b', // Voorbelasting
    ];

    /**
     * Calculate which BTW rubriek a document belongs to based on document type and BTW code
     * 
     * ENHANCED: Now handles EU transactions and imports
     * 
     * Rules:
     * - Sales invoices (verkoopfacturen): BTW verschuldigd -> rubriek 1a, 1b, 1c, 3a, 3b
     * - Purchase invoices (inkoopfacturen): BTW aftrekbaar -> rubriek 2a, 4a, 4b, 5b
     * - Receipts (bonnetjes): BTW aftrekbaar -> rubriek 2a, 5b
     * - Bank statements: Geen BTW -> geen rubriek
     */
    public function calculateRubriek(Document $document): string
    {
        $documentType = $document->document_type;
        $vatCode = $document->vat_code ?? $this->determineVatCode($document);
        $vatRate = (float) ($document->vat_rate ?? 0);
        
        // ENHANCEMENT: Check for EU supplier first (VAT number starts with country code)
        if ($this->isEUSupplier($document)) {
            return $this->calculateEURubriek($document);
        }
        
        // ENHANCEMENT: Check for import
        if ($this->isImport($document)) {
            return $this->calculateImportRubriek($document);
        }
        
        // Sales invoices (verkoopfacturen) - BTW verschuldigd
        if ($documentType === 'sales_invoice') {
            if ($vatRate == 21) {
                return '1a'; // Hoog tarief (21%)
            } elseif ($vatRate == 9) {
                return '1b'; // Laag tarief (9%)
            } elseif ($vatRate == 0) {
                return '1c'; // Vrijgesteld (0%)
            }
            // Default voor sales invoices
            return '1a';
        }
        
        // Purchase invoices (inkoopfacturen) - BTW aftrekbaar
        if ($documentType === 'purchase_invoice') {
            if ($vatRate == 21 || $vatRate == 9) {
                return '2a'; // Inkoop binnenland
            } elseif ($vatRate == 0) {
                return '5b'; // Aftrek (0% of verlegd)
            }
            // Default voor purchase invoices
            return '2a';
        }
        
        // Receipts (bonnetjes) - BTW aftrekbaar
        if ($documentType === 'receipt') {
            if ($vatRate == 21 || $vatRate == 9) {
                return '2a'; // Inkoop binnenland
            } elseif ($vatRate == 0) {
                return '5b'; // Aftrek
            }
            // Default voor receipts
            return '2a';
        }
        
        // Bank statements - geen BTW
        if ($documentType === 'bank_statement') {
            return '5b'; // Geen BTW, maar wel in aftrek overzicht
        }
        
        // Fallback: gebruik oude logica met VAT code
        if ($vatCode) {
            return self::RUBRIEK_MAPPING[$vatCode] ?? $this->getDefaultRubriekForDocumentType($documentType);
        }
        
        // IMPROVED FALLBACK: Based on document type instead of always '1a'
        return $this->getDefaultRubriekForDocumentType($documentType);
    }
    
    /**
     * Check if document is from EU supplier
     * 
     * @param Document $document
     * @return bool
     */
    protected function isEUSupplier(Document $document): bool
    {
        $supplierVat = $document->supplier_vat ?? null;
        if (!$supplierVat) {
            // Check OCR data
            $ocrData = $document->ocr_data ?? [];
            $supplierVat = $ocrData['supplier']['vat_number'] ?? null;
        }
        
        if (!$supplierVat) {
            return false;
        }
        
        // EU VAT numbers start with 2-letter country code (e.g., BE, DE, FR)
        $euCountryCodes = ['BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 
                          'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 
                          'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];
        
        $vatUpper = strtoupper(trim($supplierVat));
        foreach ($euCountryCodes as $code) {
            if (str_starts_with($vatUpper, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if document is an import
     * 
     * @param Document $document
     * @return bool
     */
    protected function isImport(Document $document): bool
    {
        // Check OCR data for import indicators
        $ocrData = $document->ocr_data ?? [];
        $rawText = strtolower($ocrData['raw_text'] ?? '');
        
        // Look for import-related keywords
        $importKeywords = ['import', 'douane', 'customs', 'invoer', 'buiten eu', 'buitenland'];
        foreach ($importKeywords as $keyword) {
            if (str_contains($rawText, $keyword)) {
                return true;
            }
        }
        
        // Check if supplier VAT indicates non-EU (starts with country code but not EU)
        $supplierVat = $document->supplier_vat ?? $ocrData['supplier']['vat_number'] ?? null;
        if ($supplierVat && !$this->isEUSupplier($document)) {
            // Non-EU VAT number might indicate import
            // This is a heuristic - could be improved with more data
            return false; // Conservative: don't assume import without clear indicators
        }
        
        return false;
    }
    
    /**
     * Calculate rubriek for EU transactions
     * 
     * @param Document $document
     * @return string
     */
    protected function calculateEURubriek(Document $document): string
    {
        $documentType = $document->document_type;
        
        // Sales to EU: rubriek 3a (intracommunautaire levering)
        if ($documentType === 'sales_invoice') {
            return '3a';
        }
        
        // Purchase from EU: rubriek 4a (voorbelasting binnen EU)
        if ($documentType === 'purchase_invoice' || $documentType === 'receipt') {
            return '4a';
        }
        
        // Default for EU transactions
        return '3a';
    }
    
    /**
     * Calculate rubriek for import transactions
     * 
     * @param Document $document
     * @return string
     */
    protected function calculateImportRubriek(Document $document): string
    {
        $documentType = $document->document_type;
        
        // Import from outside EU: rubriek 4b (voorbelasting buiten EU)
        if ($documentType === 'purchase_invoice' || $documentType === 'receipt') {
            return '4b';
        }
        
        // Sales to outside EU: rubriek 3b (diensten naar buitenland)
        if ($documentType === 'sales_invoice') {
            return '3b';
        }
        
        // Default for imports
        return '4b';
    }
    
    /**
     * Get default rubriek based on document type
     * 
     * @param string|null $documentType
     * @return string
     */
    protected function getDefaultRubriekForDocumentType(?string $documentType): string
    {
        return match($documentType) {
            'sales_invoice' => '1a',
            'purchase_invoice', 'receipt' => '2a',
            'bank_statement' => '5b',
            default => '5b',
        };
    }

    /**
     * Determine BTW code from document data
     */
    private function determineVatCode(Document $document): ?string
    {
        $vatRate = (float) ($document->vat_rate ?? 0);
        
        // Determine code based on VAT rate
        if ($vatRate == 21) {
            return 'NL21';
        } elseif ($vatRate == 9) {
            return 'NL9';
        } elseif ($vatRate == 0) {
            return 'NL0';
        }

        // Check OCR data for hints
        $ocrData = $document->ocr_data ?? [];
        if (isset($ocrData['vat_code'])) {
            return $ocrData['vat_code'];
        }

        return null;
    }

    /**
     * Validate VAT calculation
     * 
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateVatCalculation(Document $document): array
    {
        $errors = [];
        $tolerance = 0.01; // 1 cent tolerance

        $amountExcl = (float) ($document->amount_excl ?? 0);
        $amountVat = (float) ($document->amount_vat ?? 0);
        $amountIncl = (float) ($document->amount_incl ?? 0);
        $vatRate = (float) ($document->vat_rate ?? 0);

        // Validate: amount_incl = amount_excl + amount_vat
        $calculatedIncl = $amountExcl + $amountVat;
        if (abs($amountIncl - $calculatedIncl) > $tolerance) {
            $errors[] = "BTW berekening klopt niet: {$amountExcl} + {$amountVat} ≠ {$amountIncl}";
        }

        // Validate: amount_vat = amount_excl * (vat_rate / 100)
        if ($vatRate > 0) {
            $calculatedVat = $amountExcl * ($vatRate / 100);
            if (abs($amountVat - $calculatedVat) > $tolerance) {
                $errors[] = "BTW bedrag klopt niet: {$amountExcl} × {$vatRate}% ≠ {$amountVat}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Calculate period totals per rubriek
     * Uses all approved documents in the period, organized by document type
     * 
     * BTW Rubrieken:
     * - 1a, 1b, 1c: BTW verschuldigd (verkoopfacturen)
     * - 2a, 5b: BTW aftrekbaar (inkoopfacturen, bonnetjes)
     * - 3a, 3b: EU leveringen/diensten
     * - 4a, 4b: Import
     */
    public function calculatePeriodTotals($vatPeriod): array
    {
        $totals = [
            '1a' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Verkoop 21%'],
            '1b' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Verkoop 9%'],
            '1c' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Verkoop 0%'],
            '2a' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Inkoop BTW Aftrek'],
            '2b' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Verlegging Buitenland'],
            '3a' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'EU Leveringen'],
            '3b' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'EU Diensten'],
            '4a' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Import EU'],
            '4b' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Import Buiten EU'],
            '5b' => ['amount' => 0, 'vat' => 0, 'count' => 0, 'label' => 'Aftrek Overig'],
        ];

        // Get all approved documents for this client in this period
        $client = $vatPeriod->client;
        
        $documents = \App\Models\Document::where('client_id', $client->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($vatPeriod) {
                // Documents with date in period
                $query->whereBetween('document_date', [$vatPeriod->period_start, $vatPeriod->period_end])
                    // OR documents without date but created in period
                    ->orWhere(function ($q) use ($vatPeriod) {
                        $q->whereNull('document_date')
                          ->whereBetween('created_at', [$vatPeriod->period_start, $vatPeriod->period_end]);
                    });
            })
            ->where(function ($query) {
                // CRITICAL: Only include PAID sales invoices for VAT calculation
                // Unpaid sales invoices should NOT be included in VAT declaration (cash basis rule)
                $query->where(function ($q) {
                    // Include all non-sales-invoice documents (purchase invoices, receipts, etc.)
                    $q->where('document_type', '!=', 'sales_invoice');
                })->orWhere(function ($q) {
                    // OR include sales invoices that are PAID
                    $q->where('document_type', 'sales_invoice')
                      ->where('is_paid', true);
                });
            })
            ->get();

        // PERFORMANCE FIX: Load all pivot data once to avoid N+1 queries
        $pivotData = $vatPeriod->documents()
            ->get()
            ->mapWithKeys(fn($doc) => [$doc->id => $doc->pivot])
            ->toArray();
        
        // Collect documents that need rubriek updates
        $documentsToUpdate = [];
        
        foreach ($documents as $document) {
            // Get rubriek from pivot if attached (using pre-loaded data - no N+1 query)
            $rubriek = null;
            if (isset($pivotData[$document->id])) {
                $rubriek = $pivotData[$document->id]->rubriek ?? null;
            }
            
            if (!$rubriek) {
                $rubriek = $document->vat_rubriek ?? $this->calculateRubriek($document);
                // Collect documents that need rubriek update
                if (!$document->vat_rubriek) {
                    $documentsToUpdate[] = ['document' => $document, 'rubriek' => $rubriek];
                }
            }
            
            if (!isset($totals[$rubriek])) {
                $rubriek = '1a'; // Default fallback
            }

            // Only count documents with valid amounts
            if ($document->amount_excl !== null && $document->amount_vat !== null) {
                $totals[$rubriek]['amount'] += (float) $document->amount_excl;
                $totals[$rubriek]['vat'] += (float) $document->amount_vat;
                $totals[$rubriek]['count']++;
            }
        }
        
        // DATA INTEGRITY FIX: Update documents with calculated rubriek in transaction
        if (!empty($documentsToUpdate)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($documentsToUpdate) {
                foreach ($documentsToUpdate as $item) {
                    $item['document']->vat_rubriek = $item['rubriek'];
                    $item['document']->save();
                }
            });
        }

        return $totals;
    }

    /**
     * Get rubriek mapping
     */
    public function getRubriekMapping(): array
    {
        return self::RUBRIEK_MAPPING;
    }

    /**
     * Get rubriek name in Dutch
     */
    public function getRubriekName(string $rubriek): string
    {
        $names = [
            '1a' => 'Leveringen/diensten belast met hoog tarief (21%)',
            '1b' => 'Leveringen/diensten belast met laag tarief (9%)',
            '1c' => 'Overige tarieven',
            '2a' => 'Verleggingsregelingen binnenland',
            '2b' => 'Verleggingsregelingen buitenland',
            '3a' => 'Leveringen naar/in het buitenland (intracommunautair)',
            '3b' => 'Diensten naar/in het buitenland',
            '4a' => 'Voorbelasting (inkopen binnen EU)',
            '4b' => 'Voorbelasting (inkopen buiten EU)',
            '5b' => 'Totaal verschuldigde / te ontvangen BTW',
        ];

        return $names[$rubriek] ?? $rubriek;
    }

    /**
     * Auto-calculate tax for a document when it's approved
     * This method is called automatically when a document status changes to 'approved'
     * 
     * @param Document $document
     * @return array ['rubriek' => string, 'vat_code' => string|null]
     */
    public function autoCalculateForDocument(Document $document): array
    {
        $rubriek = $this->calculateRubriek($document);
        $vatCode = $document->vat_code ?? $this->determineVatCode($document);

        // Update document with calculated values if not set
        if (!$document->vat_rubriek) {
            $document->vat_rubriek = $rubriek;
        }
        if (!$document->vat_code && $vatCode) {
            $document->vat_code = $vatCode;
            $document->save();
        }

        return [
            'rubriek' => $rubriek,
            'vat_code' => $vatCode,
        ];
    }

    /**
     * Auto-calculate and attach document to VAT period
     * This is called when documents are approved to automatically include them in tax calculations
     * 
     * @param Document $document
     * @param \App\Models\VatPeriod $vatPeriod
     * @return void
     */
    public function autoAttachToPeriod(Document $document, \App\Models\VatPeriod $vatPeriod): void
    {
        // Check if document is already attached
        if ($vatPeriod->documents()->where('documents.id', $document->id)->exists()) {
            return;
        }

        // Calculate rubriek and VAT code
        $calculation = $this->autoCalculateForDocument($document);

        // Attach to period with calculated values
        $vatPeriod->documents()->attach($document->id, [
            'rubriek' => $calculation['rubriek'],
            'btw_code' => $calculation['vat_code'],
        ]);
    }
}


