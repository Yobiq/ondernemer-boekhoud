<?php

namespace App\Services;

use App\Models\VatPeriod;
use App\Models\User;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class VatPeriodLockService
{
    /**
     * Lock a VAT period (close it)
     */
    public function lock(VatPeriod $period, User $user): void
    {
        if ($period->isLocked()) {
            throw new \Exception('Periode is al afgesloten');
        }

        // Validate before locking
        $validation = $this->validateBeforeLock($period);
        if (!$validation['can_lock']) {
            throw new \Exception('Periode kan niet worden afgesloten: ' . implode(', ', $validation['errors']));
        }

        DB::transaction(function () use ($period, $user) {
            $period->update([
                'status' => 'afgesloten',
                'closed_by' => $user->id,
                'closed_at' => now(),
            ]);

            // Log the lock action
            $auditLogger = app(\App\Services\AuditLogger::class);
            $auditLogger->logPeriodLock($period, $user);
        });
    }

    /**
     * Unlock a VAT period (reopen for corrections)
     * Allows reopening closed periods to add/edit documents
     */
    public function unlock(VatPeriod $period, User $user): void
    {
        if (!$period->isLocked()) {
            throw new \Exception('Periode is niet afgesloten');
        }

        // Allow unlocking closed periods (for corrections/late submissions)
        DB::transaction(function () use ($period, $user) {
            $period->update([
                'status' => 'voorbereid', // Reopen for editing
                'closed_by' => null,
                'closed_at' => null,
            ]);

            // Log the unlock action
            $auditLogger = app(\App\Services\AuditLogger::class);
            $auditLogger->logPeriodUnlock($period, $user);
        });
    }

    /**
     * Check if period can be unlocked
     */
    public function canUnlock(VatPeriod $period): bool
    {
        return $period->isLocked();
    }

    /**
     * Check if period can be locked
     */
    public function canLock(VatPeriod $period): bool
    {
        $validation = $this->validateBeforeLock($period);
        return $validation['can_lock'];
    }

    /**
     * Validate before locking
     * 
     * @return array ['can_lock' => bool, 'errors' => array]
     */
    public function validateBeforeLock(VatPeriod $period): array
    {
        $errors = [];

        // Check if all documents are approved
        $unapprovedCount = $period->documents()
            ->where('status', '!=', 'approved')
            ->count();

        if ($unapprovedCount > 0) {
            $errors[] = "Er zijn {$unapprovedCount} document(en) die nog niet zijn goedgekeurd";
        }

        // Check if there are any open tasks
        $openTasksCount = $period->client->tasks()
            ->where('status', 'open')
            ->whereHas('document', function ($query) use ($period) {
                $query->whereHas('vatPeriods', function ($q) use ($period) {
                    $q->where('vat_periods.id', $period->id);
                });
            })
            ->count();

        if ($openTasksCount > 0) {
            $errors[] = "Er zijn {$openTasksCount} openstaande ta(a)k(en)";
        }

        // Check if period has documents
        $documentCount = $period->documents()->count();
        if ($documentCount === 0) {
            $errors[] = 'Periode heeft geen documenten';
        }

        return [
            'can_lock' => empty($errors),
            'errors' => $errors,
        ];
    }
}


