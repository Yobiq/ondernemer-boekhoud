<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'description',
        'type',
        'vat_default',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get all documents using this ledger account
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all keyword mappings for this account
     */
    public function keywordMappings(): HasMany
    {
        return $this->hasMany(LedgerKeywordMapping::class);
    }

    /**
     * Get full display name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->code} - {$this->description}";
    }

    /**
     * Scope: Only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: Balans accounts
     */
    public function scopeBalans($query)
    {
        return $query->where('type', 'balans');
    }

    /**
     * Scope: Winst & Verlies accounts
     */
    public function scopeWinstVerlies($query)
    {
        return $query->where('type', 'winst_verlies');
    }
}
