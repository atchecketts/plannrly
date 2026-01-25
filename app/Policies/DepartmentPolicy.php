<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function view(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $department->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLocationAdmin($department->location_id)) {
            return true;
        }

        return $user->isDepartmentAdmin($department->id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $department->tenant_id) {
            return false;
        }

        return $user->canManageDepartment($department);
    }

    public function delete(User $user, Department $department): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $department->tenant_id) {
            return false;
        }

        return $user->isAdmin() || $user->isLocationAdmin($department->location_id);
    }
}
