<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'file_path',
        'original_filename',
        'status',
        'document_type',
        'upload_source',
        'amount_excl',
        'amount_vat',
        'amount_incl',
        'vat_rate',
        'vat_rubriek',
        'vat_code',
        'auto_approved',
        'auto_approval_reason',
        'review_required_reason',
        'ledger_account_id',
        'confidence_score',
        'ocr_data',
        'document_date',
        'supplier_name',
        'supplier_vat',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'ocr_data' => 'array', // JSONB field
        'amount_excl' => 'decimal:2',
        'amount_vat' => 'decimal:2',
        'amount_incl' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'document_date' => 'date',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'auto_approved' => 'boolean',
    ];

    /**
     * Get the client that owns this document
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the ledger account
     */
    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    /**
     * Get all tasks related to this document
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the file URL (signed for security)
     * 
     * SECURITY FIX: This method should not be used directly for file access
     * Use DocumentFileController::serve() instead which includes authorization
     */
    public function getFileUrlAttribute(): string
    {
        // Return route to controller instead of direct storage URL
        return url("/documents/{$this->id}/file");
    }

    /**
     * Get the file download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::download($this->file_path, $this->original_filename);
    }

    /**
     * Check if document is locked (cannot be edited)
     */
    public function isLocked(): bool
    {
        return $this->status === 'approved' || $this->status === 'archived';
    }

    /**
     * Scope: Only pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Requires review
     */
    public function scopeReviewRequired($query)
    {
        return $query->where('status', 'review_required');
    }

    /**
     * Scope: Approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get all VAT periods this document belongs to
     */
    public function vatPeriods(): BelongsToMany
    {
        return $this->belongsToMany(VatPeriod::class, 'vat_period_documents')
            ->withPivot('rubriek', 'btw_code')
            ->withTimestamps();
    }

    /**
     * Scope: Auto-approved documents
     */
    public function scopeAutoApproved($query)
    {
        return $query->where('auto_approved', true);
    }

    /**
     * Scope: Requires manual review
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('status', 'review_required')
            ->orWhere('auto_approved', false);
    }
}
