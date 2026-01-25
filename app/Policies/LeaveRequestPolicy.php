<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $leaveRequest->tenant_id) {
            return false;
        }

        if ($leaveRequest->user_id === $user->id) {
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

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->user_id !== $user->id) {
            return false;
        }

        return $leaveRequest->isDraft();
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->user_id !== $user->id) {
            return false;
        }

        return $leaveRequest->isDraft() || $leaveRequest->isPending();
    }

    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        // Cannot review own request
        if ($leaveRequest->user_id === $user->id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $leaveRequest->tenant_id) {
            return false;
        }

        if (! $leaveRequest->isPending()) {
            return false;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }
}
