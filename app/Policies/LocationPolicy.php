<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin();
    }

    public function view(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLocationAdmin($location->id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function update(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        return $user->isAdmin() || $user->isLocationAdmin($location->id);
    }

    public function delete(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $location->tenant_id) {
            return false;
        }

        return $user->isAdmin();
    }
}
