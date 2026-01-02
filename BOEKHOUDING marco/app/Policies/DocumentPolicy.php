<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Check if user is admin/accountant
     */
    protected function isAdmin(User $user): bool
    {
        // Check if user has 'admin' or 'accountant' role via Spatie Permission
        return $user->hasAnyRole(['admin', 'accountant', 'boekhouder']);
    }

    /**
     * Check if user is the document owner (client)
     */
    protected function ownsDocument(User $user, Document $document): bool
    {
        // Assuming user has client_id attribute when they're a client
        return isset($user->client_id) && $user->client_id === $document->client_id;
    }

    /**
     * Determine whether the user can view any models.
     * CRITICAL: Clients see ONLY their own documents
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view (but query will be scoped in Resource)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * CRITICAL: Clients can ONLY view their own documents
     */
    public function view(User $user, Document $document): bool
    {
        // Admin can view all
        if ($this->isAdmin($user)) {
            return true;
        }

        // Client can only view their own
        return $this->ownsDocument($user, $document);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Both admin and clients can upload documents
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        // Admin can update all
        if ($this->isAdmin($user)) {
            return true;
        }

        // Clients cannot update (they can only upload new ones)
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        // Only admin can delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        // Only admin can restore
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // Only admin can force delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can approve the document
     */
    public function approve(User $user, Document $document): bool
    {
        // Only admin can approve
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can download the document
     */
    public function download(User $user, Document $document): bool
    {
        // Same as view policy
        return $this->view($user, $document);
    }
}
