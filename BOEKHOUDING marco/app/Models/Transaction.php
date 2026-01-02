<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'client_id',
        'bank_reference',
        'amount',
        'transaction_date',
        'iban',
        'counterparty_name',
        'description',
        'matched_document_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the matched document
     */
    public function matchedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'matched_document_id');
    }

    /**
     * Check if transaction is matched
     */
    public function isMatched(): bool
    {
        return !is_null($this->matched_document_id);
    }

    /**
     * Scope: Unmatched transactions
     */
    public function scopeUnmatched($query)
    {
        return $query->whereNull('matched_document_id');
    }

    /**
     * Scope: Matched transactions
     */
    public function scopeMatched($query)
    {
        return $query->whereNotNull('matched_document_id');
    }
}
