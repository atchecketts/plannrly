<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->isSuperAdmin()
            || $authUser->isAdmin()
            || $authUser->isLocationAdmin()
            || $authUser->isDepartmentAdmin();
    }

    public function view(User $authUser, User $user): bool
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->id === $user->id) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $authUser->isAdmin()
            || $authUser->isLocationAdmin()
            || $authUser->isDepartmentAdmin();
    }

    public function create(User $authUser): bool
    {
        return $authUser->isSuperAdmin()
            || $authUser->isAdmin()
            || $authUser->isLocationAdmin()
            || $authUser->isDepartmentAdmin();
    }

    public function update(User $authUser, User $user): bool
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->id === $user->id) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $authUser->isAdmin()
            || $authUser->isLocationAdmin()
            || $authUser->isDepartmentAdmin();
    }

    public function delete(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $authUser->isAdmin();
    }

    public function assignRoles(User $authUser, User $user): bool
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $authUser->isAdmin() || $authUser->isLocationAdmin();
    }
}
