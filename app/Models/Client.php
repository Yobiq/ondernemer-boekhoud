<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'kvk_number',
        'vat_number',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'country',
        'website',
        'logo',
        'notes',
        'active',
        'default_vat_period_type',
        'vat_submission_method',
        'auto_approval_enabled',
        'auto_approval_threshold',
        'email_notifications_enabled',
        'notification_preferences',
    ];

    protected $casts = [
        'active' => 'boolean',
        'auto_approval_enabled' => 'boolean',
        'auto_approval_threshold' => 'decimal:2',
        'email_notifications_enabled' => 'boolean',
        'notification_preferences' => 'array',
    ];

    /**
     * Get all documents for this client
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all transactions for this client
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    
    /**
     * Get all VAT periods for this client
     */
    public function vatPeriods(): HasMany
    {
        return $this->hasMany(VatPeriod::class);
    }

    /**
     * Get all tasks for this client
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all BTW reports for this client
     */
    public function btwReports(): HasMany
    {
        return $this->hasMany(BtwReport::class);
    }
}
