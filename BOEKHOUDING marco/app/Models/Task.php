<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'document_id',
        'type',
        'description',
        'status',
        'read_at',
        'priority',
        'client_reply',
        'replied_at',
        'admin_reply',
        'admin_replied_at',
        'deadline',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'admin_replied_at' => 'datetime',
        'deadline' => 'date',
    ];

    /**
     * Get the client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the related document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Check if task is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if task is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Scope: Open tasks
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: Resolved tasks
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Check if task is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if task is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark task as read
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if task has reply
     */
    public function hasReply(): bool
    {
        return $this->client_reply !== null && $this->replied_at !== null;
    }

    /**
     * Check if task has admin reply
     */
    public function hasAdminReply(): bool
    {
        return $this->admin_reply !== null && $this->admin_replied_at !== null;
    }

    /**
     * Scope: Unread tasks
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Read tasks
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
