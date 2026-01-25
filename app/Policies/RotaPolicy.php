<?php

namespace App\Policies;

use App\Models\Rota;
use App\Models\User;

class RotaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Rota $rota): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenant_id === $rota->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isLocationAdmin()
            || $user->isDepartmentAdmin();
    }

    public function update(User $user, Rota $rota): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $rota->tenant_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($rota->location_id && $user->isLocationAdmin($rota->location_id)) {
            return true;
        }

        return $rota->department_id && $user->isDepartmentAdmin($rota->department_id);
    }

    public function delete(User $user, Rota $rota): bool
    {
        return $this->update($user, $rota) && $rota->isDraft();
    }

    public function publish(User $user, Rota $rota): bool
    {
        return $this->update($user, $rota) && $rota->isDraft();
    }
}
