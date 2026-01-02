<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionMatchingService
{
    /**
     * Matching scores as per spec
     */
    const SCORE_AMOUNT_EXACT = 40;
    const SCORE_DATE_RANGE = 20;
    const SCORE_IBAN_MATCH = 20;
    const SCORE_NAME_MATCH = 20;
    const AUTO_MATCH_THRESHOLD = 90;
    
    /**
     * Date range for matching (±7 days)
     */
    const DATE_RANGE_DAYS = 7;
    
    /**
     * Match transactions to documents for a client
     * 
     * Scoring:
     * - Amount exact match (+40)
     * - Date within ±7 days (+20)
     * - IBAN match (+20)
     * - Name similarity (+20)
     * 
     * Score ≥ 90 → Auto-match
     * 
     * @param int $clientId
     * @return array Statistics about matching
     */
    public function matchForClient(int $clientId): array
    {
        $stats = [
            'processed' => 0,
            'matched' => 0,
            'unmatched' => 0,
        ];
        
        // Get all unmatched transactions for this client
        $transactions = Transaction::where('client_id', $clientId)
            ->whereNull('matched_document_id')
            ->get();
        
        foreach ($transactions as $transaction) {
            $stats['processed']++;
            
            $match = $this->findBestMatch($transaction);
            
            if ($match && $match['score'] >= self::AUTO_MATCH_THRESHOLD) {
                // Auto-match
                $transaction->matched_document_id = $match['document_id'];
                $transaction->save();
                
                $stats['matched']++;
                
                Log::info('Transaction auto-matched', [
                    'transaction_id' => $transaction->id,
                    'document_id' => $match['document_id'],
                    'score' => $match['score'],
                ]);
            } else {
                $stats['unmatched']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Find best matching document for a transaction
     * 
     * @param Transaction $transaction
     * @return array|null ['document_id' => int, 'score' => int, 'reasons' => array]
     */
    public function findBestMatch(Transaction $transaction): ?array
    {
        // Get candidate documents (approved, no existing match)
        $candidates = Document::where('client_id', $transaction->client_id)
            ->where('status', 'approved')
            ->whereNotNull('amount_incl')
            ->get();
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($candidates as $document) {
            $score = $this->calculateMatchScore($transaction, $document);
            
            if ($score['total'] > $bestScore) {
                $bestScore = $score['total'];
                $bestMatch = [
                    'document_id' => $document->id,
                    'score' => $score['total'],
                    'reasons' => $score['reasons'],
                ];
            }
        }
        
        return $bestMatch;
    }
    
    /**
     * Calculate matching score between transaction and document
     * 
     * @param Transaction $transaction
     * @param Document $document
     * @return array ['total' => int, 'reasons' => array]
     */
    protected function calculateMatchScore(Transaction $transaction, Document $document): array
    {
        $score = 0;
        $reasons = [];
        
        // 1. Amount matching (+40 for exact)
        if ($this->amountsMatch($transaction->amount, $document->amount_incl)) {
            $score += self::SCORE_AMOUNT_EXACT;
            $reasons[] = sprintf('Bedrag exact match: €%.2f', $transaction->amount);
        }
        
        // 2. Date matching (+20 if within ±7 days)
        if ($this->datesMatch($transaction->transaction_date, $document->document_date)) {
            $score += self::SCORE_DATE_RANGE;
            $reasons[] = 'Datum binnen bereik (±7 dagen)';
        }
        
        // 3. IBAN matching (+20)
        if ($this->ibansMatch($transaction->iban, $document->ocr_data['supplier']['iban'] ?? null)) {
            $score += self::SCORE_IBAN_MATCH;
            $reasons[] = 'IBAN match';
        }
        
        // 4. Name matching (+20)
        $nameScore = $this->namesMatch(
            $transaction->counterparty_name,
            $document->supplier_name
        );
        if ($nameScore > 0) {
            $score += $nameScore;
            $reasons[] = 'Naam similarity';
        }
        
        return [
            'total' => $score,
            'reasons' => $reasons,
        ];
    }
    
    /**
     * Check if amounts match (within 1 cent tolerance)
     */
    protected function amountsMatch(?float $amount1, ?float $amount2): bool
    {
        if (is_null($amount1) || is_null($amount2)) {
            return false;
        }
        
        return abs($amount1 - $amount2) <= 0.01;
    }
    
    /**
     * Check if dates are within acceptable range (±7 days)
     */
    protected function datesMatch($date1, $date2): bool
    {
        if (is_null($date1) || is_null($date2)) {
            return false;
        }
        
        try {
            $d1 = Carbon::parse($date1);
            $d2 = Carbon::parse($date2);
            
            return abs($d1->diffInDays($d2)) <= self::DATE_RANGE_DAYS;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if IBANs match
     */
    protected function ibansMatch(?string $iban1, ?string $iban2): bool
    {
        if (empty($iban1) || empty($iban2)) {
            return false;
        }
        
        // Normalize (remove spaces)
        $iban1 = str_replace(' ', '', strtoupper($iban1));
        $iban2 = str_replace(' ', '', strtoupper($iban2));
        
        return $iban1 === $iban2;
    }
    
    /**
     * Calculate name similarity score
     * Returns 0-20 based on similarity
     */
    protected function namesMatch(?string $name1, ?string $name2): int
    {
        if (empty($name1) || empty($name2)) {
            return 0;
        }
        
        // Normalize names
        $name1 = $this->normalizeName($name1);
        $name2 = $this->normalizeName($name2);
        
        // Exact match
        if ($name1 === $name2) {
            return self::SCORE_NAME_MATCH;
        }
        
        // Check if one contains the other
        if (str_contains($name1, $name2) || str_contains($name2, $name1)) {
            return self::SCORE_NAME_MATCH;
        }
        
        // Levenshtein similarity (partial credit)
        $similarity = 0;
        similar_text($name1, $name2, $similarity);
        
        if ($similarity >= 80) {
            return (int)round(self::SCORE_NAME_MATCH * ($similarity / 100));
        }
        
        return 0;
    }
    
    /**
     * Normalize name for comparison
     */
    protected function normalizeName(string $name): string
    {
        $name = strtolower($name);
        
        // Remove common legal suffixes
        $name = preg_replace('/\s+(b\.?v\.?|n\.?v\.?|vof|eenmanszaak|holding)\s*$/i', '', $name);
        
        // Remove punctuation
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        
        // Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }
    
    /**
     * Get match suggestions for a transaction (for manual matching UI)
     * 
     * @param Transaction $transaction
     * @param int $limit
     * @return array
     */
    public function getSuggestions(Transaction $transaction, int $limit = 5): array
    {
        $candidates = Document::where('client_id', $transaction->client_id)
            ->where('status', 'approved')
            ->whereNotNull('amount_incl')
            ->get();
        
        $suggestions = [];
        
        foreach ($candidates as $document) {
            $score = $this->calculateMatchScore($transaction, $document);
            
            if ($score['total'] > 0) {
                $suggestions[] = [
                    'document' => $document,
                    'score' => $score['total'],
                    'reasons' => $score['reasons'],
                ];
            }
        }
        
        // Sort by score descending
        usort($suggestions, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Manually match a transaction to a document
     * 
     * @param Transaction $transaction
     * @param Document $document
     * @return bool
     */
    public function manualMatch(Transaction $transaction, Document $document): bool
    {
        // Verify they belong to same client
        if ($transaction->client_id !== $document->client_id) {
            Log::warning('Cannot match transaction to document from different client');
            return false;
        }
        
        $transaction->matched_document_id = $document->id;
        $transaction->save();
        
        Log::info('Transaction manually matched', [
            'transaction_id' => $transaction->id,
            'document_id' => $document->id,
        ]);
        
        return true;
    }
    
    /**
     * Unmatch a transaction
     */
    public function unmatch(Transaction $transaction): bool
    {
        $transaction->matched_document_id = null;
        $transaction->save();
        
        Log::info('Transaction unmatched', ['transaction_id' => $transaction->id]);
        
        return true;
    }
}

