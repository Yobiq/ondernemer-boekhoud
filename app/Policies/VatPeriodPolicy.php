<?php

namespace App\Policies;

use App\Models\VatPeriod;
use App\Models\User;

class VatPeriodPolicy
{
    /**
     * Check if user is admin/boekhouder
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'accountant', 'boekhouder']);
    }

    /**
     * Check if user owns the period's client
     */
    protected function ownsPeriod(User $user, VatPeriod $vatPeriod): bool
    {
        return isset($user->client_id) && $user->client_id === $vatPeriod->client_id;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view (but query will be scoped)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VatPeriod $vatPeriod): bool
    {
        // Admin can view all
        if ($this->isAdmin($user)) {
            return true;
        }

        // Client can only view their own
        return $this->ownsPeriod($user, $vatPeriod);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin/boekhouder can create periods
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VatPeriod $vatPeriod): bool
    {
        // Only admin/boekhouder can update
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Cannot update if locked
        return !$vatPeriod->isLocked();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VatPeriod $vatPeriod): bool
    {
        // Only admin can delete
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Cannot delete if locked
        return !$vatPeriod->isLocked();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VatPeriod $vatPeriod): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VatPeriod $vatPeriod): bool
    {
        return $this->isAdmin($user) && !$vatPeriod->isLocked();
    }

    /**
     * Determine whether the user can lock the period.
     */
    public function lock(User $user, VatPeriod $vatPeriod): bool
    {
        // Only admin/boekhouder can lock
        return $this->isAdmin($user) && !$vatPeriod->isLocked();
    }

    /**
     * Determine whether the user can unlock the period.
     */
    public function unlock(User $user, VatPeriod $vatPeriod): bool
    {
        // Only admin can unlock (and only if status is ingediend)
        return $this->isAdmin($user) 
            && $vatPeriod->status === 'ingediend' 
            && !$vatPeriod->isLocked();
    }
}
