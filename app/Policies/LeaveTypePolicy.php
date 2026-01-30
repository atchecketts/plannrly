<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $leaveType->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $leaveType->tenant_id;
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $leaveType->tenant_id;
    }
}
