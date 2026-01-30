<?php

namespace App\Policies;

use App\Models\LeaveAllowance;
use App\Models\User;

class LeaveAllowancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, LeaveAllowance $leaveAllowance): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $leaveAllowance->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, LeaveAllowance $leaveAllowance): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $leaveAllowance->tenant_id;
    }

    public function delete(User $user, LeaveAllowance $leaveAllowance): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->tenant_id === $leaveAllowance->tenant_id;
    }
}
