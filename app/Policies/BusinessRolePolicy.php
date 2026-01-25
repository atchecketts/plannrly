<?php

namespace App\Policies;

use App\Models\BusinessRole;
use App\Models\User;

class BusinessRolePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BusinessRole $businessRole): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $businessRole->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function update(User $user, BusinessRole $businessRole): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $businessRole->tenant_id) {
            return false;
        }

        return $user->canManageDepartment($businessRole->department);
    }

    public function delete(User $user, BusinessRole $businessRole): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $businessRole->tenant_id) {
            return false;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin($businessRole->department->location_id)
            || $user->isDepartmentAdmin($businessRole->department_id);
    }
}
