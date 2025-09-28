<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

final class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->invoice->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->id === $payment->invoice->user_id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->id === $payment->invoice->user_id;
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->id === $payment->invoice->user_id;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->id === $payment->invoice->user_id;
    }
}
