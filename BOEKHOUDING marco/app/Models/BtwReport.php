<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BtwReport extends Model
{
    protected $fillable = [
        'client_id',
        'period',
        'status',
        'totals',
        'locked_at',
    ];

    protected $casts = [
        'totals' => 'array', // JSONB field with Dutch BTW rubrieken
        'locked_at' => 'datetime',
    ];

    /**
     * Get the client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Check if report is locked (submitted)
     */
    public function isLocked(): bool
    {
        return !is_null($this->locked_at) || $this->status === 'locked';
    }

    /**
     * Lock the report (after submission)
     */
    public function lock(): void
    {
        $this->locked_at = now();
        $this->status = 'locked';
        $this->save();
    }

    /**
     * Scope: Locked reports
     */
    public function scopeLocked($query)
    {
        return $query->where('status', 'locked')
            ->orWhereNotNull('locked_at');
    }

    /**
     * Scope: Draft reports
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
