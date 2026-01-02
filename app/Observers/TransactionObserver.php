<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        AuditLog::create([
            'entity_type' => 'Transaction',
            'entity_id' => $transaction->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => $transaction->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $changes = $transaction->getChanges();
        $original = $transaction->getOriginal();
        
        $oldValues = [];
        $newValues = [];
        
        foreach ($changes as $key => $newValue) {
            if ($key === 'updated_at') continue;
            
            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $newValue;
        }
        
        AuditLog::create([
            'entity_type' => 'Transaction',
            'entity_id' => $transaction->id,
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        AuditLog::create([
            'entity_type' => 'Transaction',
            'entity_id' => $transaction->id,
            'action' => 'updated',
            'old_values' => $transaction->getOriginal(),
            'new_values' => ['deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        AuditLog::create([
            'entity_type' => 'Transaction',
            'entity_id' => $transaction->id,
            'action' => 'updated',
            'old_values' => ['deleted' => true],
            'new_values' => $transaction->toArray(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        AuditLog::create([
            'entity_type' => 'Transaction',
            'entity_id' => $transaction->id,
            'action' => 'updated',
            'old_values' => $transaction->getOriginal(),
            'new_values' => ['force_deleted' => true],
            'user_id' => Auth::id(),
        ]);
    }
}
