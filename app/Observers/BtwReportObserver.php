<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\BtwReport;
use Illuminate\Support\Facades\Auth;

class BtwReportObserver
{
    /**
     * Handle the BtwReport "created" event.
     */
    public function created(BtwReport $btwReport): void
    {
        AuditLog::create([
            'entity_type' => 'BtwReport',
            'entity_id' => $btwReport->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => $btwReport->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the BtwReport "updated" event.
     * Enforce locking and create audit log
     */
    public function updated(BtwReport $btwReport): void
    {
        $changes = $btwReport->getChanges();
        $original = $btwReport->getOriginal();
        
        $oldValues = [];
        $newValues = [];
        
        foreach ($changes as $key => $newValue) {
            if ($key === 'updated_at') continue;
            
            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $newValue;
        }
        
        // Special action for locking
        $action = 'updated';
        if (isset($changes['status']) && $changes['status'] === 'locked') {
            $action = 'locked';
        } elseif (isset($changes['status']) && $changes['status'] === 'submitted') {
            $action = 'locked'; // Submission also locks
        }
        
        AuditLog::create([
            'entity_type' => 'BtwReport',
            'entity_id' => $btwReport->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the BtwReport "deleted" event.
     */
    public function deleted(BtwReport $btwReport): void
    {
        AuditLog::create([
            'entity_type' => 'BtwReport',
            'entity_id' => $btwReport->id,
            'action' => 'updated',
            'old_values' => $btwReport->getOriginal(),
            'new_values' => ['deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the BtwReport "restored" event.
     */
    public function restored(BtwReport $btwReport): void
    {
        AuditLog::create([
            'entity_type' => 'BtwReport',
            'entity_id' => $btwReport->id,
            'action' => 'updated',
            'old_values' => ['deleted' => true],
            'new_values' => $btwReport->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the BtwReport "force deleted" event.
     */
    public function forceDeleted(BtwReport $btwReport): void
    {
        AuditLog::create([
            'entity_type' => 'BtwReport',
            'entity_id' => $btwReport->id,
            'action' => 'updated',
            'old_values' => $btwReport->getOriginal(),
            'new_values' => ['force_deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }
}
