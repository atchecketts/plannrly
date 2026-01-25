<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shift $shift): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $shift->tenant_id) {
            return false;
        }

        if ($shift->user_id === $user->id) {
            return true;
        }

        return $user->isAdmin()
            || $user->isLocationAdmin($shift->location_id)
            || $user->isDepartmentAdmin($shift->department_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function update(User $user, Shift $shift): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $shift->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLocationAdmin($shift->location_id)) {
            return true;
        }

        return $user->isDepartmentAdmin($shift->department_id);
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $this->update($user, $shift);
    }

    public function assign(User $user, Shift $shift): bool
    {
        return $this->update($user, $shift);
    }
}
