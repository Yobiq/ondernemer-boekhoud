<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'description',
        'category',
        'amount',
        'expense_date',
        'receipt_path',
        'notes',
        'is_billable',
        'currency',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'project_id' => 'integer',
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_billable' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
