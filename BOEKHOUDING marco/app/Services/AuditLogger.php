<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an action
     */
    public function log(
        string $action,
        Model $model,
        ?\App\Models\User $user = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        $user = $user ?? Auth::user();
        $modelClass = get_class($model);
        $modelName = class_basename($modelClass);

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $modelClass, // Full class name
            'model_id' => $model->id,
            'entity_type' => $modelName, // Short name for backward compatibility
            'entity_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log document upload
     */
    public function logDocumentUpload(\App\Models\Document $document, ?\App\Models\User $user = null): void
    {
        $this->log(
            'created', // Use 'created' for uploads to match enum constraint
            $document,
            $user,
            null,
            [
                'filename' => $document->original_filename,
                'document_type' => $document->document_type,
                'client_id' => $document->client_id,
            ],
            [
                'upload_source' => $document->upload_source,
                'action_type' => 'upload', // Store actual action in metadata
            ]
        );
    }

    /**
     * Log document approval
     */
    public function logDocumentApproval(
        \App\Models\Document $document,
        \App\Models\User $user,
        bool $autoApproved = false
    ): void {
        $oldValues = [
            'status' => 'review_required',
        ];

        $newValues = [
            'status' => 'approved',
            'auto_approved' => $autoApproved,
        ];

        $this->log(
            'approved', // Use 'approved' to match enum constraint
            $document,
            $user,
            $oldValues,
            $newValues,
            [
                'auto_approved' => $autoApproved,
                'reason' => $autoApproved 
                    ? $document->auto_approval_reason 
                    : 'Handmatig goedgekeurd door boekhouder',
            ]
        );
    }

    /**
     * Log period lock
     */
    public function logPeriodLock(\App\Models\VatPeriod $period, \App\Models\User $user): void
    {
        $oldValues = [
            'status' => $period->getOriginal('status'),
        ];

        $newValues = [
            'status' => 'afgesloten',
            'closed_by' => $user->id,
            'closed_at' => now()->toIso8601String(),
        ];

        $this->log(
            'locked', // Use 'locked' to match enum constraint
            $period,
            $user,
            $oldValues,
            $newValues,
            [
                'period' => $period->period_string,
                'client_id' => $period->client_id,
                'action_type' => 'period_lock', // Store actual action in metadata
            ]
        );
    }

    /**
     * Log period unlock (reopening for corrections)
     */
    public function logPeriodUnlock(\App\Models\VatPeriod $period, \App\Models\User $user): void
    {
        $oldValues = [
            'status' => 'afgesloten',
            'closed_by' => $period->closed_by,
            'closed_at' => $period->closed_at?->toIso8601String(),
        ];

        $newValues = [
            'status' => 'voorbereid',
            'closed_by' => null,
            'closed_at' => null,
        ];

        $this->log(
            'updated', // Use 'updated' to match enum constraint
            $period,
            $user,
            $oldValues,
            $newValues,
            [
                'period' => $period->period_string,
                'client_id' => $period->client_id,
                'action_type' => 'period_unlock', // Store actual action in metadata
            ]
        );
    }

    /**
     * Get history for a model
     */
    public function getHistory(Model $model): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::forModel(get_class($model), $model->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

