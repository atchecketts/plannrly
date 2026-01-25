<?php

namespace App\Policies;

use App\Models\ShiftSwapRequest;
use App\Models\User;

class ShiftSwapPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ShiftSwapRequest $swapRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $swapRequest->tenant_id) {
            return false;
        }

        if ($swapRequest->requesting_user_id === $user->id || $swapRequest->target_user_id === $user->id) {
            return true;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function respond(User $user, ShiftSwapRequest $swapRequest): bool
    {
        if (! $swapRequest->isPending()) {
            return false;
        }

        return $swapRequest->target_user_id === $user->id;
    }

    public function cancel(User $user, ShiftSwapRequest $swapRequest): bool
    {
        if (! $swapRequest->isPending()) {
            return false;
        }

        return $swapRequest->requesting_user_id === $user->id;
    }

    public function adminApprove(User $user, ShiftSwapRequest $swapRequest): bool
    {
        if (! $swapRequest->isAccepted()) {
            return false;
        }

        if ($swapRequest->approved_by) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $swapRequest->tenant_id) {
            return false;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }
}
