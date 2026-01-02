<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class VatPeriod extends Model
{
    protected $fillable = [
        'client_id',
        'period_start',
        'period_end',
        'status',
        'prepared_by',
        'prepared_at',
        'submitted_by',
        'submitted_at',
        'closed_by',
        'closed_at',
        'year',
        'quarter',
        'month',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'prepared_at' => 'datetime',
        'submitted_at' => 'datetime',
        'closed_at' => 'datetime',
        'year' => 'integer',
        'quarter' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Get the client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who prepared this period
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Get the user who submitted this period
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who closed this period
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all documents in this period
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'vat_period_documents')
            ->withPivot('rubriek', 'btw_code')
            ->withTimestamps();
    }

    /**
     * Check if period is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if period is locked (closed)
     */
    public function isLocked(): bool
    {
        return $this->status === 'afgesloten';
    }

    /**
     * Check if period can be modified
     */
    public function canBeModified(): bool
    {
        return $this->isOpen() || $this->status === 'voorbereid';
    }

    /**
     * Lock the period (close it)
     */
    public function lock(User $user): void
    {
        if ($this->isLocked()) {
            throw new \Exception('Period is already locked');
        }

        $this->update([
            'status' => 'afgesloten',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);
    }

    /**
     * Get formatted period string
     */
    public function getPeriodStringAttribute(): string
    {
        if ($this->quarter) {
            return "{$this->year}-Q{$this->quarter}";
        } elseif ($this->month) {
            $monthName = Carbon::create($this->year, $this->month, 1)->locale('nl')->monthName;
            return "{$monthName} {$this->year}";
        }
        return "{$this->period_start->format('d-m-Y')} t/m {$this->period_end->format('d-m-Y')}";
    }

    /**
     * Scope: Open periods
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: Locked periods
     */
    public function scopeLocked($query)
    {
        return $query->where('status', 'afgesloten');
    }

    /**
     * Scope: By client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Get workflow status for this period
     * Returns the current step in the workflow
     */
    public function getWorkflowStatus(): string
    {
        $approvedCount = $this->documents()->where('status', 'approved')->count();
        $totalCount = $this->documents()->count();
        
        // If no documents, still in processing
        if ($totalCount === 0) {
            return 'documents_processing';
        }

        // If not all documents approved, still processing
        if ($approvedCount < $totalCount) {
            return 'documents_processing';
        }

        // If all approved but period not prepared, tax calculation ready
        if ($approvedCount === $totalCount && $this->status === 'open') {
            return 'tax_calculating';
        }

        // If prepared but not submitted, ready for review/submit
        if ($this->status === 'voorbereid') {
            return 'ready_to_submit';
        }

        // If submitted
        if ($this->status === 'ingediend') {
            return 'submitted';
        }

        // If closed
        if ($this->status === 'afgesloten') {
            return 'submitted';
        }

        return 'documents_processing';
    }

    /**
     * Check if workflow step is complete
     */
    public function isWorkflowStepComplete(string $step): bool
    {
        $currentStatus = $this->getWorkflowStatus();
        
        $stepOrder = [
            'documents_processing' => 1,
            'tax_calculating' => 2,
            'review_required' => 3,
            'ready_to_submit' => 4,
            'submitted' => 5,
        ];

        $currentOrder = $stepOrder[$currentStatus] ?? 0;
        $stepOrderValue = $stepOrder[$step] ?? 0;

        return $currentOrder > $stepOrderValue;
    }

    /**
     * Get workflow progress percentage
     */
    public function getWorkflowProgress(): int
    {
        $status = $this->getWorkflowStatus();
        
        return match($status) {
            'documents_processing' => 25,
            'tax_calculating' => 50,
            'review_required' => 75,
            'ready_to_submit' => 100,
            'submitted' => 100,
            default => 0,
        };
    }

    /**
     * Check if all documents are approved
     */
    public function allDocumentsApproved(): bool
    {
        $total = $this->documents()->count();
        if ($total === 0) {
            return false;
        }
        
        $approved = $this->documents()->where('status', 'approved')->count();
        return $approved === $total;
    }

    /**
     * Check if tax calculation is ready
     */
    public function isTaxCalculationReady(): bool
    {
        return $this->allDocumentsApproved() && $this->status === 'open';
    }

    /**
     * Check if ready to submit
     */
    public function isReadyToSubmit(): bool
    {
        return $this->allDocumentsApproved() 
            && in_array($this->status, ['open', 'voorbereid'])
            && !$this->isLocked();
    }
}
