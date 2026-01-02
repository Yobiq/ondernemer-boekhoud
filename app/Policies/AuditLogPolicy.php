<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Check if user is admin/boekhouder
     */
    protected function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'accountant', 'boekhouder']);
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
    public function view(User $user, AuditLog $auditLog): bool
    {
        // Admin can view all
        if ($this->isAdmin($user)) {
            return true;
        }

        // Client can only view their own audit logs
        // Check if the logged model belongs to the client
        if ($auditLog->model_type === 'App\\Models\\Document') {
            $document = $auditLog->model;
            return isset($user->client_id) && $user->client_id === $document->client_id;
        }

        if ($auditLog->model_type === 'App\\Models\\VatPeriod') {
            $period = $auditLog->model;
            return isset($user->client_id) && $user->client_id === $period->client_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Audit logs are created by the system, not users
     */
    public function create(User $user): bool
    {
        // Only system can create audit logs
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Audit logs are append-only (immutable)
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        // Audit logs are immutable
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Audit logs are append-only (immutable)
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        // Audit logs are immutable
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuditLog $auditLog): bool
    {
        // Audit logs are immutable
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuditLog $auditLog): bool
    {
        // Audit logs are immutable
        return false;
    }
}
