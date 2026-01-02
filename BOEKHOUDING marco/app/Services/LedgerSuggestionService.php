<?php

namespace App\Services;

use App\Models\Document;
use App\Models\LedgerAccount;
use App\Models\LedgerKeywordMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerSuggestionService
{
    /**
     * Scoring weights as per spec
     */
    const SCORE_SUPPLIER_HISTORY = 40;
    const SCORE_VAT_TYPE_MATCH = 20;
    const SCORE_KEYWORD_MATCH = 20;
    const SCORE_FALLBACK = 50;
    
    /**
     * Fallback account code
     */
    const FALLBACK_ACCOUNT_CODE = '4999'; // Overige kosten
    
    /**
     * Suggest ledger account for a document
     * 
     * Scoring algorithm:
     * 1. Supplier History (+40): Previous documents from same supplier
     * 2. Document Type Match (+30): Based on document type (sales -> omzet, purchase -> inkoop/kosten)
     * 3. VAT Type Match (+20): Document VAT matches account default
     * 4. Keyword Match (+20): Search in ledger_keyword_mappings
     * 5. Fallback (score 50): Account based on document type
     * 
     * @param Document $document
     * @return array ['ledger_account_id' => int, 'confidence_score' => float, 'reason' => string]
     */
    public function suggest(Document $document): array
    {
        $candidates = [];
        
        // 1. Check supplier history (highest priority)
        if (!empty($document->supplier_name)) {
            $historyMatch = $this->findBySupplierHistory($document);
            if ($historyMatch) {
                $candidates[] = $historyMatch;
            }
        }
        
        // 2. Check document type matches (NEW - based on grootboek schema)
        $typeMatches = $this->findByDocumentType($document);
        if (!empty($typeMatches)) {
            $candidates = array_merge($candidates, $typeMatches);
        }
        
        // 3. Check keyword matches
        $keywordMatches = $this->findByKeywords($document);
        if (!empty($keywordMatches)) {
            $candidates = array_merge($candidates, $keywordMatches);
        }
        
        // 4. Check VAT type matches
        if (!empty($document->vat_rate)) {
            $vatMatches = $this->findByVatType($document);
            if (!empty($vatMatches)) {
                $candidates = array_merge($candidates, $vatMatches);
            }
        }
        
        // Sort by confidence score (highest first)
        usort($candidates, function($a, $b) {
            return $b['confidence_score'] <=> $a['confidence_score'];
        });
        
        // Return best match, or fallback
        if (!empty($candidates) && $candidates[0]['confidence_score'] > self::SCORE_FALLBACK) {
            Log::info('Ledger Suggestion: Best match found', [
                'document_id' => $document->id,
                'account' => $candidates[0]['account_code'],
                'score' => $candidates[0]['confidence_score'],
                'reason' => $candidates[0]['reason'],
            ]);
            
            return $candidates[0];
        }
        
        // Fallback based on document type
        $fallback = $this->getFallbackAccount($document);
        
        Log::info('Ledger Suggestion: Using fallback', [
            'document_id' => $document->id,
            'account' => $fallback['account_code'],
            'type' => $document->document_type,
        ]);
        
        return $fallback;
    }
    
    /**
     * Find ledger accounts based on document type
     * Uses grootboek schema:
     * - Sales invoices -> Omzet rekeningen (8000-8999)
     * - Purchase invoices -> Inkoop rekeningen (5000-5999) of Kosten (4000-4999)
     * - Receipts -> Kosten rekeningen (4000-4999)
     * +30 points for document type match
     */
    protected function findByDocumentType(Document $document): array
    {
        $documentType = $document->document_type;
        $vatRate = $document->vat_rate;
        $matches = [];
        
        // Sales invoices -> Omzet rekeningen (8000-8999)
        if ($documentType === 'sales_invoice') {
            $query = LedgerAccount::where('type', 'winst_verlies')
                ->where('active', true)
                ->where(function ($q) {
                    $q->where('code', 'like', '80%')  // Omzet hoog
                      ->orWhere('code', 'like', '81%') // Omzet laag
                      ->orWhere('code', 'like', '82%') // Omzet nul
                      ->orWhere('code', 'like', '83%') // Omzet marge
                      ->orWhere('code', 'like', '84%'); // Omzet verlegd
                });
            
            // Match VAT rate if available
            if ($vatRate) {
                if ($vatRate == '21') {
                    $query->where(function ($q) {
                        $q->where('code', 'like', '80%') // Omzet hoog (21%)
                          ->orWhere('code', 'like', '84%'); // Omzet verlegd
                    });
                } elseif ($vatRate == '9') {
                    $query->where('code', 'like', '81%'); // Omzet laag (9%)
                } elseif ($vatRate == '0') {
                    $query->where('code', 'like', '82%'); // Omzet nul (0%)
                } elseif ($vatRate == 'verlegd') {
                    $query->where('code', 'like', '84%'); // Omzet verlegd
                }
            }
            
            $accounts = $query->orderBy('code')->limit(10)->get();
            
            foreach ($accounts as $account) {
                $score = 30; // Base score for document type match
                
                // Bonus if VAT matches
                if ($account->vat_default === $vatRate) {
                    $score += self::SCORE_VAT_TYPE_MATCH;
                }
                
                $matches[] = [
                    'ledger_account_id' => $account->id,
                    'account_code' => $account->code,
                    'confidence_score' => $score,
                    'reason' => "Verkoopfactuur -> Omzet: {$account->description}",
                ];
            }
        }
        
        // Purchase invoices -> Inkoop (5000-5999) of Kosten (4000-4999)
        if ($documentType === 'purchase_invoice') {
            // First try inkoop rekeningen (5000-5999)
            $query = LedgerAccount::where('type', 'winst_verlies')
                ->where('active', true)
                ->where(function ($q) {
                    $q->where('code', 'like', '50%')  // Inkopen hoog
                      ->orWhere('code', 'like', '51%') // Inkopen laag
                      ->orWhere('code', 'like', '52%') // Inkopen nul/emballage
                      ->orWhere('code', 'like', '53%') // Inkopen marge
                      ->orWhere('code', 'like', '54%'); // Inkopen verlegd
                });
            
            // Match VAT rate if available
            if ($vatRate) {
                if ($vatRate == '21') {
                    $query->where('code', 'like', '50%'); // Inkopen hoog (21%)
                } elseif ($vatRate == '9') {
                    $query->where('code', 'like', '51%'); // Inkopen laag (9%)
                } elseif ($vatRate == '0') {
                    $query->where('code', 'like', '52%'); // Inkopen nul (0%)
                } elseif ($vatRate == 'verlegd') {
                    $query->where('code', 'like', '54%'); // Inkopen verlegd
                }
            }
            
            $accounts = $query->orderBy('code')->limit(5)->get();
            
            foreach ($accounts as $account) {
                $score = 30;
                if ($account->vat_default === $vatRate) {
                    $score += self::SCORE_VAT_TYPE_MATCH;
                }
                
                $matches[] = [
                    'ledger_account_id' => $account->id,
                    'account_code' => $account->code,
                    'confidence_score' => $score,
                    'reason' => "Inkoopfactuur -> Inkoop: {$account->description}",
                ];
            }
            
            // Also check kosten rekeningen (4000-4999) as secondary option
            $kostenQuery = LedgerAccount::where('type', 'winst_verlies')
                ->where('active', true)
                ->where('code', 'like', '4%')
                ->where('vat_default', $vatRate)
                ->orderBy('code')
                ->limit(5);
            
            $kostenAccounts = $kostenQuery->get();
            foreach ($kostenAccounts as $account) {
                $matches[] = [
                    'ledger_account_id' => $account->id,
                    'account_code' => $account->code,
                    'confidence_score' => 25, // Lower score than inkoop
                    'reason' => "Inkoopfactuur -> Kosten: {$account->description}",
                ];
            }
        }
        
        // Receipts -> Kosten rekeningen (4000-4999)
        if ($documentType === 'receipt') {
            $query = LedgerAccount::where('type', 'winst_verlies')
                ->where('active', true)
                ->where('code', 'like', '4%'); // Kosten rekeningen
            
            // Match VAT rate if available
            if ($vatRate) {
                $query->where('vat_default', $vatRate);
            }
            
            $accounts = $query->orderBy('code')->limit(10)->get();
            
            foreach ($accounts as $account) {
                $score = 30;
                if ($account->vat_default === $vatRate) {
                    $score += self::SCORE_VAT_TYPE_MATCH;
                }
                
                $matches[] = [
                    'ledger_account_id' => $account->id,
                    'account_code' => $account->code,
                    'confidence_score' => $score,
                    'reason' => "Bonnetje -> Kosten: {$account->description}",
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Find ledger account based on supplier history
     * +40 points for matching supplier
     */
    protected function findBySupplierHistory(Document $document): ?array
    {
        // Find previous documents from same supplier that are approved
        $previousDocument = Document::where('client_id', $document->client_id)
            ->where('supplier_name', $document->supplier_name)
            ->where('status', 'approved')
            ->whereNotNull('ledger_account_id')
            ->latest()
            ->first();
        
        if (!$previousDocument) {
            return null;
        }
        
        $account = LedgerAccount::find($previousDocument->ledger_account_id);
        
        if (!$account) {
            return null;
        }
        
        $baseScore = self::SCORE_SUPPLIER_HISTORY;
        
        // Bonus points if VAT also matches
        $vatBonus = 0;
        if ($account->vat_default === $document->vat_rate) {
            $vatBonus = self::SCORE_VAT_TYPE_MATCH;
        }
        
        return [
            'ledger_account_id' => $account->id,
            'account_code' => $account->code,
            'confidence_score' => $baseScore + $vatBonus,
            'reason' => "Leverancier historie: {$account->description}",
        ];
    }
    
    /**
     * Find ledger accounts based on keyword matching
     * +20 points base, with priority multiplier
     */
    protected function findByKeywords(Document $document): array
    {
        $searchText = strtolower(implode(' ', array_filter([
            $document->supplier_name,
            $document->ocr_data['raw_text'] ?? '',
        ])));
        
        if (empty($searchText)) {
            return [];
        }
        
        // Find all keyword mappings and check matches
        $mappings = LedgerKeywordMapping::with('ledgerAccount')
            ->orderBy('priority', 'desc')
            ->get();
        
        $matches = [];
        
        foreach ($mappings as $mapping) {
            $keyword = strtolower($mapping->keyword);
            
            if (str_contains($searchText, $keyword)) {
                $account = $mapping->ledgerAccount;
                
                if (!$account || !$account->active) {
                    continue;
                }
                
                // Base score + priority bonus
                $score = self::SCORE_KEYWORD_MATCH;
                
                // Priority multiplier (0-10 range adds 0-10 points)
                if ($mapping->priority > 0) {
                    $score += min($mapping->priority, 10);
                }
                
                // VAT match bonus
                if ($account->vat_default === $document->vat_rate) {
                    $score += self::SCORE_VAT_TYPE_MATCH;
                }
                
                $matches[] = [
                    'ledger_account_id' => $account->id,
                    'account_code' => $account->code,
                    'confidence_score' => $score,
                    'reason' => "Keyword match '{$keyword}': {$account->description}",
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Find accounts matching VAT type
     * +20 points for VAT match
     */
    protected function findByVatType(Document $document): array
    {
        $accounts = LedgerAccount::where('vat_default', $document->vat_rate)
            ->where('active', true)
            ->where('type', 'winst_verlies') // Only expense accounts
            ->get();
        
        $matches = [];
        
        foreach ($accounts as $account) {
            $matches[] = [
                'ledger_account_id' => $account->id,
                'account_code' => $account->code,
                'confidence_score' => self::SCORE_VAT_TYPE_MATCH,
                'reason' => "BTW type match: {$account->description}",
            ];
        }
        
        return $matches;
    }
    
    /**
     * Get fallback account based on document type
     * Uses grootboek schema to find appropriate fallback
     */
    protected function getFallbackAccount(?Document $document = null): array
    {
        $account = null;
        
        if ($document) {
            $documentType = $document->document_type;
            $vatRate = $document->vat_rate;
            
            // Sales invoices -> default omzet rekening
            if ($documentType === 'sales_invoice') {
                if ($vatRate == '21') {
                    $account = LedgerAccount::where('code', '8000')->first(); // Omzet hoog algemeen
                } elseif ($vatRate == '9') {
                    $account = LedgerAccount::where('code', '8100')->first(); // Omzet laag 1
                } else {
                    $account = LedgerAccount::where('code', '8200')->first(); // Omzet nul algemeen
                }
            }
            
            // Purchase invoices -> default inkoop rekening
            if ($documentType === 'purchase_invoice') {
                if ($vatRate == '21') {
                    $account = LedgerAccount::where('code', '5000')->first(); // Inkopen hoog algemeen
                } elseif ($vatRate == '9') {
                    $account = LedgerAccount::where('code', '5100')->first(); // Inkopen laag algemeen
                } else {
                    $account = LedgerAccount::where('code', '5200')->first(); // Inkopen emballage
                }
            }
            
            // Receipts -> default kosten rekening
            if ($documentType === 'receipt') {
                $account = LedgerAccount::where('code', self::FALLBACK_ACCOUNT_CODE)->first();
            }
        }
        
        // Final fallback to 4999
        if (!$account) {
            $account = LedgerAccount::where('code', self::FALLBACK_ACCOUNT_CODE)->first();
        }
        
        if (!$account) {
            // Emergency fallback - should never happen if seeder ran
            Log::error('Ledger Suggestion: Fallback account not found!');
            
            // Return first active account as last resort
            $account = LedgerAccount::where('active', true)->first();
        }
        
        return [
            'ledger_account_id' => $account->id,
            'account_code' => $account->code,
            'confidence_score' => self::SCORE_FALLBACK,
            'reason' => 'Fallback: ' . $account->description,
        ];
    }
    
    /**
     * Learn from a manual correction
     * Adds keyword mapping based on what accountant chose
     */
    public function learnFromCorrection(Document $document, int $correctedAccountId): void
    {
        if (empty($document->supplier_name)) {
            return;
        }
        
        // Extract key words from supplier name
        $keywords = $this->extractKeywords($document->supplier_name);
        
        foreach ($keywords as $keyword) {
            // Check if mapping already exists
            $exists = LedgerKeywordMapping::where('keyword', $keyword)
                ->where('ledger_account_id', $correctedAccountId)
                ->exists();
            
            if (!$exists) {
                LedgerKeywordMapping::create([
                    'keyword' => $keyword,
                    'ledger_account_id' => $correctedAccountId,
                    'priority' => 1, // Start with priority 1
                ]);
                
                Log::info('Ledger Suggestion: Learned new keyword', [
                    'keyword' => $keyword,
                    'account_id' => $correctedAccountId,
                ]);
            }
        }
    }
    
    /**
     * Extract meaningful keywords from text
     */
    protected function extractKeywords(string $text): array
    {
        $text = strtolower($text);
        
        // Remove common legal suffixes
        $text = preg_replace('/\s+(b\.?v\.?|n\.?v\.?|vof|eenmanszaak)\s*$/i', '', $text);
        
        // Split into words
        $words = preg_split('/\s+/', $text);
        
        // Filter out short words and common words
        $stopWords = ['de', 'het', 'een', 'van', 'en', 'voor', 'bv', 'nv'];
        $keywords = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }
}

