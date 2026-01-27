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
        // Users can always view themselves
        if ($authUser->id === $user->id) {
            return true;
        }

        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Admin can view all users in tenant
        if ($authUser->isAdmin()) {
            return true;
        }

        // LocationAdmin/DepartmentAdmin can only view users in their scope
        return $authUser->canManageUser($user);
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
        // Users can always update themselves
        if ($authUser->id === $user->id) {
            return true;
        }

        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Admin can update all users in tenant
        if ($authUser->isAdmin()) {
            return true;
        }

        // LocationAdmin/DepartmentAdmin can only update users in their scope
        return $authUser->canManageUser($user);
    }

    public function delete(User $authUser, User $user): bool
    {
        // Cannot delete yourself
        if ($authUser->id === $user->id) {
            return false;
        }

        if ($authUser->isSuperAdmin()) {
            return true;
        }

        if ($authUser->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Only Admin can delete users
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

        // Admin can assign roles to anyone in tenant
        if ($authUser->isAdmin()) {
            return true;
        }

        // LocationAdmin can assign roles to users in their locations
        if ($authUser->isLocationAdmin()) {
            return $authUser->canManageUser($user);
        }

        return false;
    }
}
